<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\TriageVital;
use App\Models\Hospital\HospitalDepartment;
use App\Models\Sales\SalesInvoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TriageController extends Controller
{
    /**
     * Display triage dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Get visits waiting for triage (bills must be cleared OR paid SalesInvoice)
        // Either VisitBill with clearance_status = 'cleared' OR Customer with paid SalesInvoice matching patient
        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'bills'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'triage');
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

        // Get visits in service at triage
        $inServiceVisits = Visit::with(['patient', 'visitDepartments.department', 'triageVitals'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'triage');
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
                ->whereHas('triageVitals')
                ->whereDate('created_at', today())
                ->count(),
        ];

        return view('hospital.triage.index', compact('waitingVisits', 'inServiceVisits', 'stats'));
    }

    /**
     * Show triage form for a visit
     */
    public function create($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills'])
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
            return redirect()->route('hospital.triage.index')
                ->withErrors(['error' => 'Patient bill must be cleared or paid before triage.']);
        }

        // Check if triage already done
        if ($visit->triageVitals) {
            return redirect()->route('hospital.triage.show', $visit->id)
                ->with('info', 'Triage already completed for this visit.');
        }

        // Get available departments for routing
        $departments = HospitalDepartment::active()
            ->where('company_id', Auth::user()->company_id)
            ->where('type', '!=', 'triage')
            ->where('type', '!=', 'reception')
            ->where('type', '!=', 'cashier')
            ->orderBy('name')
            ->get();

        return view('hospital.triage.create', compact('visit', 'departments'));
    }

    /**
     * Store triage vitals and route to departments
     */
    public function store(Request $request, $visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'temperature' => 'nullable|numeric|min:30|max:45',
            'blood_pressure_systolic' => 'nullable|integer|min:50|max:250',
            'blood_pressure_diastolic' => 'nullable|integer|min:30|max:150',
            'pulse_rate' => 'nullable|integer|min:30|max:200',
            'respiratory_rate' => 'nullable|integer|min:10|max:50',
            'oxygen_saturation' => 'nullable|numeric|min:0|max:100',
            'weight' => 'nullable|numeric|min:0|max:300',
            'height' => 'nullable|numeric|min:0|max:250',
            'chief_complaint' => 'nullable|string',
            'triage_notes' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,critical',
            'route_to_departments' => 'nullable|array',
            'route_to_departments.*' => 'exists:hospital_departments,id',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Calculate BMI if weight and height provided
            $bmi = null;
            if (!empty($validated['weight']) && !empty($validated['height'])) {
                $heightInMeters = $validated['height'] / 100;
                $bmi = $validated['weight'] / ($heightInMeters * $heightInMeters);
            }

            // Create triage vitals
            $triageVital = TriageVital::create([
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'temperature' => $validated['temperature'] ?? null,
                'blood_pressure_systolic' => $validated['blood_pressure_systolic'] ?? null,
                'blood_pressure_diastolic' => $validated['blood_pressure_diastolic'] ?? null,
                'pulse_rate' => $validated['pulse_rate'] ?? null,
                'respiratory_rate' => $validated['respiratory_rate'] ?? null,
                'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'height' => $validated['height'] ?? null,
                'bmi' => $bmi,
                'chief_complaint' => $validated['chief_complaint'] ?? null,
                'triage_notes' => $validated['triage_notes'] ?? null,
                'priority' => $validated['priority'],
                'taken_by' => $user->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]);

            // Update triage visit department status to completed
            $triageDept = $visit->visitDepartments()
                ->whereHas('department', function ($q) {
                    $q->where('type', 'triage');
                })
                ->first();

            if ($triageDept) {
                $triageDept->status = 'completed';
                $triageDept->service_ended_at = now();
                $triageDept->calculateServiceTime();
                $triageDept->save();
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

            return redirect()->route('hospital.triage.show', $visit->id)
                ->with('success', 'Triage vitals recorded and patient routed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to record triage: ' . $e->getMessage()]);
        }
    }

    /**
     * Show triage details
     */
    public function show($visitId)
    {
        $visit = Visit::with([
            'patient',
            'triageVitals.takenBy',
            'visitDepartments.department',
        ])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        return view('hospital.triage.show', compact('visit'));
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

        // Find triage department
        $triageDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'triage');
            })
            ->where('status', 'waiting')
            ->first();

        if (!$triageDept) {
            return back()->withErrors(['error' => 'Triage department not found or already started.']);
        }

        try {
            $triageDept->status = 'in_service';
            $triageDept->service_started_at = now();
            $triageDept->served_by = Auth::id();
            $triageDept->calculateWaitingTime();
            $triageDept->save();

            return redirect()->route('hospital.triage.index')
                ->with('success', 'Triage service started.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to start service: ' . $e->getMessage()]);
        }
    }
}
