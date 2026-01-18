<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\PharmacyDispensation;
use App\Models\Hospital\PharmacyDispensationItem;
use App\Models\Hospital\VisitBill;
use App\Models\Hospital\VisitBillItem;
use App\Models\Inventory\Item;
use App\Models\Inventory\Movement;
use App\Models\Customer;
use App\Models\Sales\SalesInvoice;
use App\Models\SystemSetting;
use App\Models\GlTransaction;
use App\Services\InventoryStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PharmacyController extends Controller
{
    protected $stockService;

    public function __construct(InventoryStockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display pharmacy dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;
        $locationId = session('location_id') ?? $user->location_id ?? null;

        // Get visits waiting for pharmacy (bills must be cleared OR has paid SalesInvoice for pharmacy)
        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'bills', 'consultation', 'pharmacyDispensations'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'pharmacy');
                })->where('status', 'waiting');
            })
            ->where(function ($query) use ($companyId, $branchId) {
                // Either has cleared VisitBill (old flow)
                $query->whereHas('bills', function ($q) {
                    $q->where('clearance_status', 'cleared');
                })
                // OR has Customer with paid SalesInvoice matching patient (new pre-billing flow - pharmacy bill)
                ->orWhereExists(function ($subQuery) use ($companyId, $branchId) {
                    $subQuery->select(DB::raw(1))
                        ->from('sales_invoices')
                        ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
                        ->join('patients', 'patients.id', '=', 'visits.patient_id')
                        ->where('sales_invoices.company_id', $companyId)
                        ->where('sales_invoices.branch_id', $branchId)
                        ->where('sales_invoices.status', 'paid')
                        ->where('sales_invoices.notes', 'like', '%Pharmacy bill for Visit%')
                        ->where(function ($q) {
                            $q->whereColumn('customers.phone', 'patients.phone')
                                ->orWhereColumn('customers.email', 'patients.email')
                                ->orWhereColumn('customers.name', DB::raw("CONCAT(patients.first_name, ' ', patients.last_name)"));
                        });
                });
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get visits in service at pharmacy
        $inServiceVisits = Visit::with(['patient', 'visitDepartments.department', 'pharmacyDispensations'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'pharmacy');
                })->where('status', 'in_service');
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get pending dispensations
        $pendingDispensations = PharmacyDispensation::with(['patient', 'visit', 'items.product'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get statistics
        $stats = [
            'waiting' => $waitingVisits->count(),
            'in_service' => $inServiceVisits->count(),
            'pending_dispensations' => $pendingDispensations->count(),
            'dispensed_today' => PharmacyDispensation::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('status', 'dispensed')
                ->whereDate('dispensed_at', today())
                ->count(),
        ];

        return view('hospital.pharmacy.index', compact('waitingVisits', 'inServiceVisits', 'pendingDispensations', 'stats', 'locationId'));
    }

    /**
     * Show dispensation form for a visit
     */
    public function create($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills', 'consultation', 'pharmacyDispensations.items.product'])
            ->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        // Check if bill is cleared (VisitBill) OR has paid SalesInvoice (pre-billing)
        $hasClearedBill = $visit->bills()->where('clearance_status', 'cleared')->exists();
        
        $patient = $visit->patient;
        $hasPaidInvoice = false;
        if ($patient) {
            $customer = Customer::where('company_id', $patient->company_id)
                ->where(function ($q) use ($patient) {
                    if ($patient->phone) {
                        $q->where('phone', $patient->phone);
                    }
                    if ($patient->email) {
                        $q->orWhere('email', $patient->email);
                    }
                    $q->orWhere('name', $patient->full_name);
                })
                ->first();
            
            if ($customer) {
                $hasPaidInvoice = SalesInvoice::where('customer_id', $customer->id)
                    ->where('company_id', $patient->company_id)
                    ->where('branch_id', $patient->branch_id)
                    ->where('status', 'paid')
                    ->where('notes', 'like', '%Pharmacy bill for Visit%')
                    ->exists();
            }
        }
        
        if (!$hasClearedBill && !$hasPaidInvoice) {
            return redirect()->route('hospital.pharmacy.index')
                ->withErrors(['error' => 'Patient bill must be cleared or paid before pharmacy dispensing.']);
        }

        // Get medications from paid SalesInvoice (pharmacy bill created by doctor)
        $pharmacyInvoice = null;
        $invoiceItems = collect();
        
        if ($hasPaidInvoice && $patient) {
            $customer = Customer::where('company_id', $patient->company_id)
                ->where(function ($q) use ($patient) {
                    if ($patient->phone) {
                        $q->where('phone', $patient->phone);
                    }
                    if ($patient->email) {
                        $q->orWhere('email', $patient->email);
                    }
                    $q->orWhere('name', $patient->full_name);
                })
                ->first();
            
            if ($customer) {
                $pharmacyInvoice = SalesInvoice::where('customer_id', $customer->id)
                    ->where('company_id', $patient->company_id)
                    ->where('branch_id', $patient->branch_id)
                    ->where('status', 'paid')
                    ->where('notes', 'like', '%Pharmacy bill for Visit #' . $visit->visit_number . '%')
                    ->with(['items.inventoryItem'])
                    ->first();
                
                if ($pharmacyInvoice) {
                    $invoiceItems = $pharmacyInvoice->items;
                }
            }
        }

        $user = Auth::user();
        $locationId = session('location_id') ?? $user->location_id ?? null;

        // Map invoice items to medications format
        $medications = $invoiceItems->map(function ($invoiceItem) use ($locationId) {
            $item = $invoiceItem->inventoryItem;
            if (!$item || $item->item_type !== 'product') {
                return null;
            }
            
            $stock = 0;
            if ($locationId && $item->track_stock) {
                $stock = $this->stockService->getItemStockAtLocation($item->id, $locationId);
            }
            
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'unit_price' => $invoiceItem->unit_price,
                'unit_of_measure' => $item->unit_of_measure,
                'stock' => $stock,
                'track_stock' => $item->track_stock,
                'quantity' => $invoiceItem->quantity,
                'description' => $invoiceItem->description ?? '', // Dosage from doctor
                'sales_invoice_item_id' => $invoiceItem->id,
            ];
        })->filter();

        // Get prescription from consultation if available
        $prescription = null;
        if ($visit->consultation && $visit->consultation->prescription) {
            $prescription = $visit->consultation->prescription;
        }

        return view('hospital.pharmacy.create', compact('visit', 'medications', 'prescription', 'locationId', 'pharmacyInvoice'));
    }

    /**
     * Store dispensation
     */
    public function store(Request $request, $visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments', 'bills'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:inventory_items,id',
            'items.*.quantity_prescribed' => 'required|numeric|min:1',
            'items.*.quantity_dispensed' => 'required|numeric|min:0',
            'items.*.sales_invoice_item_id' => 'nullable|exists:sales_invoice_items,id',
            'instructions' => 'nullable|string',
        ]);

        // Convert numeric values to integers for database
        $validated['items'] = array_map(function ($item) {
            $item['quantity_prescribed'] = (int) $item['quantity_prescribed'];
            $item['quantity_dispensed'] = (int) $item['quantity_dispensed'];
            return $item;
        }, $validated['items']);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;
            $locationId = session('location_id') ?? $user->location_id ?? null;

            // Generate dispensation number
            $dispensationNumber = 'PHARM-' . now()->format('Ymd') . '-' . str_pad(PharmacyDispensation::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Check stock availability
            $stockIssues = [];
            foreach ($validated['items'] as $item) {
                $product = Item::find($item['product_id']);
                if ($product && $product->track_stock && $locationId) {
                    $availableStock = $this->stockService->getItemStockAtLocation($product->id, $locationId);
                    if ($item['quantity_dispensed'] > $availableStock) {
                        $stockIssues[] = "{$product->name}: Requested {$item['quantity_dispensed']}, Available {$availableStock}";
                    }
                }
            }

            if (!empty($stockIssues)) {
                return back()->withInput()->withErrors(['stock' => 'Insufficient stock: ' . implode(', ', $stockIssues)]);
            }

            // No need to create bill - it already exists from doctor's pharmacy invoice
            $bill = null;

            // Create dispensation (bill is already paid via SalesInvoice)
            $dispensation = PharmacyDispensation::create([
                'dispensation_number' => $dispensationNumber,
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'bill_id' => null, // No VisitBill - using SalesInvoice instead
                'status' => 'pending',
                'instructions' => $validated['instructions'] ?? null,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]);

            // Create dispensation items - get dosage from SalesInvoiceItem description
            foreach ($validated['items'] as $itemData) {
                $product = Item::find($itemData['product_id']);
                $status = $itemData['quantity_dispensed'] > 0 ? 'dispensed' : 'cancelled';
                
                if ($itemData['quantity_dispensed'] < $itemData['quantity_prescribed']) {
                    $status = 'partial';
                }

                // Get dosage from SalesInvoiceItem if available
                $dosageInstructions = null;
                if (!empty($itemData['sales_invoice_item_id'])) {
                    $invoiceItem = \App\Models\Sales\SalesInvoiceItem::find($itemData['sales_invoice_item_id']);
                    if ($invoiceItem && $invoiceItem->description) {
                        $dosageInstructions = $invoiceItem->description;
                    }
                }

                $dispensationItem = PharmacyDispensationItem::create([
                    'dispensation_id' => $dispensation->id,
                    'product_id' => $product->id,
                    'quantity_prescribed' => $itemData['quantity_prescribed'],
                    'quantity_dispensed' => $itemData['quantity_dispensed'],
                    'dosage_instructions' => $dosageInstructions,
                    'status' => $status,
                ]);

                // Create inventory movement and GL transactions for dispensed items
                if ($itemData['quantity_dispensed'] > 0 && $product) {
                    $unitCost = $product->cost_price ?? 0;
                    $totalCost = $itemData['quantity_dispensed'] * $unitCost;
                    
                    // Check stock if tracking is enabled
                    $balanceBefore = null;
                    $balanceAfter = null;
                    if ($product->track_stock && $locationId) {
                        $availableStock = $this->stockService->getItemStockAtLocation($product->id, $locationId);
                        
                        if ($itemData['quantity_dispensed'] > $availableStock) {
                            DB::rollBack();
                            return back()->withInput()->withErrors(['error' => "Insufficient stock for {$product->name}. Available: {$availableStock}, Required: {$itemData['quantity_dispensed']}"]);
                        }

                        // Calculate balance before and after
                        $balanceBefore = $availableStock;
                        $balanceAfter = $balanceBefore - $itemData['quantity_dispensed'];
                    }

                    // Create inventory movement (sold) - always create for GL transactions
                    // Use default location if not set
                    $movementLocationId = $locationId ?? SystemSetting::where('key', 'inventory_default_location')->value('value') ?? 1;
                    
                    $movement = Movement::create([
                        'branch_id' => $branchId,
                        'location_id' => $movementLocationId,
                        'item_id' => $product->id,
                        'user_id' => $user->id,
                        'movement_type' => 'sold',
                        'quantity' => $itemData['quantity_dispensed'],
                        'unit_price' => $product->unit_price ?? 0,
                        'unit_cost' => $unitCost,
                        'total_cost' => $totalCost,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'reference_type' => 'pharmacy_dispensation',
                        'reference_id' => $dispensation->id,
                        'reference_number' => $dispensation->dispensation_number,
                        'notes' => "Dispensed to patient: {$visit->patient->full_name}",
                        'movement_date' => now(),
                    ]);

                    // Create GL transactions if total cost > 0
                    if ($totalCost > 0) {
                        $this->createGLTransactionsForDispense($movement, $product, $dispensation, $user);
                    }
                    
                    Log::info('Inventory movement created for pharmacy dispensation during store', [
                        'movement_id' => $movement->id,
                        'dispensation_id' => $dispensation->id,
                        'product_id' => $product->id,
                        'quantity' => $itemData['quantity_dispensed'],
                        'total_cost' => $totalCost,
                    ]);
                }
            }

            // Update dispensation status to dispensed if any items were dispensed
            $hasDispensedItems = $dispensation->items()->where('quantity_dispensed', '>', 0)->exists();
            if ($hasDispensedItems) {
                $dispensation->status = 'dispensed';
                $dispensation->dispensed_by = $user->id;
                $dispensation->dispensed_at = now();
                $dispensation->save();
            }

            DB::commit();

            $message = $hasDispensedItems 
                ? 'Medications dispensed successfully. Stock updated and GL transactions recorded.'
                : 'Dispensation created successfully.';

            return redirect()->route('hospital.pharmacy.show', $dispensation->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pharmacy dispensation error: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create dispensation: ' . $e->getMessage()]);
        }
    }

    /**
     * Show dispensation details
     */
    public function show($id)
    {
        $dispensation = PharmacyDispensation::with([
            'patient',
            'visit',
            'bill',
            'items.product',
            'dispensedBy',
        ])->findOrFail($id);

        // Verify access
        if ($dispensation->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to dispensation.');
        }

        $user = Auth::user();
        $locationId = session('location_id') ?? $user->location_id ?? null;

        // Get stock for each item
        $itemsWithStock = $dispensation->items->map(function ($item) use ($locationId) {
            $stock = 0;
            if ($locationId && $item->product && $item->product->track_stock) {
                $stock = $this->stockService->getItemStockAtLocation($item->product->id, $locationId);
            }
            $item->available_stock = $stock;
            return $item;
        });

        return view('hospital.pharmacy.show', compact('dispensation', 'itemsWithStock', 'locationId'));
    }

    /**
     * Start service (mark patient as in_service)
     */
    public function startService($visitId)
    {
        $visit = Visit::with(['visitDepartments'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        // Find pharmacy department
        $pharmacyDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'pharmacy');
            })
            ->where('status', 'waiting')
            ->first();

        if (!$pharmacyDept) {
            return back()->withErrors(['error' => 'Pharmacy department not found or already started.']);
        }

        try {
            $pharmacyDept->status = 'in_service';
            $pharmacyDept->service_started_at = now();
            $pharmacyDept->served_by = Auth::id();
            $pharmacyDept->calculateWaitingTime();
            $pharmacyDept->save();

            return redirect()->route('hospital.pharmacy.index')
                ->with('success', 'Pharmacy service started.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to start service: ' . $e->getMessage()]);
        }
    }

    /**
     * Dispense medications (just mark as dispensed - movements/GL already created in store)
     */
    public function dispense($id)
    {
        $dispensation = PharmacyDispensation::with(['items.product', 'visit'])->findOrFail($id);

        // Verify access
        if ($dispensation->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to dispensation.');
        }

        if ($dispensation->status === 'dispensed') {
            return back()->withErrors(['error' => 'Dispensation already completed.']);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Update item statuses (already set during store, but ensure consistency)
            foreach ($dispensation->items as $item) {
                if ($item->quantity_dispensed >= $item->quantity_prescribed) {
                    $item->status = 'dispensed';
                } elseif ($item->quantity_dispensed > 0) {
                    $item->status = 'partial';
                }
                $item->save();
            }

            // Update dispensation status
            $dispensation->status = 'dispensed';
            $dispensation->dispensed_by = $user->id;
            $dispensation->dispensed_at = now();
            $dispensation->save();

            // Update pharmacy visit department status to completed
            $pharmacyDept = $dispensation->visit->visitDepartments()
                ->whereHas('department', function ($q) {
                    $q->where('type', 'pharmacy');
                })
                ->first();

            if ($pharmacyDept && $pharmacyDept->status === 'in_service') {
                $pharmacyDept->status = 'completed';
                $pharmacyDept->service_ended_at = now();
                $pharmacyDept->calculateServiceTime();
                $pharmacyDept->save();
            }

            DB::commit();

            return redirect()->route('hospital.pharmacy.show', $dispensation->id)
                ->with('success', 'Medications dispensed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pharmacy dispense error', [
                'dispensation_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to dispense medications: ' . $e->getMessage()]);
        }
    }

    /**
     * Get product stock via AJAX
     */
    public function getProductStock(Request $request)
    {
        $productId = $request->get('product_id');
        $locationId = session('location_id') ?? Auth::user()->location_id ?? null;

        if (!$locationId) {
            return response()->json(['stock' => 0, 'message' => 'No location selected']);
        }

        $product = Item::find($productId);
        if (!$product) {
            return response()->json(['stock' => 0, 'message' => 'Product not found']);
        }

        $stock = 0;
        if ($product->track_stock) {
            $stock = $this->stockService->getItemStockAtLocation($productId, $locationId);
        } else {
            return response()->json(['stock' => null, 'message' => 'Stock tracking not enabled for this product']);
        }

        return response()->json([
            'stock' => $stock,
            'unit' => $product->unit_of_measure,
            'track_stock' => $product->track_stock,
        ]);
    }

    /**
     * Create GL transactions for pharmacy dispensation
     */
    private function createGLTransactionsForDispense($movement, $product, $dispensation, $user)
    {
        try {
            // Get default accounts from system settings
            $inventoryAccountId = SystemSetting::where('key', 'inventory_default_inventory_account')->value('value');
            $costAccountId = SystemSetting::where('key', 'inventory_default_cost_account')->value('value');

            if (!$inventoryAccountId || !$costAccountId) {
                Log::warning('Default inventory account or cost account not configured in inventory settings. Skipping GL transactions for pharmacy dispensation.');
                return;
            }

            $totalCost = $movement->total_cost;
            if ($totalCost <= 0) {
                Log::warning("Zero or negative cost for pharmacy dispensation. Skipping GL transactions.");
                return;
            }

            $branchId = session('branch_id') ?? $user->branch_id;
            $description = "Pharmacy Dispensation: {$dispensation->dispensation_number} - {$product->name} - Patient: {$dispensation->patient->full_name}";

            // Debit: Cost of Goods Sold Account (Expense increases)
            GlTransaction::create([
                'chart_account_id' => $costAccountId,
                'amount' => $totalCost,
                'nature' => 'debit',
                'transaction_id' => $movement->id,
                'transaction_type' => 'pharmacy_dispensation',
                'date' => $movement->movement_date ?? now(),
                'description' => $description,
                'branch_id' => $branchId,
                'user_id' => $user->id,
            ]);

            // Credit: Inventory Account (Asset decreases)
            GlTransaction::create([
                'chart_account_id' => $inventoryAccountId,
                'amount' => $totalCost,
                'nature' => 'credit',
                'transaction_id' => $movement->id,
                'transaction_type' => 'pharmacy_dispensation',
                'date' => $movement->movement_date ?? now(),
                'description' => $description,
                'branch_id' => $branchId,
                'user_id' => $user->id,
            ]);

            Log::info('GL transactions created for pharmacy dispensation', [
                'movement_id' => $movement->id,
                'dispensation_id' => $dispensation->id,
                'total_cost' => $totalCost,
                'inventory_account_id' => $inventoryAccountId,
                'cost_account_id' => $costAccountId,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create GL transactions for pharmacy dispensation: " . $e->getMessage());
            // Don't throw - allow dispensation to continue even if GL fails
        }
    }
}
