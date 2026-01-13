<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\DentalRecord;
use App\Models\Inventory\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DentalController extends Controller
{
    /**
     * Display dental dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Get visits waiting for dental (bills must be cleared)
        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'bills'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'dental');
                })->where('status', 'waiting');
            })
            ->whereHas('bills', function ($q) {
                $q->where('clearance_status', 'cleared');
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get visits in service at dental
        $inServiceVisits = Visit::with(['patient', 'visitDepartments.department', 'dentalRecords'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'dental');
                })->where('status', 'in_service');
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get completed records today
        $completedToday = DentalRecord::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count();

        // Get follow-up required records
        $followUpRequired = DentalRecord::with(['patient', 'visit'])
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

        return view('hospital.dental.index', compact('waitingVisits', 'inServiceVisits', 'followUpRequired', 'stats'));
    }

    /**
     * Show dental procedure form for a visit
     */
    public function create($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills', 'dentalRecords'])
            ->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        // Check if bill is cleared
        $hasClearedBill = $visit->bills()->where('clearance_status', 'cleared')->exists();
        if (!$hasClearedBill) {
            return redirect()->route('hospital.dental.index')
                ->withErrors(['error' => 'Patient bill must be cleared before dental procedure.']);
        }

        // Get dental services from inventory_items
        $dentalServices = Item::where('company_id', Auth::user()->company_id)
            ->where('item_type', 'service')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('name', 'like', '%dental%')
                  ->orWhere('name', 'like', '%tooth%')
                  ->orWhere('name', 'like', '%cleaning%')
                  ->orWhere('name', 'like', '%filling%')
                  ->orWhere('name', 'like', '%extraction%')
                  ->orWhere('description', 'like', '%dental%');
            })
            ->orderBy('name')
            ->get();

        return view('hospital.dental.create', compact('visit', 'dentalServices'));
    }

    /**
     * Store dental record
     */
    public function store(Request $request, $visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'service_id' => 'nullable|exists:inventory_items,id',
            'procedure_type' => 'required|string|max:255',
            'procedure_description' => 'nullable|string',
            'findings' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'treatment_performed' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,follow_up_required',
            'next_appointment_date' => 'nullable|date|after_or_equal:today',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max per image
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Generate record number
            $recordNumber = 'DENT-' . now()->format('Ymd') . '-' . str_pad(DentalRecord::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('dental/' . $companyId . '/' . $branchId, 'public');
                    $imagePaths[] = $path;
                }
            }

            // Create dental record
            $dentalRecord = DentalRecord::create([
                'record_number' => $recordNumber,
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'service_id' => $validated['service_id'] ?? null,
                'procedure_type' => $validated['procedure_type'],
                'procedure_description' => $validated['procedure_description'] ?? null,
                'findings' => $validated['findings'] ?? null,
                'treatment_plan' => $validated['treatment_plan'] ?? null,
                'treatment_performed' => $validated['treatment_performed'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'images' => !empty($imagePaths) ? json_encode($imagePaths) : null,
                'status' => $validated['status'],
                'next_appointment_date' => $validated['next_appointment_date'] ?? null,
                'completed_at' => $validated['status'] === 'completed' ? now() : null,
                'performed_by' => $user->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]);

            // Update dental visit department status to completed if status is completed
            if ($validated['status'] === 'completed') {
                $dentalDept = $visit->visitDepartments()
                    ->whereHas('department', function ($q) {
                        $q->where('type', 'dental');
                    })
                    ->first();

                if ($dentalDept && $dentalDept->status === 'in_service') {
                    $dentalDept->status = 'completed';
                    $dentalDept->service_ended_at = now();
                    $dentalDept->calculateServiceTime();
                    $dentalDept->save();
                }
            }

            DB::commit();

            return redirect()->route('hospital.dental.show', $dentalRecord->id)
                ->with('success', 'Dental record created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create dental record: ' . $e->getMessage()]);
        }
    }

    /**
     * Show dental record details
     */
    public function show($id)
    {
        $dentalRecord = DentalRecord::with([
            'patient',
            'visit',
            'performedBy',
        ])->findOrFail($id);

        // Verify access
        if ($dentalRecord->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to dental record.');
        }

        // Decode images if present
        $images = [];
        if ($dentalRecord->images) {
            $images = json_decode($dentalRecord->images, true) ?? [];
        }

        return view('hospital.dental.show', compact('dentalRecord', 'images'));
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

        // Find dental department
        $dentalDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'dental');
            })
            ->where('status', 'waiting')
            ->first();

        if (!$dentalDept) {
            return back()->withErrors(['error' => 'Dental department not found or already started.']);
        }

        try {
            $dentalDept->status = 'in_service';
            $dentalDept->service_started_at = now();
            $dentalDept->served_by = Auth::id();
            $dentalDept->calculateWaitingTime();
            $dentalDept->save();

            return redirect()->route('hospital.dental.index')
                ->with('success', 'Dental service started.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to start service: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark record as completed
     */
    public function markCompleted($id)
    {
        $dentalRecord = DentalRecord::findOrFail($id);

        // Verify access
        if ($dentalRecord->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to dental record.');
        }

        try {
            $dentalRecord->status = 'completed';
            $dentalRecord->completed_at = now();
            $dentalRecord->save();

            // Update dental visit department status to completed
            $visit = $dentalRecord->visit;
            $dentalDept = $visit->visitDepartments()
                ->whereHas('department', function ($q) {
                    $q->where('type', 'dental');
                })
                ->where('status', 'in_service')
                ->first();

            if ($dentalDept) {
                $dentalDept->status = 'completed';
                $dentalDept->service_ended_at = now();
                $dentalDept->calculateServiceTime();
                $dentalDept->save();
            }

            return redirect()->route('hospital.dental.show', $dentalRecord->id)
                ->with('success', 'Dental record marked as completed.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to mark record as completed: ' . $e->getMessage()]);
        }
    }
}
