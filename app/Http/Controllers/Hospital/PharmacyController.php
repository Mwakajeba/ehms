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

        // Get visits waiting for pharmacy (bills must be cleared)
        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'bills', 'consultation', 'pharmacyDispensations'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'pharmacy');
                })->where('status', 'waiting');
            })
            ->whereHas('bills', function ($q) {
                $q->where('clearance_status', 'cleared');
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

        // Check if bill is cleared
        $hasClearedBill = $visit->bills()->where('clearance_status', 'cleared')->exists();
        if (!$hasClearedBill) {
            return redirect()->route('hospital.pharmacy.index')
                ->withErrors(['error' => 'Patient bill must be cleared before pharmacy dispensing.']);
        }

        // Get available medications from inventory_items (products only)
        $user = Auth::user();
        $locationId = session('location_id') ?? $user->location_id ?? null;
        
        $medications = Item::where('company_id', $user->company_id)
            ->where('item_type', 'product')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($item) use ($locationId) {
                $stock = 0;
                if ($locationId && $item->track_stock) {
                    $stock = $this->stockService->getItemStockAtLocation($item->id, $locationId);
                }
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'unit_price' => $item->unit_price,
                    'unit_of_measure' => $item->unit_of_measure,
                    'stock' => $stock,
                    'track_stock' => $item->track_stock,
                ];
            });

        // Get prescription from consultation if available
        $prescription = null;
        if ($visit->consultation && $visit->consultation->prescription) {
            $prescription = $visit->consultation->prescription;
        }

        return view('hospital.pharmacy.create', compact('visit', 'medications', 'prescription', 'locationId'));
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
            'items.*.quantity_prescribed' => 'required|integer|min:1',
            'items.*.quantity_dispensed' => 'required|integer|min:0',
            'items.*.dosage_instructions' => 'nullable|string',
            'instructions' => 'nullable|string',
            'create_bill' => 'boolean',
        ]);

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

            // Create bill if requested
            $bill = null;
            if ($request->boolean('create_bill')) {
                $billNumber = 'BILL-' . now()->format('Ymd') . '-' . str_pad(VisitBill::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
                
                $bill = VisitBill::create([
                    'bill_number' => $billNumber,
                    'visit_id' => $visit->id,
                    'patient_id' => $visit->patient_id,
                    'bill_type' => 'pharmacy_bill',
                    'subtotal' => 0,
                    'total' => 0,
                    'payment_status' => 'pending',
                    'clearance_status' => 'pending',
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'created_by' => $user->id,
                ]);

                $subtotal = 0;
                foreach ($validated['items'] as $itemData) {
                    $product = Item::find($itemData['product_id']);
                    $quantity = $itemData['quantity_dispensed'];
                    $itemTotal = $product->unit_price * $quantity;
                    $subtotal += $itemTotal;

                    VisitBillItem::create([
                        'bill_id' => $bill->id,
                        'item_type' => 'product',
                        'service_id' => null,
                        'product_id' => $product->id,
                        'item_name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $product->unit_price,
                        'total' => $itemTotal,
                    ]);
                }

                $bill->subtotal = $subtotal;
                $bill->total = $subtotal;
                $bill->balance = $subtotal;
                $bill->save();
            }

            // Create dispensation
            $dispensation = PharmacyDispensation::create([
                'dispensation_number' => $dispensationNumber,
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'bill_id' => $bill ? $bill->id : null,
                'status' => 'pending',
                'instructions' => $validated['instructions'] ?? null,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]);

            // Create dispensation items
            foreach ($validated['items'] as $itemData) {
                $product = Item::find($itemData['product_id']);
                $status = $itemData['quantity_dispensed'] > 0 ? 'pending' : 'cancelled';
                
                if ($itemData['quantity_dispensed'] < $itemData['quantity_prescribed']) {
                    $status = 'partial';
                }

                PharmacyDispensationItem::create([
                    'dispensation_id' => $dispensation->id,
                    'product_id' => $product->id,
                    'quantity_prescribed' => $itemData['quantity_prescribed'],
                    'quantity_dispensed' => $itemData['quantity_dispensed'],
                    'dosage_instructions' => $itemData['dosage_instructions'] ?? null,
                    'status' => $status,
                ]);
            }

            DB::commit();

            return redirect()->route('hospital.pharmacy.show', $dispensation->id)
                ->with('success', 'Dispensation created successfully.');
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
     * Dispense medications
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
            $locationId = session('location_id') ?? $user->location_id ?? null;

            // Check stock and create inventory movements
            foreach ($dispensation->items as $item) {
                if ($item->quantity_dispensed > 0 && $item->product) {
                    $product = $item->product;
                    
                    if ($product->track_stock && $locationId) {
                        $availableStock = $this->stockService->getItemStockAtLocation($product->id, $locationId);
                        
                        if ($item->quantity_dispensed > $availableStock) {
                            DB::rollBack();
                            return back()->withErrors(['error' => "Insufficient stock for {$product->name}. Available: {$availableStock}, Required: {$item->quantity_dispensed}"]);
                        }

                        // Calculate balance before and after
                        $balanceBefore = $availableStock;
                        $balanceAfter = $balanceBefore - $item->quantity_dispensed;
                        $unitCost = $product->cost_price ?? 0;
                        $totalCost = $item->quantity_dispensed * $unitCost;

                        // Create inventory movement (sold)
                        \App\Models\Inventory\Movement::create([
                            'branch_id' => $user->branch_id,
                            'location_id' => $locationId,
                            'item_id' => $product->id,
                            'user_id' => $user->id,
                            'movement_type' => 'sold',
                            'quantity' => $item->quantity_dispensed,
                            'unit_price' => $product->unit_price ?? 0,
                            'unit_cost' => $unitCost,
                            'total_cost' => $totalCost,
                            'balance_before' => $balanceBefore,
                            'balance_after' => $balanceAfter,
                            'reference_type' => 'pharmacy_dispensation',
                            'reference_id' => $dispensation->id,
                            'notes' => "Dispensed to patient: {$dispensation->patient->full_name}",
                            'movement_date' => now(),
                        ]);
                    }

                    // Update item status
                    if ($item->quantity_dispensed >= $item->quantity_prescribed) {
                        $item->status = 'dispensed';
                    } elseif ($item->quantity_dispensed > 0) {
                        $item->status = 'partial';
                    }
                    $item->save();
                }
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
                ->with('success', 'Medications dispensed successfully. Stock updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pharmacy dispense error: ' . $e->getMessage());
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
}
