<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\FamilyPlanningRecord;
use App\Models\Customer;
use App\Models\Sales\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FamilyPlanningController extends Controller
{
    /**
     * Display family planning dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Get visits waiting for family planning (bills must be cleared OR paid SalesInvoice for family planning)
        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'bills'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'family_planning');
                })->where('status', 'waiting');
            })
            ->where(function ($query) use ($companyId, $branchId) {
                // Either has cleared VisitBill (old flow)
                $query->whereHas('bills', function ($q) {
                    $q->where('clearance_status', 'cleared');
                })
                // OR has Customer with paid SalesInvoice for family planning matching patient (new pre-billing flow)
                ->orWhereExists(function ($subQuery) use ($companyId, $branchId) {
                    $subQuery->select(DB::raw(1))
                        ->from('sales_invoices')
                        ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
                        ->join('patients', 'patients.id', '=', 'visits.patient_id')
                        ->where('sales_invoices.company_id', $companyId)
                        ->where('sales_invoices.branch_id', $branchId)
                        ->where('sales_invoices.status', 'paid')
                        ->where('sales_invoices.notes', 'like', '%Family Planning bill for Visit #%')
                        ->where(function ($q) {
                            $q->whereColumn('customers.phone', 'patients.phone')
                                ->orWhereColumn('customers.email', 'patients.email')
                                ->orWhereColumn('customers.name', DB::raw("CONCAT(patients.first_name, ' ', patients.last_name)"));
                        });
                });
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get visits in service at family planning
        $inServiceVisits = Visit::with(['patient', 'visitDepartments.department', 'familyPlanningRecords'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'family_planning');
                })->where('status', 'in_service');
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get completed records today
        $completedToday = FamilyPlanningRecord::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count();

        // Get follow-up required records
        $followUpRequired = FamilyPlanningRecord::with(['patient', 'visit'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('status', 'follow_up_required')
            ->where('next_appointment_date', '>=', today())
            ->orderBy('next_appointment_date', 'asc')
            ->get();

        // Get statistics
        $stats = [
            'waiting' => $waitingVisits->count(),
            'in_service' => $inServiceVisits->count(),
            'completed_today' => $completedToday,
            'follow_up_required' => $followUpRequired->count(),
        ];

        return view('hospital.family-planning.index', compact('waitingVisits', 'inServiceVisits', 'followUpRequired', 'stats'));
    }

    /**
     * Show family planning form for a visit
     */
    public function create($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills', 'familyPlanningRecords.item', 'familyPlanningRecords.performedBy'])
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
        $familyPlanningInvoice = null;
        $familyPlanningInvoiceItems = collect();
        
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
                // Get paid SalesInvoice for family planning (check notes for visit number)
                $familyPlanningInvoice = SalesInvoice::where('customer_id', $customer->id)
                    ->where('company_id', $patient->company_id)
                    ->where('branch_id', $patient->branch_id)
                    ->where('status', 'paid')
                    ->where('notes', 'like', "%Family Planning bill for Visit #{$visit->visit_number}%")
                    ->with(['items.inventoryItem'])
                    ->first();
                
                if ($familyPlanningInvoice) {
                    $hasPaidInvoice = true;
                    $familyPlanningInvoiceItems = $familyPlanningInvoice->items;
                }
            }
        }
        
        if (!$hasClearedBill && !$hasPaidInvoice) {
            return redirect()->route('hospital.family-planning.index')
                ->withErrors(['error' => 'Patient bill must be cleared or paid before family planning service.']);
        }

        // Get existing family planning records for this visit, keyed by item_id
        $existingRecords = FamilyPlanningRecord::where('visit_id', $visit->id)
            ->get()
            ->keyBy('item_id');

        // Get family planning department status
        $familyPlanningDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'family_planning');
            })
            ->first();

        return view('hospital.family-planning.create', compact('visit', 'familyPlanningInvoice', 'familyPlanningInvoiceItems', 'existingRecords', 'familyPlanningDept'));
    }

    /**
     * Store family planning records (multiple items - services and products)
     */
    public function store(Request $request, $visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'records' => 'required|array|min:1',
            'records.*.item_id' => 'required|exists:inventory_items,id',
            'records.*.item_name' => 'nullable|string|max:255',
            'records.*.record_id' => 'nullable|exists:family_planning_records,id',
            'records.*.service_type' => 'required|string|max:255',
            'records.*.status' => 'required|in:pending,completed,follow_up_required',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            $allCompleted = true;
            $recordCounter = FamilyPlanningRecord::whereDate('created_at', today())->count();

            // Process each record
            foreach ($validated['records'] as $recordData) {
                if (empty($recordData['item_id']) || empty($recordData['service_type'])) {
                    continue; // Skip empty records
                }

                // Check if record already exists
                $existingRecord = null;
                if (!empty($recordData['record_id'])) {
                    $existingRecord = FamilyPlanningRecord::where('id', $recordData['record_id'])
                        ->where('visit_id', $visit->id)
                        ->where('item_id', $recordData['item_id'])
                        ->first();
                }

                if ($existingRecord) {
                    // Update existing record
                    $existingRecord->update([
                        'service_type' => $recordData['service_type'],
                        'status' => $recordData['status'],
                        'completed_at' => $recordData['status'] === 'completed' ? now() : null,
                        'performed_by' => $user->id,
                    ]);

                    if ($recordData['status'] !== 'completed') {
                        $allCompleted = false;
                    }
                } else {
                    // Create new record
                    $recordCounter++;
                    $recordNumber = 'FP-' . now()->format('Ymd') . '-' . str_pad($recordCounter, 4, '0', STR_PAD_LEFT);

                    FamilyPlanningRecord::create([
                        'record_number' => $recordNumber,
                        'visit_id' => $visit->id,
                        'patient_id' => $visit->patient_id,
                        'item_id' => $recordData['item_id'],
                        'service_type' => $recordData['service_type'],
                        'status' => $recordData['status'],
                        'completed_at' => $recordData['status'] === 'completed' ? now() : null,
                        'performed_by' => $user->id,
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                    ]);

                    if ($recordData['status'] !== 'completed') {
                        $allCompleted = false;
                    }
                }
            }

            // Update family planning visit department status to completed if all items are completed
            if ($allCompleted) {
                $familyPlanningDept = $visit->visitDepartments()
                    ->whereHas('department', function ($q) {
                        $q->where('type', 'family_planning');
                    })
                    ->first();

                if ($familyPlanningDept && $familyPlanningDept->status === 'in_service') {
                    $familyPlanningDept->status = 'completed';
                    $familyPlanningDept->service_ended_at = now();
                    $familyPlanningDept->calculateServiceTime();
                    $familyPlanningDept->save();
                }
            }

            DB::commit();

            return redirect()->route('hospital.family-planning.create', $visit->id)
                ->with('success', 'Family planning records saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to save family planning records: ' . $e->getMessage()]);
        }
    }

    /**
     * Show family planning record details
     */
    public function show($id)
    {
        $familyPlanningRecord = FamilyPlanningRecord::with([
            'patient',
            'visit',
            'item',
            'performedBy',
        ])->findOrFail($id);

        // Verify access
        if ($familyPlanningRecord->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to family planning record.');
        }

        return view('hospital.family-planning.show', compact('familyPlanningRecord'));
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

        // Find family planning department
        $familyPlanningDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'family_planning');
            })
            ->where('status', 'waiting')
            ->first();

        if (!$familyPlanningDept) {
            return back()->withErrors(['error' => 'Family planning department not found or already started.']);
        }

        try {
            $familyPlanningDept->status = 'in_service';
            $familyPlanningDept->service_started_at = now();
            $familyPlanningDept->served_by = Auth::id();
            $familyPlanningDept->calculateWaitingTime();
            $familyPlanningDept->save();

            return redirect()->route('hospital.family-planning.index')
                ->with('success', 'Family planning service started.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to start service: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark record as completed
     */
    public function markCompleted($id)
    {
        $familyPlanningRecord = FamilyPlanningRecord::findOrFail($id);

        // Verify access
        if ($familyPlanningRecord->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to family planning record.');
        }

        try {
            $familyPlanningRecord->status = 'completed';
            $familyPlanningRecord->completed_at = now();
            $familyPlanningRecord->save();

            // Update family planning visit department status to completed
            $visit = $familyPlanningRecord->visit;
            $familyPlanningDept = $visit->visitDepartments()
                ->whereHas('department', function ($q) {
                    $q->where('type', 'family_planning');
                })
                ->where('status', 'in_service')
                ->first();

            if ($familyPlanningDept) {
                $familyPlanningDept->status = 'completed';
                $familyPlanningDept->service_ended_at = now();
                $familyPlanningDept->calculateServiceTime();
                $familyPlanningDept->save();
            }

            return redirect()->route('hospital.family-planning.show', $familyPlanningRecord->id)
                ->with('success', 'Family planning record marked as completed.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to mark record as completed: ' . $e->getMessage()]);
        }
    }
}
