<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\Consultation;
use App\Models\Hospital\HospitalDepartment;
use App\Models\Hospital\VisitBill;
use App\Models\Hospital\VisitBillItem;
use App\Models\Inventory\Item;
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
        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'triageVitals', 'bills'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'doctor');
                })->where('status', 'waiting');
            })
            ->whereHas('bills', function ($q) {
                $q->where('clearance_status', 'cleared');
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

        // Get statistics
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
        $visit = Visit::with(['patient', 'visitDepartments.department', 'triageVitals', 'bills'])
            ->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        // Check if bill is cleared
        $hasClearedBill = $visit->bills()->where('clearance_status', 'cleared')->exists();
        if (!$hasClearedBill) {
            return redirect()->route('hospital.doctor.index')
                ->withErrors(['error' => 'Patient bill must be cleared before consultation.']);
        }

        // Check if consultation already done
        if ($visit->consultation) {
            return redirect()->route('hospital.doctor.show', $visit->id)
                ->with('info', 'Consultation already completed for this visit.');
        }

        // Get available departments for routing
        $departments = HospitalDepartment::active()
            ->where('company_id', Auth::user()->company_id)
            ->where('type', '!=', 'doctor')
            ->where('type', '!=', 'triage')
            ->where('type', '!=', 'reception')
            ->where('type', '!=', 'cashier')
            ->orderBy('name')
            ->get();

        // Get medicines (products) with stock availability
        $user = Auth::user();
        $companyId = $user->company_id;
        $locationId = session('location_id') ?? $user->location_id ?? 1;
        $medicines = Item::where('company_id', $companyId)
            ->where('item_type', 'product')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($item) use ($locationId) {
                $stock = $this->stockService->getItemStockAtLocation($item->id, $locationId);
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'unit_price' => $item->unit_price,
                    'unit_of_measure' => $item->unit_of_measure,
                    'available_stock' => $stock,
                    'is_available' => $stock > 0,
                ];
            });

        // Get lab test services
        $labServices = Item::where('company_id', $companyId)
            ->where('item_type', 'service')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('name', 'like', '%lab%')
                  ->orWhere('name', 'like', '%test%')
                  ->orWhere('description', 'like', '%lab%');
            })
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'unit_price' => $item->unit_price,
                ];
            });

        // Get ultrasound services
        $ultrasoundServices = Item::where('company_id', $companyId)
            ->where('item_type', 'service')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('name', 'like', '%ultrasound%')
                  ->orWhere('name', 'like', '%scan%')
                  ->orWhere('description', 'like', '%ultrasound%');
            })
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'unit_price' => $item->unit_price,
                ];
            });

        // Get all other services (for general service selection)
        $otherServices = Item::where('company_id', $companyId)
            ->where('item_type', 'service')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'not like', '%lab%')
                          ->where('name', 'not like', '%test%')
                          ->where('name', 'not like', '%ultrasound%')
                          ->where('name', 'not like', '%scan%');
                })
                ->where(function ($query) {
                    $query->whereNull('description')
                          ->orWhere(function ($q2) {
                              $q2->where('description', 'not like', '%lab%')
                                 ->where('description', 'not like', '%ultrasound%');
                          });
                });
            })
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'unit_price' => $item->unit_price,
                ];
            });

        return view('hospital.doctor.create', compact('visit', 'departments', 'medicines', 'labServices', 'ultrasoundServices', 'otherServices'));
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
}
