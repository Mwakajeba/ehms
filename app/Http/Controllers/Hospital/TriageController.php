<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\TriageVital;
use App\Models\Hospital\HospitalDepartment;
use App\Services\Hospital\VisitBillingClearance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TriageController extends Controller
{
    protected function branchId(): int
    {
        return (int) (session('branch_id') ?? Auth::user()->branch_id);
    }

    protected function assertVisitBranch(Visit $visit): void
    {
        $branchId = $this->branchId();

        if ((int) $visit->branch_id !== $branchId) {
            abort(403, 'This visit belongs to another branch. Switch to that branch to continue.');
        }

        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }
    }

    protected function waitingVisitsQuery(int $companyId, int $branchId)
    {
        return VisitBillingClearance::applyClearedBillOrPaidInvoice(
            Visit::query()
                ->with(['patient', 'visitDepartments.department', 'bills'])
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->whereHas('visitDepartments', function ($q) use ($branchId) {
                    $q->where('status', 'waiting')
                        ->whereHas('department', function ($query) use ($branchId) {
                            $query->where('type', 'triage');
                            VisitBillingClearance::scopeDepartmentsForBranch($query, $branchId);
                        });
                }),
            $companyId,
            $branchId
        );
    }

    protected function inServiceVisitsQuery(int $companyId, int $branchId)
    {
        return Visit::with(['patient', 'visitDepartments.department', 'triageVitals'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) use ($branchId) {
                $q->where('status', 'in_service')
                    ->whereHas('department', function ($query) use ($branchId) {
                        $query->where('type', 'triage');
                        VisitBillingClearance::scopeDepartmentsForBranch($query, $branchId);
                    });
            });
    }

    /**
     * Display triage dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = $this->branchId();

        $waitingVisits = $this->waitingVisitsQuery($companyId, $branchId)
            ->orderBy('visit_date', 'asc')
            ->get();

        $inServiceVisits = $this->inServiceVisitsQuery($companyId, $branchId)
            ->orderBy('visit_date', 'asc')
            ->get();

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
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills', 'triageVitals'])
            ->findOrFail($visitId);

        $this->assertVisitBranch($visit);

        $companyId = Auth::user()->company_id;
        $branchId = $this->branchId();

        if (!VisitBillingClearance::visitHasClearance($visit, $companyId, $branchId)) {
            return redirect()->route('hospital.triage.index')
                ->withErrors(['error' => 'Patient bill must be cleared or paid at this branch before triage.']);
        }

        if ($visit->triageVitals) {
            return redirect()->route('hospital.triage.show', $visit->id)
                ->with('info', 'Triage already completed for this visit.');
        }

        $departments = HospitalDepartment::active()
            ->where('company_id', $companyId)
            ->where('type', '!=', 'triage')
            ->where('type', '!=', 'reception')
            ->where('type', '!=', 'cashier')
            ->where(function ($q) use ($branchId) {
                VisitBillingClearance::scopeDepartmentsForBranch($q, $branchId);
            })
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

        $this->assertVisitBranch($visit);

        $companyId = Auth::user()->company_id;
        $branchId = $this->branchId();

        if (!VisitBillingClearance::visitHasClearance($visit, $companyId, $branchId)) {
            return redirect()->route('hospital.triage.index')
                ->withErrors(['error' => 'Patient bill must be cleared or paid at this branch before triage.']);
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

            $bmi = null;
            if (!empty($validated['weight']) && !empty($validated['height'])) {
                $heightInMeters = $validated['height'] / 100;
                $bmi = $validated['weight'] / ($heightInMeters * $heightInMeters);
            }

            TriageVital::create([
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

            $triageDept = $visit->visitDepartments()
                ->whereHas('department', function ($q) use ($branchId) {
                    $q->where('type', 'triage');
                    VisitBillingClearance::scopeDepartmentsForBranch($q, $branchId);
                })
                ->first();

            if ($triageDept) {
                $triageDept->status = 'completed';
                $triageDept->service_ended_at = now();
                $triageDept->calculateServiceTime();
                $triageDept->save();
            }

            if (!empty($validated['route_to_departments'])) {
                $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                $sequence = $maxSequence + 1;

                foreach ($validated['route_to_departments'] as $deptId) {
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

        $this->assertVisitBranch($visit);

        return view('hospital.triage.show', compact('visit'));
    }

    /**
     * Start service (mark patient as in_service)
     */
    public function startService($visitId)
    {
        $visit = Visit::with(['visitDepartments'])->findOrFail($visitId);

        $this->assertVisitBranch($visit);

        $branchId = $this->branchId();

        $triageDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) use ($branchId) {
                $q->where('type', 'triage');
                VisitBillingClearance::scopeDepartmentsForBranch($q, $branchId);
            })
            ->where('status', 'waiting')
            ->first();

        if (!$triageDept) {
            return back()->withErrors(['error' => 'Triage department not found for this branch or service already started.']);
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
