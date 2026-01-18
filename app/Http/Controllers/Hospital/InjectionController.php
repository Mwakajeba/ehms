<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\InjectionRecord;
use App\Models\Customer;
use App\Models\Sales\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InjectionController extends Controller
{
    /**
     * Display injection dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Get visits waiting for injection (routed to vaccination department with paid SalesInvoice for injection)
        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'bills'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'vaccine'); // Injection routes to vaccine department
                })->where('status', 'waiting');
            })
            ->where(function ($query) use ($companyId, $branchId) {
                // Either has cleared VisitBill (old flow)
                $query->whereHas('bills', function ($q) {
                    $q->where('clearance_status', 'cleared');
                })
                // OR has Customer with paid SalesInvoice for injection matching patient (new pre-billing flow)
                ->orWhereExists(function ($subQuery) use ($companyId, $branchId) {
                    $subQuery->select(DB::raw(1))
                        ->from('sales_invoices')
                        ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
                        ->join('patients', 'patients.id', '=', 'visits.patient_id')
                        ->where('sales_invoices.company_id', $companyId)
                        ->where('sales_invoices.branch_id', $branchId)
                        ->where('sales_invoices.status', 'paid')
                        ->where('sales_invoices.notes', 'like', '%Injection bill for Visit #%')
                        ->where(function ($q) {
                            $q->whereColumn('customers.phone', 'patients.phone')
                                ->orWhereColumn('customers.email', 'patients.email')
                                ->orWhereColumn('customers.name', DB::raw("CONCAT(patients.first_name, ' ', patients.last_name)"));
                        });
                });
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get visits in service at injection (vaccine department)
        $inServiceVisits = Visit::with(['patient', 'visitDepartments.department', 'injectionRecords'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'vaccine'); // Injection routes to vaccine department
                })->where('status', 'in_service');
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get completed records today
        $completedToday = InjectionRecord::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count();

        // Get follow-up required records
        $followUpRequired = InjectionRecord::with(['patient', 'visit'])
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

        return view('hospital.injection.index', compact('waitingVisits', 'inServiceVisits', 'followUpRequired', 'stats'));
    }

    /**
     * Show injection form for a visit
     */
    public function create($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills', 'injectionRecords.item', 'injectionRecords.performedBy'])
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
        $injectionInvoice = null;
        $injectionInvoiceItems = collect();
        
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
                // Get paid SalesInvoice for injection (check notes for visit number)
                $injectionInvoice = SalesInvoice::where('customer_id', $customer->id)
                    ->where('company_id', $patient->company_id)
                    ->where('branch_id', $patient->branch_id)
                    ->where('status', 'paid')
                    ->where('notes', 'like', "%Injection bill for Visit #{$visit->visit_number}%")
                    ->with(['items.inventoryItem'])
                    ->first();
                
                if ($injectionInvoice) {
                    $hasPaidInvoice = true;
                    $injectionInvoiceItems = $injectionInvoice->items;
                }
            }
        }

        // Get vaccine department status - if already in_service, allow access
        $vaccinationDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'vaccine'); // Injection routes to vaccine department
            })
            ->first();

        $isInService = $vaccinationDept && $vaccinationDept->status === 'in_service';
        
        // Only check bill/invoice if not already in service
        if (!$isInService && !$hasClearedBill && !$hasPaidInvoice) {
            return redirect()->route('hospital.injection.index')
                ->withErrors(['error' => 'Patient bill must be cleared or paid before injection procedure.']);
        }

        // Get existing injection records for this visit, keyed by item_id
        $existingRecords = InjectionRecord::where('visit_id', $visit->id)
            ->get()
            ->keyBy('item_id');

        return view('hospital.injection.create', compact('visit', 'injectionInvoice', 'injectionInvoiceItems', 'existingRecords', 'vaccinationDept'));
    }

    /**
     * Store injection records (multiple items - services and products)
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
            'records.*.record_id' => 'nullable|exists:injection_records,id',
            'records.*.injection_type' => 'required|string|max:255',
            'records.*.status' => 'required|in:pending,completed,follow_up_required',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            $allCompleted = true;
            $recordCounter = InjectionRecord::whereDate('created_at', today())->count();

            // Process each record
            foreach ($validated['records'] as $recordData) {
                if (empty($recordData['item_id']) || empty($recordData['injection_type'])) {
                    continue; // Skip empty records
                }

                // Check if record already exists
                $existingRecord = null;
                if (!empty($recordData['record_id'])) {
                    $existingRecord = InjectionRecord::where('id', $recordData['record_id'])
                        ->where('visit_id', $visit->id)
                        ->where('item_id', $recordData['item_id'])
                        ->first();
                }

                if ($existingRecord) {
                    // Update existing record
                    $existingRecord->update([
                        'injection_type' => $recordData['injection_type'],
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
                    $recordNumber = 'INJ-' . now()->format('Ymd') . '-' . str_pad($recordCounter, 4, '0', STR_PAD_LEFT);

                    InjectionRecord::create([
                        'record_number' => $recordNumber,
                        'visit_id' => $visit->id,
                        'patient_id' => $visit->patient_id,
                        'item_id' => $recordData['item_id'],
                        'injection_type' => $recordData['injection_type'],
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

            // Update vaccine visit department status to completed if all items are completed
            if ($allCompleted) {
                $vaccinationDept = $visit->visitDepartments()
                    ->whereHas('department', function ($q) {
                        $q->where('type', 'vaccine'); // Injection routes to vaccine department
                    })
                    ->first();

                if ($vaccinationDept && $vaccinationDept->status === 'in_service') {
                    $vaccinationDept->status = 'completed';
                    $vaccinationDept->service_ended_at = now();
                    $vaccinationDept->calculateServiceTime();
                    $vaccinationDept->save();
                }
            }

            DB::commit();

            return redirect()->route('hospital.injection.create', $visit->id)
                ->with('success', 'Injection records saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to save injection records: ' . $e->getMessage()]);
        }
    }

    /**
     * Show injection record details
     */
    public function show($id)
    {
        $injectionRecord = InjectionRecord::with([
            'patient',
            'visit',
            'item',
            'performedBy',
        ])->findOrFail($id);

        // Verify access
        if ($injectionRecord->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to injection record.');
        }

        return view('hospital.injection.show', compact('injectionRecord'));
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

        // Find vaccine department (injection routes to vaccine department)
        $vaccinationDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'vaccine'); // Injection routes to vaccine department
            })
            ->where('status', 'waiting')
            ->first();

        if (!$vaccinationDept) {
            return back()->withErrors(['error' => 'Vaccine department not found or already started.']);
        }

        try {
            $vaccinationDept->status = 'in_service';
            $vaccinationDept->service_started_at = now();
            $vaccinationDept->served_by = Auth::id();
            $vaccinationDept->calculateWaitingTime();
            $vaccinationDept->save();

            return redirect()->route('hospital.injection.index')
                ->with('success', 'Injection service started.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to start service: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark record as completed
     */
    public function markCompleted($id)
    {
        $injectionRecord = InjectionRecord::findOrFail($id);

        // Verify access
        if ($injectionRecord->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to injection record.');
        }

        try {
            $injectionRecord->status = 'completed';
            $injectionRecord->completed_at = now();
            $injectionRecord->save();

            // Update vaccine visit department status to completed
            $visit = $injectionRecord->visit;
            $vaccinationDept = $visit->visitDepartments()
                ->whereHas('department', function ($q) {
                    $q->where('type', 'vaccine'); // Injection routes to vaccine department
                })
                ->where('status', 'in_service')
                ->first();

            if ($vaccinationDept) {
                $vaccinationDept->status = 'completed';
                $vaccinationDept->service_ended_at = now();
                $vaccinationDept->calculateServiceTime();
                $vaccinationDept->save();
            }

            return redirect()->route('hospital.injection.show', $injectionRecord->id)
                ->with('success', 'Injection record marked as completed.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to mark record as completed: ' . $e->getMessage()]);
        }
    }
}
