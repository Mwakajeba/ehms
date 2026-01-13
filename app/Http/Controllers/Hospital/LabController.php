<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\LabResult;
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

        // Get visits waiting for lab (bills must be cleared)
        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'bills'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'lab');
                })->where('status', 'waiting');
            })
            ->whereHas('bills', function ($q) {
                $q->where('clearance_status', 'cleared');
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

        // Check if bill is cleared
        $hasClearedBill = $visit->bills()->where('clearance_status', 'cleared')->exists();
        if (!$hasClearedBill) {
            return redirect()->route('hospital.lab.index')
                ->withErrors(['error' => 'Patient bill must be cleared before lab tests.']);
        }

        // Get lab test services from inventory_items
        $labTests = Item::where('company_id', Auth::user()->company_id)
            ->where('item_type', 'service')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('name', 'like', '%lab%')
                  ->orWhere('name', 'like', '%test%')
                  ->orWhere('description', 'like', '%lab%');
            })
            ->orderBy('name')
            ->get();

        return view('hospital.lab.create', compact('visit', 'labTests'));
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
            'test_name' => 'required|string|max:255',
            'result_value' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'reference_range' => 'nullable|string|max:255',
            'status' => 'nullable|in:normal,abnormal,critical',
            'notes' => 'nullable|string',
            'result_status' => 'required|in:pending,ready',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Generate result number
            $resultNumber = 'LAB-' . now()->format('Ymd') . '-' . str_pad(LabResult::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Get service from inventory_items (for now, we'll use a default or create a mapping)
            // Note: The migration references hospital_services, but we're using inventory_items
            // This might need a migration update later
            $serviceId = $request->service_id ?? null;

            // Create lab result
            $labResult = LabResult::create([
                'result_number' => $resultNumber,
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'service_id' => $serviceId, // This might need to be updated to reference inventory_items
                'test_name' => $validated['test_name'],
                'result_value' => $validated['result_value'] ?? null,
                'unit' => $validated['unit'] ?? null,
                'reference_range' => $validated['reference_range'] ?? null,
                'status' => $validated['status'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'result_status' => $validated['result_status'],
                'completed_at' => $validated['result_status'] === 'ready' ? now() : null,
                'performed_by' => $user->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]);

            // Update lab visit department status to completed if result is ready
            if ($validated['result_status'] === 'ready') {
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

            return redirect()->route('hospital.lab.show', $labResult->id)
                ->with('success', 'Lab result recorded successfully.');
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
