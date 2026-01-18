<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\UltrasoundResult;
use App\Models\Inventory\Item;
use App\Models\Customer;
use App\Models\Sales\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UltrasoundController extends Controller
{
    /**
     * Display ultrasound dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Get visits waiting for ultrasound (bills must be cleared OR paid SalesInvoice)
        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'bills'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'ultrasound');
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

        // Get visits in service at ultrasound
        $inServiceVisits = Visit::with(['patient', 'visitDepartments.department', 'ultrasoundResults'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'ultrasound');
                })->where('status', 'in_service');
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        // Get ready results (for printing)
        $readyResults = UltrasoundResult::with(['patient', 'visit'])
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
            'completed_today' => UltrasoundResult::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->whereDate('completed_at', today())
                ->count(),
        ];

        return view('hospital.ultrasound.index', compact('waitingVisits', 'inServiceVisits', 'readyResults', 'stats'));
    }

    /**
     * Show ultrasound examination form for a visit
     */
    public function create($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills', 'ultrasoundResults'])
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
            return redirect()->route('hospital.ultrasound.index')
                ->withErrors(['error' => 'Patient bill must be cleared or paid before ultrasound examination.']);
        }

        // Get ultrasound services from inventory_items
        $ultrasoundServices = Item::where('company_id', Auth::user()->company_id)
            ->where('item_type', 'service')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('name', 'like', '%ultrasound%')
                  ->orWhere('name', 'like', '%scan%')
                  ->orWhere('name', 'like', '%sonography%')
                  ->orWhere('description', 'like', '%ultrasound%');
            })
            ->orderBy('name')
            ->get();

        return view('hospital.ultrasound.create', compact('visit', 'ultrasoundServices'));
    }

    /**
     * Store ultrasound result
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
            'examination_type' => 'required|string|max:255',
            'findings' => 'nullable|string',
            'impression' => 'nullable|string',
            'recommendation' => 'nullable|string',
            'result_status' => 'required|in:pending,ready',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max per image
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Generate result number
            $resultNumber = 'US-' . now()->format('Ymd') . '-' . str_pad(UltrasoundResult::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('ultrasound/' . $companyId . '/' . $branchId, 'public');
                    $imagePaths[] = $path;
                }
            }

            // Create ultrasound result
            $ultrasoundResult = UltrasoundResult::create([
                'result_number' => $resultNumber,
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'service_id' => $validated['service_id'] ?? null,
                'examination_type' => $validated['examination_type'],
                'findings' => $validated['findings'] ?? null,
                'impression' => $validated['impression'] ?? null,
                'recommendation' => $validated['recommendation'] ?? null,
                'images' => !empty($imagePaths) ? json_encode($imagePaths) : null,
                'result_status' => $validated['result_status'],
                'completed_at' => $validated['result_status'] === 'ready' ? now() : null,
                'performed_by' => $user->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]);

            // Update ultrasound visit department status to completed if result is ready
            if ($validated['result_status'] === 'ready') {
                $ultrasoundDept = $visit->visitDepartments()
                    ->whereHas('department', function ($q) {
                        $q->where('type', 'ultrasound');
                    })
                    ->first();

                if ($ultrasoundDept && $ultrasoundDept->status === 'in_service') {
                    $ultrasoundDept->status = 'completed';
                    $ultrasoundDept->service_ended_at = now();
                    $ultrasoundDept->calculateServiceTime();
                    $ultrasoundDept->save();
                }
            }

            DB::commit();

            return redirect()->route('hospital.ultrasound.show', $ultrasoundResult->id)
                ->with('success', 'Ultrasound examination recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to record ultrasound examination: ' . $e->getMessage()]);
        }
    }

    /**
     * Show ultrasound result details
     */
    public function show($id)
    {
        $ultrasoundResult = UltrasoundResult::with([
            'patient',
            'visit',
            'performedBy',
        ])->findOrFail($id);

        // Verify access
        if ($ultrasoundResult->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to ultrasound result.');
        }

        // Decode images if present
        $images = [];
        if ($ultrasoundResult->images) {
            $images = json_decode($ultrasoundResult->images, true) ?? [];
        }

        return view('hospital.ultrasound.show', compact('ultrasoundResult', 'images'));
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

        // Find ultrasound department
        $ultrasoundDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'ultrasound');
            })
            ->where('status', 'waiting')
            ->first();

        if (!$ultrasoundDept) {
            return back()->withErrors(['error' => 'Ultrasound department not found or already started.']);
        }

        try {
            $ultrasoundDept->status = 'in_service';
            $ultrasoundDept->service_started_at = now();
            $ultrasoundDept->served_by = Auth::id();
            $ultrasoundDept->calculateWaitingTime();
            $ultrasoundDept->save();

            return redirect()->route('hospital.ultrasound.index')
                ->with('success', 'Ultrasound service started.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to start service: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark result as ready
     */
    public function markReady($id)
    {
        $ultrasoundResult = UltrasoundResult::findOrFail($id);

        // Verify access
        if ($ultrasoundResult->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to ultrasound result.');
        }

        try {
            $ultrasoundResult->result_status = 'ready';
            $ultrasoundResult->completed_at = now();
            $ultrasoundResult->save();

            // Update ultrasound visit department status to completed
            $visit = $ultrasoundResult->visit;
            $ultrasoundDept = $visit->visitDepartments()
                ->whereHas('department', function ($q) {
                    $q->where('type', 'ultrasound');
                })
                ->where('status', 'in_service')
                ->first();

            if ($ultrasoundDept) {
                $ultrasoundDept->status = 'completed';
                $ultrasoundDept->service_ended_at = now();
                $ultrasoundDept->calculateServiceTime();
                $ultrasoundDept->save();
            }

            return redirect()->route('hospital.ultrasound.show', $ultrasoundResult->id)
                ->with('success', 'Ultrasound result marked as ready.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to mark result as ready: ' . $e->getMessage()]);
        }
    }

    /**
     * Print result
     */
    public function printResult($id)
    {
        $ultrasoundResult = UltrasoundResult::with(['patient', 'visit'])->findOrFail($id);

        // Verify access
        if ($ultrasoundResult->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to ultrasound result.');
        }

        // Mark as printed
        $ultrasoundResult->result_status = 'printed';
        $ultrasoundResult->printed_at = now();
        $ultrasoundResult->save();

        // Decode images if present
        $images = [];
        if ($ultrasoundResult->images) {
            $images = json_decode($ultrasoundResult->images, true) ?? [];
        }

        return view('hospital.ultrasound.print', compact('ultrasoundResult', 'images'));
    }
}
