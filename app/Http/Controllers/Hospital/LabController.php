<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\LabResult;
use App\Models\Hospital\VisitBill;
use App\Models\Hospital\VisitBillItem;
use App\Models\Hospital\HospitalDepartment;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesInvoiceItem;
use App\Models\Customer;
use App\Models\Inventory\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LabController extends Controller
{
    /**
     * Display lab dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Get visits waiting for lab (bills must be cleared OR paid SalesInvoice)
        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'bills'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'lab');
                })->where('status', 'waiting');
            })
            ->where(function ($query) use ($companyId, $branchId) {
                // Either has cleared VisitBill (old flow)
                $query->whereHas('bills', function ($q) {
                    $q->where('clearance_status', 'cleared');
                })
                // OR has Customer with paid SalesInvoice matching patient (new pre-billing flow)
                ->orWhereExists(function ($subQuery) use ($companyId, $branchId) {
                    $subQuery->select(DB::raw(1))
                        ->from('sales_invoices')
                        ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
                        ->join('patients', 'patients.id', '=', 'visits.patient_id')
                        ->where('sales_invoices.company_id', $companyId)
                        ->where('sales_invoices.branch_id', $branchId)
                        ->where('sales_invoices.status', 'paid')
                        ->where(function ($q) {
                            $q->whereColumn('customers.phone', 'patients.phone')
                                ->orWhereColumn('customers.email', 'patients.email')
                                ->orWhereColumn('customers.name', DB::raw("CONCAT(patients.first_name, ' ', patients.last_name)"));
                        });
                });
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get visits in service at lab
        $inServiceVisits = Visit::with(['patient', 'visitDepartments.department', 'labResults'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'lab');
                })->where('status', 'in_service');
            })
            ->orderBy('visit_date', 'asc')
            ->get();
        
        // For each visit, get lab tests count from SalesInvoice
        foreach ($inServiceVisits as $visit) {
            $patient = $visit->patient;
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
                    $labInvoice = SalesInvoice::where('customer_id', $customer->id)
                        ->where('company_id', $patient->company_id)
                        ->where('branch_id', $patient->branch_id)
                        ->where('status', 'paid')
                        ->where('notes', 'like', "%Lab test bill for Visit #{$visit->visit_number}%")
                        ->withCount('items')
                        ->first();
                    
                    // Add lab tests count to visit (for display in view)
                    $visit->lab_tests_count = $labInvoice ? $labInvoice->items_count : 0;
                } else {
                    $visit->lab_tests_count = 0;
                }
            } else {
                $visit->lab_tests_count = 0;
            }
        }

        // Get ready results (for printing)
        $readyResults = LabResult::with(['patient', 'visit'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('result_status', 'ready')
            ->orderBy('completed_at', 'desc')
            ->get();

        // Get statistics
        $stats = [
            'waiting' => $waitingVisits->count(),
            'in_service' => $inServiceVisits->count(),
            'ready_results' => $readyResults->count(),
            'completed_today' => LabResult::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->whereDate('completed_at', today())
                ->count(),
        ];

        return view('hospital.lab.index', compact('waitingVisits', 'inServiceVisits', 'readyResults', 'stats'));
    }

    /**
     * Show lab test form for a visit
     */
    public function create($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills', 'labResults'])
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
        $labInvoice = null;
        $labInvoiceItems = collect();
        
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
                // Get paid SalesInvoice for lab tests (check notes for visit number)
                $labInvoice = SalesInvoice::where('customer_id', $customer->id)
                    ->where('company_id', $patient->company_id)
                    ->where('branch_id', $patient->branch_id)
                    ->where('status', 'paid')
                    ->where('notes', 'like', "%Lab test bill for Visit #{$visit->visit_number}%")
                    ->with(['items.inventoryItem'])
                    ->first();
                
                if ($labInvoice) {
                    $hasPaidInvoice = true;
                    $labInvoiceItems = $labInvoice->items;
                }
            }
        }
        
        if (!$hasClearedBill && !$hasPaidInvoice) {
            return redirect()->route('hospital.lab.index')
                ->withErrors(['error' => 'Patient bill must be cleared or paid before lab tests.']);
        }

        // Get existing lab results for this visit
        $existingResults = LabResult::where('visit_id', $visit->id)
            ->get()
            ->keyBy('service_id'); // Key by service_id for easy lookup

        return view('hospital.lab.create', compact('visit', 'labInvoice', 'labInvoiceItems', 'existingResults'));
    }

    /**
     * Store lab result
     */
    public function store(Request $request, $visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'results' => 'required|array|min:1',
            'results.*.service_id' => 'required|exists:inventory_items,id',
            'results.*.test_name' => 'required|string|max:255',
            'results.*.result_value' => 'nullable|string',
            'results.*.unit' => 'nullable|string|max:50',
            'results.*.reference_range' => 'nullable|string|max:255',
            'results.*.status' => 'nullable|in:normal,abnormal,critical',
            'results.*.notes' => 'nullable|string',
            'results.*.result_status' => 'required|in:pending,ready',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Create lab results for each test
            $allReady = true;
            $resultCounter = LabResult::whereDate('created_at', today())->count();
            
            foreach ($validated['results'] as $resultData) {
                if (empty($resultData['service_id']) || empty($resultData['test_name'])) {
                    continue; // Skip empty results
                }
                
                // Check if result already exists for this service
                $existingResult = LabResult::where('visit_id', $visit->id)
                    ->where('service_id', $resultData['service_id'])
                    ->first();
                
                if ($existingResult) {
                    // Update existing result
                    $existingResult->update([
                        'test_name' => $resultData['test_name'],
                        'result_value' => $resultData['result_value'] ?? null,
                        'unit' => $resultData['unit'] ?? null,
                        'reference_range' => $resultData['reference_range'] ?? null,
                        'status' => $resultData['status'] ?? null,
                        'notes' => $resultData['notes'] ?? null,
                        'result_status' => $resultData['result_status'],
                        'completed_at' => $resultData['result_status'] === 'ready' ? now() : null,
                        'performed_by' => $user->id,
                    ]);
                    
                    if ($resultData['result_status'] !== 'ready') {
                        $allReady = false;
                    }
                } else {
                    // Create new result
                    $resultCounter++;
                    $resultNumber = 'LAB-' . now()->format('Ymd') . '-' . str_pad($resultCounter, 4, '0', STR_PAD_LEFT);
                    
                    LabResult::create([
                        'result_number' => $resultNumber,
                        'visit_id' => $visit->id,
                        'patient_id' => $visit->patient_id,
                        'service_id' => $resultData['service_id'],
                        'test_name' => $resultData['test_name'],
                        'result_value' => $resultData['result_value'] ?? null,
                        'unit' => $resultData['unit'] ?? null,
                        'reference_range' => $resultData['reference_range'] ?? null,
                        'status' => $resultData['status'] ?? null,
                        'notes' => $resultData['notes'] ?? null,
                        'result_status' => $resultData['result_status'],
                        'completed_at' => $resultData['result_status'] === 'ready' ? now() : null,
                        'performed_by' => $user->id,
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                    ]);
                    
                    if ($resultData['result_status'] !== 'ready') {
                        $allReady = false;
                    }
                }
            }

            // Update lab visit department status to completed if all results are ready
            if ($allReady) {
                $labDept = $visit->visitDepartments()
                    ->whereHas('department', function ($q) {
                        $q->where('type', 'lab');
                    })
                    ->first();

                if ($labDept && $labDept->status === 'in_service') {
                    $labDept->status = 'completed';
                    $labDept->service_ended_at = now();
                    $labDept->calculateServiceTime();
                    $labDept->save();
                }
            }

            DB::commit();

            // Route patient back to doctor if all results are ready
            if ($allReady) {
                $doctorDept = HospitalDepartment::where('company_id', $companyId)
                    ->where('type', 'doctor')
                    ->first();
                
                if ($doctorDept) {
                    // Check if doctor department already assigned
                    $existingDept = $visit->visitDepartments()
                        ->where('department_id', $doctorDept->id)
                        ->first();
                    
                    if (!$existingDept) {
                        $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                        
                        VisitDepartment::create([
                            'visit_id' => $visit->id,
                            'department_id' => $doctorDept->id,
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
            }
            
            DB::commit();
            
            return redirect()->route('hospital.lab.index')
                ->with('success', 'Lab results recorded successfully.' . ($allReady ? ' Patient has been sent back to doctor.' : ''));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to record lab result: ' . $e->getMessage()]);
        }
    }

    /**
     * Show lab result details
     */
    public function show($id)
    {
        $labResult = LabResult::with([
            'patient',
            'visit',
            'performedBy',
        ])->findOrFail($id);

        // Verify access
        if ($labResult->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to lab result.');
        }

        return view('hospital.lab.show', compact('labResult'));
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

        // Find lab department
        $labDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'lab');
            })
            ->where('status', 'waiting')
            ->first();

        if (!$labDept) {
            return back()->withErrors(['error' => 'Lab department not found or already started.']);
        }

        try {
            $labDept->status = 'in_service';
            $labDept->service_started_at = now();
            $labDept->served_by = Auth::id();
            $labDept->calculateWaitingTime();
            $labDept->save();

            return redirect()->route('hospital.lab.index')
                ->with('success', 'Lab service started.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to start service: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark result as ready
     */
    public function markReady($id)
    {
        $labResult = LabResult::findOrFail($id);

        // Verify access
        if ($labResult->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to lab result.');
        }

        try {
            $labResult->result_status = 'ready';
            $labResult->completed_at = now();
            $labResult->save();

            // Update lab visit department status to completed
            $visit = $labResult->visit;
            $labDept = $visit->visitDepartments()
                ->whereHas('department', function ($q) {
                    $q->where('type', 'lab');
                })
                ->where('status', 'in_service')
                ->first();

            if ($labDept) {
                $labDept->status = 'completed';
                $labDept->service_ended_at = now();
                $labDept->calculateServiceTime();
                $labDept->save();
            }

            return redirect()->route('hospital.lab.show', $labResult->id)
                ->with('success', 'Lab result marked as ready.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to mark result as ready: ' . $e->getMessage()]);
        }
    }

    /**
     * Print result
     */
    public function printResult($id)
    {
        $labResult = LabResult::with(['patient', 'visit'])->findOrFail($id);

        // Verify access
        if ($labResult->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to lab result.');
        }

        // Mark as printed
        $labResult->result_status = 'printed';
        $labResult->printed_at = now();
        $labResult->save();

        return view('hospital.lab.print', compact('labResult'));
    }
}
