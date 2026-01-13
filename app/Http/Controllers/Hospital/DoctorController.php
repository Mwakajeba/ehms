<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\Consultation;
use App\Models\Hospital\HospitalDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
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

        return view('hospital.doctor.create', compact('visit', 'departments'));
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
