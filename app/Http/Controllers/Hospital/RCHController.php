<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\RCHRecord;
use App\Models\Hospital\VisitBill;
use App\Models\Hospital\VisitBillItem;
use App\Models\Inventory\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RCHController extends Controller
{
    /**
     * Display RCH dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Get visits waiting for RCH (bills must be cleared)
        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'bills'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'rch');
                })->where('status', 'waiting');
            })
            ->whereHas('bills', function ($q) {
                $q->where('clearance_status', 'cleared');
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get visits in service at RCH
        $inServiceVisits = Visit::with(['patient', 'visitDepartments.department', 'rchRecords'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'rch');
                })->where('status', 'in_service');
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get completed records today
        $completedToday = RCHRecord::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count();

        // Get follow-up required records
        $followUpRequired = RCHRecord::with(['patient', 'visit'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('status', 'follow_up_required')
            ->where('next_appointment_date', '>=', today())
            ->orderBy('next_appointment_date', 'asc')
            ->get();

        // Get statistics by service type
        $statsByType = RCHRecord::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->select('service_type', DB::raw('count(*) as count'))
            ->groupBy('service_type')
            ->get()
            ->pluck('count', 'service_type');

        // Get statistics
        $stats = [
            'waiting' => $waitingVisits->count(),
            'in_service' => $inServiceVisits->count(),
            'completed_today' => $completedToday,
            'follow_up_required' => $followUpRequired->count(),
            'by_type' => $statsByType,
        ];

        return view('hospital.rch.index', compact('waitingVisits', 'inServiceVisits', 'followUpRequired', 'stats'));
    }

    /**
     * Show RCH service form for a visit
     */
    public function create($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills', 'rchRecords'])
            ->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        // Check if bill is cleared
        $hasClearedBill = $visit->bills()->where('clearance_status', 'cleared')->exists();
        if (!$hasClearedBill) {
            return redirect()->route('hospital.rch.index')
                ->withErrors(['error' => 'Patient bill must be cleared before RCH services.']);
        }

        // Get RCH services from inventory_items
        $rchServices = Item::where('company_id', Auth::user()->company_id)
            ->where('item_type', 'service')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hospital.rch.create', compact('visit', 'rchServices'));
    }

    /**
     * Store RCH record
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
            'service_type' => 'required|in:antenatal_care,postnatal_care,child_health,family_planning,immunization,growth_monitoring,health_education,counseling,other',
            'service_description' => 'nullable|string',
            'findings' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'counseling_notes' => 'nullable|string',
            'health_education_topics' => 'nullable|string',
            'notes' => 'nullable|string',
            'vitals' => 'nullable|array',
            'vitals.weight' => 'nullable|numeric|min:0',
            'vitals.height' => 'nullable|numeric|min:0',
            'vitals.blood_pressure' => 'nullable|string',
            'vitals.temperature' => 'nullable|numeric',
            'vitals.pulse' => 'nullable|integer|min:0',
            'vitals.respiratory_rate' => 'nullable|integer|min:0',
            'status' => 'required|in:pending,completed,follow_up_required',
            'next_appointment_date' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Generate record number
            $recordNumber = 'RCH-' . now()->format('Ymd') . '-' . str_pad(RCHRecord::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Create RCH record
            $rchRecord = RCHRecord::create([
                'record_number' => $recordNumber,
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'service_id' => $validated['service_id'] ?? null,
                'service_type' => $validated['service_type'],
                'service_description' => $validated['service_description'] ?? null,
                'findings' => $validated['findings'] ?? null,
                'recommendations' => $validated['recommendations'] ?? null,
                'counseling_notes' => $validated['counseling_notes'] ?? null,
                'health_education_topics' => $validated['health_education_topics'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'vitals' => $validated['vitals'] ?? null,
                'status' => $validated['status'],
                'next_appointment_date' => $validated['next_appointment_date'] ?? null,
                'completed_at' => $validated['status'] === 'completed' ? now() : null,
                'performed_by' => $user->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]);

            // Auto-create bill item if service_id is provided
            if ($validated['service_id']) {
                $service = Item::find($validated['service_id']);
                if ($service && $service->item_type === 'service') {
                    // Get or create a final bill for this visit
                    $finalBill = VisitBill::where('visit_id', $visit->id)
                        ->where('bill_type', 'final')
                        ->first();

                    if (!$finalBill) {
                        // Create final bill if it doesn't exist
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

                    // Check if this service is already in the bill
                    $existingItem = VisitBillItem::where('bill_id', $finalBill->id)
                        ->where('service_id', $service->id)
                        ->first();

                    if (!$existingItem) {
                        // Add service to bill
                        $itemTotal = $service->unit_price;
                        
                        VisitBillItem::create([
                            'bill_id' => $finalBill->id,
                            'item_type' => 'service',
                            'service_id' => $service->id,
                            'item_name' => $service->name . ' - ' . ucfirst(str_replace('_', ' ', $validated['service_type'])),
                            'quantity' => 1,
                            'unit_price' => $service->unit_price,
                            'total' => $itemTotal,
                        ]);

                        // Update bill totals
                        $finalBill->subtotal = $finalBill->items->sum('total');
                        $finalBill->total = $finalBill->subtotal - $finalBill->discount + $finalBill->tax;
                        $finalBill->balance = $finalBill->total - $finalBill->paid;
                        $finalBill->save();
                    }
                }
            }

            // Update RCH visit department status to completed if status is completed
            if ($validated['status'] === 'completed') {
                $rchDept = $visit->visitDepartments()
                    ->whereHas('department', function ($q) {
                        $q->where('type', 'rch');
                    })
                    ->first();

                if ($rchDept && $rchDept->status === 'in_service') {
                    $rchDept->status = 'completed';
                    $rchDept->service_ended_at = now();
                    $rchDept->calculateServiceTime();
                    $rchDept->save();
                }
            }

            DB::commit();

            return redirect()->route('hospital.rch.show', $rchRecord->id)
                ->with('success', 'RCH record created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create RCH record: ' . $e->getMessage()]);
        }
    }

    /**
     * Show RCH record details
     */
    public function show($id)
    {
        $rchRecord = RCHRecord::with([
            'patient',
            'visit',
            'performedBy',
        ])->findOrFail($id);

        // Verify access
        if ($rchRecord->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to RCH record.');
        }

        return view('hospital.rch.show', compact('rchRecord'));
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

        // Find RCH department
        $rchDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'rch');
            })
            ->where('status', 'waiting')
            ->first();

        if (!$rchDept) {
            return back()->withErrors(['error' => 'RCH department not found or already started.']);
        }

        try {
            $rchDept->status = 'in_service';
            $rchDept->service_started_at = now();
            $rchDept->served_by = Auth::id();
            $rchDept->calculateWaitingTime();
            $rchDept->save();

            return redirect()->route('hospital.rch.index')
                ->with('success', 'RCH service started.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to start service: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark record as completed
     */
    public function markCompleted($id)
    {
        $rchRecord = RCHRecord::findOrFail($id);

        // Verify access
        if ($rchRecord->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to RCH record.');
        }

        try {
            $rchRecord->status = 'completed';
            $rchRecord->completed_at = now();
            $rchRecord->save();

            // Update RCH visit department status to completed
            $visit = $rchRecord->visit;
            $rchDept = $visit->visitDepartments()
                ->whereHas('department', function ($q) {
                    $q->where('type', 'rch');
                })
                ->where('status', 'in_service')
                ->first();

            if ($rchDept) {
                $rchDept->status = 'completed';
                $rchDept->service_ended_at = now();
                $rchDept->calculateServiceTime();
                $rchDept->save();
            }

            return redirect()->route('hospital.rch.show', $rchRecord->id)
                ->with('success', 'RCH record marked as completed.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to mark record as completed: ' . $e->getMessage()]);
        }
    }
}
