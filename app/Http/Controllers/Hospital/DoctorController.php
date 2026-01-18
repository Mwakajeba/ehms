<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\Consultation;
use App\Models\Hospital\HospitalDepartment;
use App\Models\Hospital\VisitBill;
use App\Models\Hospital\VisitBillItem;
use App\Models\Hospital\DiagnosisExplanation;
use App\Models\Inventory\Item;
use App\Models\Customer;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesInvoiceItem;
use App\Services\InventoryStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    protected $stockService;

    public function __construct(InventoryStockService $stockService)
    {
        $this->stockService = $stockService;
    }
    /**
     * Display doctor dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Get visits waiting for doctor (bills must be cleared and triage completed)
        // Show visits that either have no bills OR have at least one cleared bill
        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'triageVitals', 'bills'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'doctor');
                })->where('status', 'waiting');
            })
            ->where(function ($query) {
                // Either no bills exist, or at least one bill is cleared
                $query->doesntHave('bills')
                      ->orWhereHas('bills', function ($q) {
                          $q->where('clearance_status', 'cleared');
                      });
            })
            ->whereHas('triageVitals') // Must have completed triage
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get visits in service at doctor
        $inServiceVisits = Visit::with(['patient', 'visitDepartments.department', 'consultation'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'doctor');
                })->where('status', 'in_service');
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get statistics - use actual collections for accuracy (same data as displayed in tables)
        $stats = [
            'waiting' => $waitingVisits->count(),
            'in_service' => $inServiceVisits->count(),
            'completed_today' => Visit::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->whereHas('consultation', function ($q) {
                    $q->whereDate('created_at', today());
                })
                ->count(),
        ];

        return view('hospital.doctor.index', compact('waitingVisits', 'inServiceVisits', 'stats'));
    }

    /**
     * Show consultation form for a visit
     */
    public function create($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'triageVitals', 'bills', 'labResults.service', 'labResults.performedBy', 'ultrasoundResults.service', 'ultrasoundResults.performedBy', 'dentalRecords.service', 'dentalRecords.performedBy', 'vaccinationRecords.item', 'vaccinationRecords.performedBy', 'injectionRecords.item', 'injectionRecords.performedBy', 'diagnosisExplanation', 'pharmacyDispensations.items.product', 'pharmacyDispensations.dispensedBy'])
            ->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        // Check if bill is cleared (VisitBill) OR has paid SalesInvoice (pre-billing)
        $hasClearedBill = $visit->bills()->where('clearance_status', 'cleared')->exists();
        
        // Check if patient has paid SalesInvoice (match Customer by name/phone/email)
        $patient = $visit->patient;
        $hasPaidInvoice = false;
        $paidInvoices = collect();
        
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
                // Get all paid invoices for this visit (check notes for visit number)
                $paidInvoices = SalesInvoice::where('customer_id', $customer->id)
                    ->where('company_id', $patient->company_id)
                    ->where('branch_id', $patient->branch_id)
                    ->where('status', 'paid')
                    ->where(function ($q) use ($visit) {
                        $q->where('notes', 'like', "%Visit #{$visit->visit_number}%")
                          ->orWhere('notes', 'like', "%for Visit #{$visit->visit_number}%");
                    })
                    ->with(['items.inventoryItem', 'customer', 'receipts'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                $hasPaidInvoice = $paidInvoices->isNotEmpty();
            }
        }
        
        if (!$hasClearedBill && !$hasPaidInvoice) {
            return redirect()->route('hospital.doctor.index')
                ->withErrors(['error' => 'Patient bill must be cleared or paid before consultation.']);
        }

        // Get pre-billing services (all services from inventory_items with item_type = 'service')
        $preBillingServices = Item::where('company_id', Auth::user()->company_id)
            ->where('item_type', 'service')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hospital.doctor.create', compact('visit', 'preBillingServices', 'paidInvoices'));
    }

    /**
     * Show form to create lab test bill
     */
    public function createLabBill($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'triageVitals', 'bills'])
            ->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        // Check if bill is cleared (VisitBill) OR has paid SalesInvoice (pre-billing)
        $hasClearedBill = $visit->bills()->where('clearance_status', 'cleared')->exists();
        
        // Check if patient has paid SalesInvoice (match Customer by name/phone/email)
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
                    ->exists();
            }
        }
        
        if (!$hasClearedBill && !$hasPaidInvoice) {
            return redirect()->route('hospital.doctor.index')
                ->withErrors(['error' => 'Patient bill must be cleared or paid before creating lab test bill.']);
        }

        // Get all services from inventory_items (item_type = 'service')
        $services = Item::where('company_id', Auth::user()->company_id)
            ->where('item_type', 'service')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get available departments for routing (exclude triage, reception, cashier, doctor)
        $departments = HospitalDepartment::active()
            ->where('company_id', Auth::user()->company_id)
            ->whereNotIn('type', ['triage', 'reception', 'cashier', 'doctor'])
            ->orderBy('name')
            ->get();

        return view('hospital.doctor.create-lab-bill', compact('visit', 'services', 'departments'));
    }

    /**
     * Store lab test bill and route to cashier
     */
    public function storeLabBill(Request $request, $visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'route_to_departments' => 'nullable|array',
            'route_to_departments.*' => 'exists:hospital_departments,id',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Get or create customer from patient
            $customer = $this->getOrCreateCustomerFromPatient($visit->patient);

            // Create sales invoice for lab test bill
            $invoice = SalesInvoice::create([
                'customer_id' => $customer->id,
                'invoice_date' => now(),
                'due_date' => now(), // Due immediately
                'status' => 'draft',
                'currency' => 'TZS',
                'exchange_rate' => 1.000000,
                'branch_id' => $branchId,
                'company_id' => $companyId,
                'created_by' => $user->id,
                'notes' => "Lab test bill for Visit #{$visit->visit_number} - Patient: {$visit->patient->full_name}",
            ]);

            $subtotal = 0;

            // Add items to invoice
            foreach ($validated['items'] as $itemData) {
                if (empty($itemData['inventory_item_id'])) {
                    continue;
                }

                $service = Item::find($itemData['inventory_item_id']);
                if (!$service || $service->item_type !== 'service') {
                    continue;
                }

                $quantity = $itemData['quantity'] ?? 1;
                $unitPrice = $itemData['unit_price'] ?? $service->unit_price;
                $lineTotal = $quantity * $unitPrice;
                $vatType = $itemData['vat_type'] ?? 'no_vat';
                $vatRate = $itemData['vat_rate'] ?? 0;
                $vatAmount = 0;
                
                if ($vatType === 'inclusive' || $vatType === 'exclusive') {
                    if ($vatType === 'inclusive') {
                        $vatAmount = $lineTotal * ($vatRate / (100 + $vatRate));
                    } else {
                        $vatAmount = $lineTotal * ($vatRate / 100);
                    }
                }

                $subtotal += $lineTotal;

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'inventory_item_id' => $service->id,
                    'item_name' => $service->name,
                    'item_code' => $service->code,
                    'description' => $service->description,
                    'unit_of_measure' => $service->unit_of_measure,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'vat_type' => $vatType,
                    'vat_rate' => $vatRate,
                    'vat_amount' => $vatAmount,
                    'discount_type' => $itemData['discount_type'] ?? null,
                    'discount_rate' => $itemData['discount_rate'] ?? 0,
                    'discount_amount' => $itemData['discount_amount'] ?? 0,
                ]);
            }

            $invoice->updateTotals(); // Recalculate totals
            $invoice->createDoubleEntryTransactions(); // Post to GL

            // Route to cashier for payment
            $cashierDept = HospitalDepartment::where('company_id', $companyId)
                ->where('type', 'cashier')
                ->first();

            if ($cashierDept) {
                // Check if cashier department already assigned
                $existingDept = $visit->visitDepartments()
                    ->where('department_id', $cashierDept->id)
                    ->first();

                if (!$existingDept) {
                    $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                    
                    VisitDepartment::create([
                        'visit_id' => $visit->id,
                        'department_id' => $cashierDept->id,
                        'status' => 'waiting',
                        'waiting_started_at' => now(),
                        'sequence' => $maxSequence + 1,
                    ]);
                } else {
                    // Reset to waiting if already exists
                    $existingDept->status = 'waiting';
                    $existingDept->waiting_started_at = now();
                    $existingDept->save();
                }
            }

            // Route to additional departments if specified
            if (!empty($validated['route_to_departments'])) {
                $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                $sequence = $maxSequence + 1;

                foreach ($validated['route_to_departments'] as $deptId) {
                    // Check if department already assigned
                    $existing = $visit->visitDepartments()
                        ->where('department_id', $deptId)
                        ->first();

                    if (!$existing) {
                        VisitDepartment::create([
                            'visit_id' => $visit->id,
                            'department_id' => $deptId,
                            'status' => 'waiting',
                            'waiting_started_at' => now(),
                            'sequence' => $sequence++,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('sales.invoices.show', $invoice->encoded_id)
                ->with('success', 'Lab test bill created successfully. Patient has been sent to cashier for payment.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create lab test bill: ' . $e->getMessage()]);
        }
    }

    /**
     * Store consultation
     */
    public function store(Request $request, $visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'chief_complaint' => 'nullable|string',
            'history_of_present_illness' => 'nullable|string',
            'physical_examination' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'prescription' => 'nullable|string',
            'notes' => 'nullable|string',
            'route_to_departments' => 'nullable|array',
            'route_to_departments.*' => 'exists:hospital_departments,id',
            'medicines' => 'nullable|array',
            'medicines.*.product_id' => 'required|exists:inventory_items,id',
            'medicines.*.quantity' => 'required|numeric|min:1',
            'lab_tests' => 'nullable|array',
            'lab_tests.*.service_id' => 'required|exists:inventory_items,id',
            'ultrasound_services' => 'nullable|array',
            'ultrasound_services.*.service_id' => 'required|exists:inventory_items,id',
            'other_services' => 'nullable|array',
            'other_services.*.service_id' => 'required|exists:inventory_items,id',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Create consultation
            $consultation = Consultation::create([
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'chief_complaint' => $validated['chief_complaint'] ?? null,
                'history_of_present_illness' => $validated['history_of_present_illness'] ?? null,
                'physical_examination' => $validated['physical_examination'] ?? null,
                'diagnosis' => $validated['diagnosis'] ?? null,
                'treatment_plan' => $validated['treatment_plan'] ?? null,
                'prescription' => $validated['prescription'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'doctor_id' => $user->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]);

            // Update doctor visit department status to completed
            $doctorDept = $visit->visitDepartments()
                ->whereHas('department', function ($q) {
                    $q->where('type', 'doctor');
                })
                ->first();

            if ($doctorDept) {
                $doctorDept->status = 'completed';
                $doctorDept->service_ended_at = now();
                $doctorDept->calculateServiceTime();
                $doctorDept->save();
            }

            // Route to additional departments if specified
            if (!empty($validated['route_to_departments'])) {
                $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                $sequence = $maxSequence + 1;

                foreach ($validated['route_to_departments'] as $deptId) {
                    // Check if department already assigned
                    $existing = $visit->visitDepartments()
                        ->where('department_id', $deptId)
                        ->first();

                    if (!$existing) {
                        VisitDepartment::create([
                            'visit_id' => $visit->id,
                            'department_id' => $deptId,
                            'status' => 'waiting',
                            'waiting_started_at' => now(),
                            'sequence' => $sequence++,
                        ]);
                    }
                }
            }

            // Create bill for selected medicines, lab tests, ultrasound, and other services
            $locationId = session('location_id') ?? $user->location_id ?? 1;
            $hasItems = !empty($validated['medicines']) || 
                       !empty($validated['lab_tests']) || 
                       !empty($validated['ultrasound_services']) || 
                       !empty($validated['other_services']);

            if ($hasItems) {
                // Get or create final bill
                $finalBill = VisitBill::where('visit_id', $visit->id)
                    ->where('bill_type', 'final')
                    ->first();

                if (!$finalBill) {
                    $billNumber = 'BILL-' . now()->format('Ymd') . '-' . str_pad(VisitBill::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
                    
                    $finalBill = VisitBill::create([
                        'bill_number' => $billNumber,
                        'visit_id' => $visit->id,
                        'patient_id' => $visit->patient_id,
                        'bill_type' => 'final',
                        'subtotal' => 0,
                        'discount' => 0,
                        'tax' => 0,
                        'total' => 0,
                        'paid' => 0,
                        'balance' => 0,
                        'payment_status' => 'pending',
                        'clearance_status' => 'pending',
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                        'created_by' => $user->id,
                    ]);
                }

                $subtotal = 0;

                // Add medicines to bill
                if (!empty($validated['medicines'])) {
                    foreach ($validated['medicines'] as $medicineData) {
                        $product = Item::find($medicineData['product_id']);
                        if (!$product || $product->item_type !== 'product') {
                            continue;
                        }

                        $quantity = $medicineData['quantity'] ?? 1;
                        
                        // Check stock availability
                        $availableStock = $this->stockService->getItemStockAtLocation($product->id, $locationId);
                        if ($quantity > $availableStock) {
                            DB::rollBack();
                            return back()->withInput()->withErrors([
                                'error' => "Insufficient stock for {$product->name}. Available: {$availableStock}, Required: {$quantity}"
                            ]);
                        }

                        $itemTotal = $product->unit_price * $quantity;
                        $subtotal += $itemTotal;

                        VisitBillItem::create([
                            'bill_id' => $finalBill->id,
                            'item_type' => 'product',
                            'product_id' => $product->id,
                            'item_name' => $product->name,
                            'quantity' => $quantity,
                            'unit_price' => $product->unit_price,
                            'total' => $itemTotal,
                        ]);
                    }
                }

                // Add lab tests to bill
                if (!empty($validated['lab_tests'])) {
                    foreach ($validated['lab_tests'] as $labData) {
                        $service = Item::find($labData['service_id']);
                        if (!$service || $service->item_type !== 'service') {
                            continue;
                        }

                        // Check if already in bill
                        $existingItem = VisitBillItem::where('bill_id', $finalBill->id)
                            ->where('service_id', $service->id)
                            ->first();

                        if (!$existingItem) {
                            $itemTotal = $service->unit_price;
                            $subtotal += $itemTotal;

                            VisitBillItem::create([
                                'bill_id' => $finalBill->id,
                                'item_type' => 'service',
                                'service_id' => $service->id,
                                'item_name' => $service->name,
                                'quantity' => 1,
                                'unit_price' => $service->unit_price,
                                'total' => $itemTotal,
                            ]);
                        }
                    }
                }

                // Add ultrasound services to bill
                if (!empty($validated['ultrasound_services'])) {
                    foreach ($validated['ultrasound_services'] as $ultrasoundData) {
                        $service = Item::find($ultrasoundData['service_id']);
                        if (!$service || $service->item_type !== 'service') {
                            continue;
                        }

                        // Check if already in bill
                        $existingItem = VisitBillItem::where('bill_id', $finalBill->id)
                            ->where('service_id', $service->id)
                            ->first();

                        if (!$existingItem) {
                            $itemTotal = $service->unit_price;
                            $subtotal += $itemTotal;

                            VisitBillItem::create([
                                'bill_id' => $finalBill->id,
                                'item_type' => 'service',
                                'service_id' => $service->id,
                                'item_name' => $service->name,
                                'quantity' => 1,
                                'unit_price' => $service->unit_price,
                                'total' => $itemTotal,
                            ]);
                        }
                    }
                }

                // Add other services to bill
                if (!empty($validated['other_services'])) {
                    foreach ($validated['other_services'] as $serviceData) {
                        $service = Item::find($serviceData['service_id']);
                        if (!$service || $service->item_type !== 'service') {
                            continue;
                        }

                        // Check if already in bill
                        $existingItem = VisitBillItem::where('bill_id', $finalBill->id)
                            ->where('service_id', $service->id)
                            ->first();

                        if (!$existingItem) {
                            $itemTotal = $service->unit_price;
                            $subtotal += $itemTotal;

                            VisitBillItem::create([
                                'bill_id' => $finalBill->id,
                                'item_type' => 'service',
                                'service_id' => $service->id,
                                'item_name' => $service->name,
                                'quantity' => 1,
                                'unit_price' => $service->unit_price,
                                'total' => $itemTotal,
                            ]);
                        }
                    }
                }

                // Update bill totals
                $finalBill->subtotal = $subtotal;
                $finalBill->total = $subtotal;
                $finalBill->balance = $subtotal;
                $finalBill->calculateTotals();
            }

            DB::commit();

            return redirect()->route('hospital.doctor.show', $visit->id)
                ->with('success', 'Consultation recorded and patient routed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to record consultation: ' . $e->getMessage()]);
        }
    }

    /**
     * Show consultation details
     */
    public function show($visitId)
    {
        $visit = Visit::with([
            'patient',
            'triageVitals',
            'consultation.doctor',
            'visitDepartments.department',
        ])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        return view('hospital.doctor.show', compact('visit'));
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

        // Find doctor department
        $doctorDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'doctor');
            })
            ->where('status', 'waiting')
            ->first();

        if (!$doctorDept) {
            return back()->withErrors(['error' => 'Doctor department not found or already started.']);
        }

        try {
            $doctorDept->status = 'in_service';
            $doctorDept->service_started_at = now();
            $doctorDept->served_by = Auth::id();
            $doctorDept->calculateWaitingTime();
            $doctorDept->save();

            return redirect()->route('hospital.doctor.index')
                ->with('success', 'Consultation service started.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to start service: ' . $e->getMessage()]);
        }
    }

    /**
     * Get or create customer from patient
     */
    private function getOrCreateCustomerFromPatient($patient)
    {
        // Check if customer already exists with same phone, email, or name
        $customer = Customer::where('company_id', $patient->company_id)
            ->where(function ($q) use ($patient) {
                if ($patient->phone) {
                    $q->where('phone', $patient->phone);
                }
                if ($patient->email) {
                    $q->orWhere('email', $patient->email);
                }
                // If no phone or email, search by name to avoid duplicates
                if (!$patient->phone && !$patient->email) {
                    $q->orWhere('name', $patient->full_name);
                }
            })
            ->first();

        if ($customer) {
            return $customer;
        }

        // Create new customer from patient
        // Use placeholder phone if patient phone is null (Customer requires phone)
        // Format: MRN-based or timestamp-based to ensure uniqueness
        $phone = $patient->phone ?? ('000' . str_pad($patient->id ?? time(), 9, '0', STR_PAD_LEFT)); // 12 digits
        
        // Generate customerNo explicitly (same as CustomerController)
        $customerNo = 100000 + (Customer::max('id') ?? 0) + 1;
        
        $customer = Customer::create([
            'customerNo' => $customerNo,
            'name' => $patient->full_name,
            'phone' => $phone,
            'email' => $patient->email,
            'company_id' => $patient->company_id,
            'branch_id' => $patient->branch_id,
            'status' => 'active',
        ]);

        return $customer;
    }

    /**
     * Store pre-billing services as Sales Invoice
     */
    public function storePreBill(Request $request, $visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:inventory_items,id',
            'services.*.quantity' => 'nullable|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Get or create customer from patient
            $customer = $this->getOrCreateCustomerFromPatient($visit->patient);

            // Create sales invoice for pre-billing services
            $invoice = SalesInvoice::create([
                'customer_id' => $customer->id,
                'invoice_date' => now(),
                'due_date' => now(), // Pre-bills are due immediately
                'status' => 'draft',
                'currency' => 'TZS',
                'exchange_rate' => 1.000000,
                'branch_id' => $branchId,
                'company_id' => $companyId,
                'created_by' => $user->id,
                'notes' => "Pre-billing services for Visit #{$visit->visit_number} - Patient: {$visit->patient->full_name}",
            ]);

            $subtotal = 0;

            // Add services to invoice
            foreach ($validated['services'] as $serviceData) {
                if (empty($serviceData['service_id'])) {
                    continue;
                }

                $service = Item::find($serviceData['service_id']);
                if (!$service || $service->item_type !== 'service') {
                    continue;
                }

                $quantity = $serviceData['quantity'] ?? 1;
                $unitPrice = $service->unit_price;
                $lineTotal = $unitPrice * $quantity;
                $subtotal += $lineTotal;

                        // Create invoice item
                        SalesInvoiceItem::create([
                            'sales_invoice_id' => $invoice->id,
                            'inventory_item_id' => $service->id,
                            'item_name' => $service->name,
                            'item_code' => $service->code,
                            'description' => $service->description,
                            'unit_of_measure' => $service->unit_of_measure ?? 'Unit',
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'line_total' => $lineTotal,
                            'vat_type' => 'no_vat',
                            'vat_rate' => 0,
                            'vat_amount' => 0,
                            'discount_type' => null,
                            'discount_rate' => 0,
                            'discount_amount' => 0,
                        ]);
            }

            // Update invoice totals
            $invoice->subtotal = $subtotal;
            $invoice->vat_amount = 0;
            $invoice->discount_amount = 0;
            $invoice->total_amount = $subtotal;
            $invoice->balance_due = $subtotal;
            $invoice->status = 'sent'; // Mark as sent to cashier
            $invoice->save();

            // Create GL transactions (double-entry accounting)
            $invoice->createDoubleEntryTransactions();

            // Route patient to cashier for payment
            $cashierDept = HospitalDepartment::where('company_id', $companyId)
                ->where('type', 'cashier')
                ->first();

            if ($cashierDept) {
                $existingDept = $visit->visitDepartments()
                    ->where('department_id', $cashierDept->id)
                    ->first();

                if (!$existingDept) {
                    $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                    
                    VisitDepartment::create([
                        'visit_id' => $visit->id,
                        'department_id' => $cashierDept->id,
                        'status' => 'waiting',
                        'waiting_started_at' => now(),
                        'sequence' => $maxSequence + 1,
                    ]);
                } else {
                    $existingDept->status = 'waiting';
                    $existingDept->waiting_started_at = now();
                    $existingDept->save();
                }
            }

            // Link invoice to visit for reference
            $visit->update(['notes' => ($visit->notes ?? '') . "\n\nPre-billing Invoice: {$invoice->invoice_number}"]);

            DB::commit();

            return redirect()->route('sales.invoices.show', $invoice->encoded_id)
                ->with('success', 'Pre-billing invoice created successfully. Patient has been sent to cashier for payment.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create pre-bill: ' . $e->getMessage()]);
        }
    }

    /**
     * Show diagnosis explanation form
     */
    public function createDiagnosis($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'diagnosisExplanation'])
            ->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        return view('hospital.doctor.create-diagnosis', compact('visit'));
    }

    /**
     * Store diagnosis explanation
     */
    public function storeDiagnosis(Request $request, $visitId)
    {
        $visit = Visit::with(['patient'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'diagnosis' => 'nullable|string',
            'explanation' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Check if diagnosis explanation already exists
            $diagnosisExplanation = DiagnosisExplanation::where('visit_id', $visit->id)->first();

            if ($diagnosisExplanation) {
                // Update existing
                $diagnosisExplanation->update([
                    'diagnosis' => $validated['diagnosis'] ?? null,
                    'explanation' => $validated['explanation'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);
            } else {
                // Create new - generate explanation number
                $explanationNumber = 'DX-' . now()->format('Ymd') . '-' . str_pad(DiagnosisExplanation::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

                $diagnosisExplanation = DiagnosisExplanation::create([
                    'explanation_number' => $explanationNumber,
                    'visit_id' => $visit->id,
                    'patient_id' => $visit->patient_id,
                    'diagnosis' => $validated['diagnosis'] ?? null,
                    'explanation' => $validated['explanation'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => $user->id,
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                ]);
            }

            DB::commit();

            return redirect()->route('hospital.doctor.create', $visit->id)
                ->with('success', 'Diagnosis explanation saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to save diagnosis explanation: ' . $e->getMessage()]);
        }
    }

    /**
     * Show pharmacy bill form
     */
    public function createPharmacyBill($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills'])
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
                    ->exists();
            }
        }
        
        if (!$hasClearedBill && !$hasPaidInvoice) {
            return redirect()->route('hospital.doctor.create', $visit->id)
                ->withErrors(['error' => 'Patient bill must be cleared or paid before creating pharmacy bill.']);
        }

        // Get all products from inventory_items (item_type = 'product')
        $products = Item::where('company_id', Auth::user()->company_id)
            ->where('item_type', 'product')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hospital.doctor.create-pharmacy-bill', compact('visit', 'products'));
    }

    /**
     * Store pharmacy bill and route to cashier and pharmacy
     */
    public function storePharmacyBill(Request $request, $visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Get or create customer from patient
            $customer = $this->getOrCreateCustomerFromPatient($visit->patient);

            // Create sales invoice for pharmacy bill
            $invoice = SalesInvoice::create([
                'customer_id' => $customer->id,
                'invoice_date' => now(),
                'due_date' => now(), // Due immediately
                'status' => 'draft',
                'currency' => 'TZS',
                'exchange_rate' => 1.000000,
                'branch_id' => $branchId,
                'company_id' => $companyId,
                'created_by' => $user->id,
                'notes' => "Pharmacy bill for Visit #{$visit->visit_number} - Patient: {$visit->patient->full_name}",
            ]);

            // Add items to invoice
            foreach ($validated['items'] as $itemData) {
                $product = Item::find($itemData['inventory_item_id']);
                if (!$product || $product->item_type !== 'product') {
                    continue;
                }

                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'];
                $lineTotal = $quantity * $unitPrice;
                $description = $itemData['description'] ?? $product->description ?? '';

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'inventory_item_id' => $product->id,
                    'item_name' => $product->name,
                    'item_code' => $product->code,
                    'description' => $description,
                    'unit_of_measure' => $product->unit_of_measure,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'vat_type' => 'no_vat',
                    'vat_rate' => 0,
                    'vat_amount' => 0,
                    'discount_type' => null,
                    'discount_rate' => 0,
                    'discount_amount' => 0,
                ]);
            }

            $invoice->updateTotals(); // Recalculate totals
            $invoice->createDoubleEntryTransactions(); // Post to GL

            // Link invoice to visit for reference
            $visit->update(['notes' => ($visit->notes ?? '') . "\n\nPharmacy Invoice: {$invoice->invoice_number}"]);

            // Route patient to cashier for payment
            $cashierDept = HospitalDepartment::where('company_id', $companyId)
                ->where('type', 'cashier')
                ->first();

            if ($cashierDept) {
                $existingCashierDept = $visit->visitDepartments()
                    ->where('department_id', $cashierDept->id)
                    ->first();

                if (!$existingCashierDept) {
                    $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                    VisitDepartment::create([
                        'visit_id' => $visit->id,
                        'department_id' => $cashierDept->id,
                        'status' => 'waiting',
                        'waiting_started_at' => now(),
                        'sequence' => $maxSequence + 1,
                    ]);
                } else {
                    $existingCashierDept->status = 'waiting';
                    $existingCashierDept->waiting_started_at = now();
                    $existingCashierDept->save();
                }
            }

            // Route to pharmacy (after payment, pharmacy will see it)
            $pharmacyDept = HospitalDepartment::where('company_id', $companyId)
                ->where('type', 'pharmacy')
                ->first();

            if ($pharmacyDept) {
                $existingPharmacyDept = $visit->visitDepartments()
                    ->where('department_id', $pharmacyDept->id)
                    ->first();

                if (!$existingPharmacyDept) {
                    $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                    VisitDepartment::create([
                        'visit_id' => $visit->id,
                        'department_id' => $pharmacyDept->id,
                        'status' => 'waiting',
                        'waiting_started_at' => null, // Will be set after payment
                        'sequence' => $maxSequence + 1,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('sales.invoices.show', $invoice->encoded_id)
                ->with('success', 'Pharmacy bill created successfully. Patient has been sent to cashier for payment.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create pharmacy bill: ' . $e->getMessage()]);
        }
    }

    /**
     * Show vaccination bill form
     */
    public function createVaccinationBill($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills'])
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
                    ->exists();
            }
        }
        
        if (!$hasClearedBill && !$hasPaidInvoice) {
            return redirect()->route('hospital.doctor.create', $visit->id)
                ->withErrors(['error' => 'Patient bill must be cleared or paid before creating vaccination bill.']);
        }

        // Get all services and products from inventory_items (item_type = 'service' OR 'product')
        $items = Item::where('company_id', Auth::user()->company_id)
            ->whereIn('item_type', ['service', 'product'])
            ->where('is_active', true)
            ->orderBy('item_type')
            ->orderBy('name')
            ->get();

        return view('hospital.doctor.create-vaccination-bill', compact('visit', 'items'));
    }

    /**
     * Store vaccination bill and route to cashier and vaccination department
     */
    public function storeVaccinationBill(Request $request, $visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Get or create customer from patient
            $customer = $this->getOrCreateCustomerFromPatient($visit->patient);

            // Create sales invoice for vaccination bill
            $invoice = SalesInvoice::create([
                'customer_id' => $customer->id,
                'invoice_date' => now(),
                'due_date' => now(), // Due immediately
                'status' => 'draft',
                'currency' => 'TZS',
                'exchange_rate' => 1.000000,
                'branch_id' => $branchId,
                'company_id' => $companyId,
                'created_by' => $user->id,
                'notes' => "Vaccination bill for Visit #{$visit->visit_number} - Patient: {$visit->patient->full_name}",
            ]);

            // Add items to invoice
            foreach ($validated['items'] as $itemData) {
                $item = Item::find($itemData['inventory_item_id']);
                if (!$item || !in_array($item->item_type, ['service', 'product'])) {
                    continue;
                }

                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'];
                $lineTotal = $quantity * $unitPrice;
                $description = $itemData['description'] ?? $item->description ?? '';

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'inventory_item_id' => $item->id,
                    'item_name' => $item->name,
                    'item_code' => $item->code,
                    'description' => $description,
                    'unit_of_measure' => $item->unit_of_measure,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'vat_type' => 'no_vat',
                    'vat_rate' => 0,
                    'vat_amount' => 0,
                    'discount_type' => null,
                    'discount_rate' => 0,
                    'discount_amount' => 0,
                ]);
            }

            $invoice->updateTotals(); // Recalculate totals
            $invoice->createDoubleEntryTransactions(); // Post to GL

            // Link invoice to visit for reference
            $visit->update(['notes' => ($visit->notes ?? '') . "\n\nVaccination Invoice: {$invoice->invoice_number}"]);

            // Route patient to cashier for payment
            $cashierDept = HospitalDepartment::where('company_id', $companyId)
                ->where('type', 'cashier')
                ->first();

            if ($cashierDept) {
                $existingCashierDept = $visit->visitDepartments()
                    ->where('department_id', $cashierDept->id)
                    ->first();

                if (!$existingCashierDept) {
                    $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                    VisitDepartment::create([
                        'visit_id' => $visit->id,
                        'department_id' => $cashierDept->id,
                        'status' => 'waiting',
                        'waiting_started_at' => now(),
                        'sequence' => $maxSequence + 1,
                    ]);
                } else {
                    $existingCashierDept->status = 'waiting';
                    $existingCashierDept->waiting_started_at = now();
                    $existingCashierDept->save();
                }
            }

            // Route to vaccination department (after payment, vaccination will see it)
            $vaccinationDept = HospitalDepartment::where('company_id', $companyId)
                ->where('type', 'vaccine')
                ->first();

            if ($vaccinationDept) {
                $existingVaccinationDept = $visit->visitDepartments()
                    ->where('department_id', $vaccinationDept->id)
                    ->first();

                if (!$existingVaccinationDept) {
                    $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                    VisitDepartment::create([
                        'visit_id' => $visit->id,
                        'department_id' => $vaccinationDept->id,
                        'status' => 'waiting',
                        'waiting_started_at' => null, // Will be set after payment
                        'sequence' => $maxSequence + 1,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('sales.invoices.show', $invoice->encoded_id)
                ->with('success', 'Vaccination bill created successfully. Patient has been sent to cashier for payment.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create vaccination bill: ' . $e->getMessage()]);
        }
    }

    /**
     * Show injection bill form
     */
    public function createInjectionBill($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills'])
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
                    ->exists();
            }
        }
        
        if (!$hasClearedBill && !$hasPaidInvoice) {
            return redirect()->route('hospital.doctor.create', $visit->id)
                ->withErrors(['error' => 'Patient bill must be cleared or paid before creating injection bill.']);
        }

        // Get all services and products from inventory_items (item_type = 'service' OR 'product')
        $items = Item::where('company_id', Auth::user()->company_id)
            ->whereIn('item_type', ['service', 'product'])
            ->where('is_active', true)
            ->orderBy('item_type')
            ->orderBy('name')
            ->get();

        return view('hospital.doctor.create-injection-bill', compact('visit', 'items'));
    }

    /**
     * Store injection bill and route to cashier and vaccination department
     */
    public function storeInjectionBill(Request $request, $visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Get or create customer from patient
            $customer = $this->getOrCreateCustomerFromPatient($visit->patient);

            // Create sales invoice for injection bill
            $invoice = SalesInvoice::create([
                'customer_id' => $customer->id,
                'invoice_date' => now(),
                'due_date' => now(), // Due immediately
                'status' => 'draft',
                'currency' => 'TZS',
                'exchange_rate' => 1.000000,
                'branch_id' => $branchId,
                'company_id' => $companyId,
                'created_by' => $user->id,
                'notes' => "Injection bill for Visit #{$visit->visit_number} - Patient: {$visit->patient->full_name}",
            ]);

            // Add items to invoice
            foreach ($validated['items'] as $itemData) {
                $item = Item::find($itemData['inventory_item_id']);
                if (!$item || !in_array($item->item_type, ['service', 'product'])) {
                    continue;
                }

                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'];
                $lineTotal = $quantity * $unitPrice;
                $description = $itemData['description'] ?? $item->description ?? '';

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'inventory_item_id' => $item->id,
                    'item_name' => $item->name,
                    'item_code' => $item->code,
                    'description' => $description,
                    'unit_of_measure' => $item->unit_of_measure,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'vat_type' => 'no_vat',
                    'vat_rate' => 0,
                    'vat_amount' => 0,
                    'discount_type' => null,
                    'discount_rate' => 0,
                    'discount_amount' => 0,
                ]);
            }

            $invoice->updateTotals(); // Recalculate totals
            $invoice->createDoubleEntryTransactions(); // Post to GL

            // Link invoice to visit for reference
            $visit->update(['notes' => ($visit->notes ?? '') . "\n\nInjection Invoice: {$invoice->invoice_number}"]);

            // Route patient to cashier for payment
            $cashierDept = HospitalDepartment::where('company_id', $companyId)
                ->where('type', 'cashier')
                ->first();

            if ($cashierDept) {
                $existingCashierDept = $visit->visitDepartments()
                    ->where('department_id', $cashierDept->id)
                    ->first();

                if (!$existingCashierDept) {
                    $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                    VisitDepartment::create([
                        'visit_id' => $visit->id,
                        'department_id' => $cashierDept->id,
                        'status' => 'waiting',
                        'waiting_started_at' => now(),
                        'sequence' => $maxSequence + 1,
                    ]);
                } else {
                    $existingCashierDept->status = 'waiting';
                    $existingCashierDept->waiting_started_at = now();
                    $existingCashierDept->save();
                }
            }

            // Route to vaccination department (after payment, vaccination will see it)
            $vaccinationDept = HospitalDepartment::where('company_id', $companyId)
                ->where('type', 'vaccine')
                ->first();

            if ($vaccinationDept) {
                $existingVaccinationDept = $visit->visitDepartments()
                    ->where('department_id', $vaccinationDept->id)
                    ->first();

                if (!$existingVaccinationDept) {
                    $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                    VisitDepartment::create([
                        'visit_id' => $visit->id,
                        'department_id' => $vaccinationDept->id,
                        'status' => 'waiting',
                        'waiting_started_at' => null, // Will be set after payment
                        'sequence' => $maxSequence + 1,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('sales.invoices.show', $invoice->encoded_id)
                ->with('success', 'Injection bill created successfully. Patient has been sent to cashier for payment.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create injection bill: ' . $e->getMessage()]);
        }
    }

    /**
     * Show dental bill form
     */
    public function createDentalBill($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills'])
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
                    ->exists();
            }
        }
        
        if (!$hasClearedBill && !$hasPaidInvoice) {
            return redirect()->route('hospital.doctor.create', $visit->id)
                ->withErrors(['error' => 'Patient bill must be cleared or paid before creating dental bill.']);
        }

        // Get only services from inventory_items (item_type = 'service')
        $services = Item::where('company_id', Auth::user()->company_id)
            ->where('item_type', 'service')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hospital.doctor.create-dental-bill', compact('visit', 'services'));
    }

    /**
     * Store dental bill and route to cashier and dental department
     */
    public function storeDentalBill(Request $request, $visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Get or create customer from patient
            $customer = $this->getOrCreateCustomerFromPatient($visit->patient);

            // Create sales invoice for dental bill
            $invoice = SalesInvoice::create([
                'customer_id' => $customer->id,
                'invoice_date' => now(),
                'due_date' => now(), // Due immediately
                'status' => 'draft',
                'currency' => 'TZS',
                'exchange_rate' => 1.000000,
                'branch_id' => $branchId,
                'company_id' => $companyId,
                'created_by' => $user->id,
                'notes' => "Dental bill for Visit #{$visit->visit_number} - Patient: {$visit->patient->full_name}",
            ]);

            // Add items to invoice (only services)
            foreach ($validated['items'] as $itemData) {
                $item = Item::find($itemData['inventory_item_id']);
                if (!$item || $item->item_type !== 'service') {
                    continue;
                }

                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'];
                $lineTotal = $quantity * $unitPrice;
                $description = $itemData['description'] ?? $item->description ?? '';

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'inventory_item_id' => $item->id,
                    'item_name' => $item->name,
                    'item_code' => $item->code,
                    'description' => $description,
                    'unit_of_measure' => $item->unit_of_measure,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'vat_type' => 'no_vat',
                    'vat_rate' => 0,
                    'vat_amount' => 0,
                    'discount_type' => null,
                    'discount_rate' => 0,
                    'discount_amount' => 0,
                ]);
            }

            $invoice->updateTotals(); // Recalculate totals
            $invoice->createDoubleEntryTransactions(); // Post to GL

            // Link invoice to visit for reference
            $visit->update(['notes' => ($visit->notes ?? '') . "\n\nDental Invoice: {$invoice->invoice_number}"]);

            // Route patient to cashier for payment
            $cashierDept = HospitalDepartment::where('company_id', $companyId)
                ->where('type', 'cashier')
                ->first();

            if ($cashierDept) {
                $existingCashierDept = $visit->visitDepartments()
                    ->where('department_id', $cashierDept->id)
                    ->first();

                if (!$existingCashierDept) {
                    $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                    VisitDepartment::create([
                        'visit_id' => $visit->id,
                        'department_id' => $cashierDept->id,
                        'status' => 'waiting',
                        'waiting_started_at' => now(),
                        'sequence' => $maxSequence + 1,
                    ]);
                } else {
                    $existingCashierDept->status = 'waiting';
                    $existingCashierDept->waiting_started_at = now();
                    $existingCashierDept->save();
                }
            }

            // Route to dental department (after payment, dental will see it)
            $dentalDept = HospitalDepartment::where('company_id', $companyId)
                ->where('type', 'dental')
                ->first();

            if ($dentalDept) {
                $existingDentalDept = $visit->visitDepartments()
                    ->where('department_id', $dentalDept->id)
                    ->first();

                if (!$existingDentalDept) {
                    $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                    VisitDepartment::create([
                        'visit_id' => $visit->id,
                        'department_id' => $dentalDept->id,
                        'status' => 'waiting',
                        'waiting_started_at' => null, // Will be set after payment
                        'sequence' => $maxSequence + 1,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('sales.invoices.show', $invoice->encoded_id)
                ->with('success', 'Dental bill created successfully. Patient has been sent to cashier for payment.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create dental bill: ' . $e->getMessage()]);
        }
    }
}
