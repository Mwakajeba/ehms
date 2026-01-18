<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\VaccinationRecord;
use App\Models\Customer;
use App\Models\Sales\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VaccinationController extends Controller
{
    /**
     * Display vaccination dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Get visits waiting for vaccination (bills must be cleared OR paid SalesInvoice for vaccination)
        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'bills'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'vaccine');
                })->where('status', 'waiting');
            })
            ->where(function ($query) use ($companyId, $branchId) {
                // Either has cleared VisitBill (old flow)
                $query->whereHas('bills', function ($q) {
                    $q->where('clearance_status', 'cleared');
                })
                // OR has Customer with paid SalesInvoice for vaccination matching patient (new pre-billing flow)
                ->orWhereExists(function ($subQuery) use ($companyId, $branchId) {
                    $subQuery->select(DB::raw(1))
                        ->from('sales_invoices')
                        ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
                        ->join('patients', 'patients.id', '=', 'visits.patient_id')
                        ->where('sales_invoices.company_id', $companyId)
                        ->where('sales_invoices.branch_id', $branchId)
                        ->where('sales_invoices.status', 'paid')
                        ->where('sales_invoices.notes', 'like', '%Vaccination bill for Visit #%')
                        ->where(function ($q) {
                            $q->whereColumn('customers.phone', 'patients.phone')
                                ->orWhereColumn('customers.email', 'patients.email')
                                ->orWhereColumn('customers.name', DB::raw("CONCAT(patients.first_name, ' ', patients.last_name)"));
                        });
                });
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get visits in service at vaccination
        $inServiceVisits = Visit::with(['patient', 'visitDepartments.department', 'vaccinationRecords'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'vaccine');
                })->where('status', 'in_service');
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get completed records today
        $completedToday = VaccinationRecord::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count();

        // Get follow-up required records
        $followUpRequired = VaccinationRecord::with(['patient', 'visit'])
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

        return view('hospital.vaccination.index', compact('waitingVisits', 'inServiceVisits', 'followUpRequired', 'stats'));
    }

    /**
     * Show vaccination form for a visit
     */
    public function create($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills', 'vaccinationRecords.item', 'vaccinationRecords.performedBy'])
            ->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        // Check if bill is cleared (VisitBill) OR has paid SalesInvoice (pre-billing)
        $hasClearedBill = $visit->bills()->where('clearance_status', 'cleared')->exists();
        
        // Get vaccine department status - if already in_service, allow access
        $vaccinationDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'vaccine');
            })
            ->first();

        $isInService = $vaccinationDept && $vaccinationDept->status === 'in_service';
        
        // Check if patient has paid SalesInvoice (match Customer by name/phone/email)
        $patient = $visit->patient;
        $hasPaidInvoice = false;
        $vaccinationInvoice = null;
        $vaccinationInvoiceItems = collect();
        
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
                // Try exact match first (visit number in notes)
                $vaccinationInvoice = SalesInvoice::where('customer_id', $customer->id)
                    ->where('company_id', $patient->company_id)
                    ->where('branch_id', $patient->branch_id)
                    ->where('status', 'paid')
                    ->where('notes', 'like', "%Vaccination bill for Visit #{$visit->visit_number}%")
                    ->with(['items.inventoryItem'])
                    ->first();
                
                // If not found and in service, try broader search (just "Vaccination bill")
                if (!$vaccinationInvoice && $isInService) {
                    $vaccinationInvoice = SalesInvoice::where('customer_id', $customer->id)
                        ->where('company_id', $patient->company_id)
                        ->where('branch_id', $patient->branch_id)
                        ->where('status', 'paid')
                        ->where('notes', 'like', '%Vaccination bill%')
                        ->with(['items.inventoryItem'])
                        ->orderBy('created_at', 'desc')
                        ->first();
                }
                
                if ($vaccinationInvoice) {
                    $hasPaidInvoice = true;
                    $vaccinationInvoiceItems = $vaccinationInvoice->items;
                }
            }
        }
        
        // Only check bill/invoice if not already in service
        if (!$isInService && !$hasClearedBill && !$hasPaidInvoice) {
            return redirect()->route('hospital.vaccination.index')
                ->withErrors(['error' => 'Patient bill must be cleared or paid before vaccination procedure.']);
        }

        // Get existing vaccination records for this visit, keyed by item_id
        $existingRecords = VaccinationRecord::where('visit_id', $visit->id)
            ->get()
            ->keyBy('item_id');

        return view('hospital.vaccination.create', compact('visit', 'vaccinationInvoice', 'vaccinationInvoiceItems', 'existingRecords', 'vaccinationDept'));
    }

    /**
     * Store vaccination records (multiple items - services and products)
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
            'records.*.record_id' => 'nullable|exists:vaccination_records,id',
            'records.*.vaccine_type' => 'required|string|max:255',
            'records.*.status' => 'required|in:pending,completed,follow_up_required',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            $allCompleted = true;
            $recordCounter = VaccinationRecord::whereDate('created_at', today())->count();

            // Process each record
            foreach ($validated['records'] as $recordData) {
                if (empty($recordData['item_id']) || empty($recordData['vaccine_type'])) {
                    continue; // Skip empty records
                }

                // Check if record already exists
                $existingRecord = null;
                if (!empty($recordData['record_id'])) {
                    $existingRecord = VaccinationRecord::where('id', $recordData['record_id'])
                        ->where('visit_id', $visit->id)
                        ->where('item_id', $recordData['item_id'])
                        ->first();
                }

                if ($existingRecord) {
                    // Update existing record
                    $existingRecord->update([
                        'vaccine_type' => $recordData['vaccine_type'],
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
                    $recordNumber = 'VAC-' . now()->format('Ymd') . '-' . str_pad($recordCounter, 4, '0', STR_PAD_LEFT);

                    VaccinationRecord::create([
                        'record_number' => $recordNumber,
                        'visit_id' => $visit->id,
                        'patient_id' => $visit->patient_id,
                        'item_id' => $recordData['item_id'],
                        'vaccine_type' => $recordData['vaccine_type'],
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

            // Update vaccination visit department status to completed if all items are completed
            if ($allCompleted) {
                $vaccinationDept = $visit->visitDepartments()
                    ->whereHas('department', function ($q) {
                        $q->where('type', 'vaccine');
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

            return redirect()->route('hospital.vaccination.create', $visit->id)
                ->with('success', 'Vaccination records saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to save vaccination records: ' . $e->getMessage()]);
        }
    }

    /**
     * Show vaccination record details
     */
    public function show($id)
    {
        $vaccinationRecord = VaccinationRecord::with([
            'patient',
            'visit',
            'item',
            'performedBy',
        ])->findOrFail($id);

        // Verify access
        if ($vaccinationRecord->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to vaccination record.');
        }

        return view('hospital.vaccination.show', compact('vaccinationRecord'));
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

        // Find vaccination department
        $vaccinationDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'vaccine');
            })
            ->where('status', 'waiting')
            ->first();

        if (!$vaccinationDept) {
            return back()->withErrors(['error' => 'Vaccination department not found or already started.']);
        }

        try {
            $vaccinationDept->status = 'in_service';
            $vaccinationDept->service_started_at = now();
            $vaccinationDept->served_by = Auth::id();
            $vaccinationDept->calculateWaitingTime();
            $vaccinationDept->save();

            return redirect()->route('hospital.vaccination.index')
                ->with('success', 'Vaccination service started.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to start service: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark record as completed
     */
    public function markCompleted($id)
    {
        $vaccinationRecord = VaccinationRecord::findOrFail($id);

        // Verify access
        if ($vaccinationRecord->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to vaccination record.');
        }

        try {
            $vaccinationRecord->status = 'completed';
            $vaccinationRecord->completed_at = now();
            $vaccinationRecord->save();

            // Update vaccination visit department status to completed
            $visit = $vaccinationRecord->visit;
            $vaccinationDept = $visit->visitDepartments()
                ->whereHas('department', function ($q) {
                    $q->where('type', 'vaccine');
                })
                ->where('status', 'in_service')
                ->first();

            if ($vaccinationDept) {
                $vaccinationDept->status = 'completed';
                $vaccinationDept->service_ended_at = now();
                $vaccinationDept->calculateServiceTime();
                $vaccinationDept->save();
            }

            return redirect()->route('hospital.vaccination.show', $vaccinationRecord->id)
                ->with('success', 'Vaccination record marked as completed.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to mark record as completed: ' . $e->getMessage()]);
        }
    }
}
