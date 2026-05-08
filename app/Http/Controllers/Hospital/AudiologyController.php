<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\AudiologyResult;
use App\Models\Hospital\HospitalDepartment;
use App\Models\Customer;
use App\Models\Sales\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AudiologyController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        $waitingVisits = Visit::with(['patient', 'visitDepartments.department', 'bills'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'audiology');
                })->where('status', 'waiting');
            })
            ->where(function ($query) use ($companyId, $branchId) {
                $query->whereHas('bills', function ($q) {
                    $q->where('clearance_status', 'cleared');
                })
                ->orWhereExists(function ($subQuery) use ($companyId, $branchId) {
                    $subQuery->select(DB::raw(1))
                        ->from('sales_invoices')
                        ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
                        ->join('patients', 'patients.id', '=', 'visits.patient_id')
                        ->where('sales_invoices.company_id', $companyId)
                        ->where('sales_invoices.branch_id', $branchId)
                        ->where('sales_invoices.status', 'paid')
                        ->where('sales_invoices.notes', 'like', '%Audiology test bill for Visit #%')
                        ->where(function ($q) {
                            $q->whereColumn('customers.phone', 'patients.phone')
                                ->orWhereColumn('customers.email', 'patients.email')
                                ->orWhereColumn('customers.name', DB::raw("CONCAT(patients.first_name, ' ', patients.last_name)"));
                        });
                });
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        $inServiceVisits = Visit::with(['patient', 'visitDepartments.department', 'audiologyResults'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereHas('visitDepartments', function ($q) {
                $q->whereHas('department', function ($query) {
                    $query->where('type', 'audiology');
                })->where('status', 'in_service');
            })
            ->orderBy('visit_date', 'asc')
            ->get();

        $readyResults = AudiologyResult::with(['patient', 'visit'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('result_status', 'ready')
            ->orderBy('completed_at', 'desc')
            ->get();

        $stats = [
            'waiting' => $waitingVisits->count(),
            'in_service' => $inServiceVisits->count(),
            'ready_results' => $readyResults->count(),
            'completed_today' => AudiologyResult::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->whereDate('completed_at', today())
                ->count(),
        ];

        return view('hospital.audiology.index', compact('waitingVisits', 'inServiceVisits', 'readyResults', 'stats'));
    }

    public function create($visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments.department', 'bills', 'audiologyResults'])
            ->findOrFail($visitId);

        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $hasClearedBill = $visit->bills()->where('clearance_status', 'cleared')->exists();

        $patient = $visit->patient;
        $hasPaidInvoice = false;
        $audiologyInvoice = null;
        $audiologyInvoiceItems = collect();

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
                $audiologyInvoice = SalesInvoice::where('customer_id', $customer->id)
                    ->where('company_id', $patient->company_id)
                    ->where('branch_id', $patient->branch_id)
                    ->where('status', 'paid')
                    ->where('notes', 'like', "%Audiology test bill for Visit #{$visit->visit_number}%")
                    ->with(['items.inventoryItem'])
                    ->first();

                if ($audiologyInvoice) {
                    $hasPaidInvoice = true;
                    $audiologyInvoiceItems = $audiologyInvoice->items;
                }
            }
        }

        if (!$hasClearedBill && !$hasPaidInvoice) {
            return redirect()->route('hospital.audiology.index')
                ->withErrors(['error' => 'Patient bill must be cleared or paid before audiology tests.']);
        }

        $existingResults = AudiologyResult::where('visit_id', $visit->id)
            ->get()
            ->keyBy('service_id');

        return view('hospital.audiology.create', compact('visit', 'audiologyInvoice', 'audiologyInvoiceItems', 'existingResults'));
    }

    public function store(Request $request, $visitId)
    {
        $visit = Visit::with(['patient', 'visitDepartments'])->findOrFail($visitId);

        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $validated = $request->validate([
            'results' => 'required|array|min:1',
            'results.*.service_id' => 'nullable|exists:inventory_items,id',
            'results.*.test_type' => 'required|string|max:255',
            'results.*.findings' => 'nullable|string',
            'results.*.impression' => 'nullable|string',
            'results.*.recommendation' => 'nullable|string',
            'results.*.result_status' => 'required|in:pending,ready',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            $allReady = true;
            $resultCounter = AudiologyResult::whereDate('created_at', today())->count();

            foreach ($validated['results'] as $resultData) {
                if (empty($resultData['test_type'])) {
                    continue;
                }

                $serviceId = $resultData['service_id'] ?? null;

                $existingResult = AudiologyResult::where('visit_id', $visit->id)
                    ->when($serviceId, fn ($q) => $q->where('service_id', $serviceId))
                    ->when(!$serviceId, fn ($q) => $q->whereNull('service_id')->where('test_type', $resultData['test_type']))
                    ->first();

                $payload = [
                    'service_id' => $serviceId,
                    'test_type' => $resultData['test_type'],
                    'findings' => $resultData['findings'] ?? null,
                    'impression' => $resultData['impression'] ?? null,
                    'recommendation' => $resultData['recommendation'] ?? null,
                    'result_status' => $resultData['result_status'],
                    'completed_at' => $resultData['result_status'] === 'ready' ? now() : null,
                    'performed_by' => $user->id,
                ];

                if ($existingResult) {
                    $existingResult->update($payload);
                } else {
                    $resultCounter++;
                    $resultNumber = 'AUD-' . now()->format('Ymd') . '-' . str_pad($resultCounter, 4, '0', STR_PAD_LEFT);

                    AudiologyResult::create($payload + [
                        'result_number' => $resultNumber,
                        'visit_id' => $visit->id,
                        'patient_id' => $visit->patient_id,
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                    ]);
                }

                if ($resultData['result_status'] !== 'ready') {
                    $allReady = false;
                }
            }

            if ($allReady) {
                $audiologyDept = $visit->visitDepartments()
                    ->whereHas('department', function ($q) {
                        $q->where('type', 'audiology');
                    })
                    ->first();

                if ($audiologyDept && $audiologyDept->status === 'in_service') {
                    $audiologyDept->status = 'completed';
                    $audiologyDept->service_ended_at = now();
                    $audiologyDept->calculateServiceTime();
                    $audiologyDept->save();
                }
            }

            DB::commit();

            if ($allReady) {
                $doctorDept = HospitalDepartment::where('company_id', $companyId)
                    ->where('type', 'doctor')
                    ->first();

                if ($doctorDept) {
                    $existingDept = $visit->visitDepartments()
                        ->where('department_id', $doctorDept->id)
                        ->first();

                    if (!$existingDept) {
                        $maxSequence = $visit->visitDepartments()->max('sequence') ?? 0;
                        VisitDepartment::create([
                            'visit_id' => $visit->id,
                            'department_id' => $doctorDept->id,
                            'status' => 'waiting',
                            'waiting_started_at' => now(),
                            'sequence' => $maxSequence + 1,
                        ]);
                    } else {
                        $existingDept->status = 'waiting';
                        $existingDept->waiting_started_at = now();
                        $existingDept->save();
                    }
                }
            }

            return redirect()->route('hospital.audiology.index')
                ->with('success', 'Audiology results recorded successfully.' . ($allReady ? ' Patient has been sent back to doctor.' : ''));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to record audiology result: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $audiologyResult = AudiologyResult::with(['patient', 'visit', 'performedBy'])->findOrFail($id);

        if ($audiologyResult->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to audiology result.');
        }

        return view('hospital.audiology.show', compact('audiologyResult'));
    }

    public function startService($visitId)
    {
        $visit = Visit::with(['visitDepartments'])->findOrFail($visitId);

        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        $audiologyDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'audiology');
            })
            ->where('status', 'waiting')
            ->first();

        if (!$audiologyDept) {
            return back()->withErrors(['error' => 'Audiology department not found or already started.']);
        }

        try {
            $audiologyDept->status = 'in_service';
            $audiologyDept->service_started_at = now();
            $audiologyDept->served_by = Auth::id();
            $audiologyDept->calculateWaitingTime();
            $audiologyDept->save();

            return redirect()->route('hospital.audiology.index')
                ->with('success', 'Audiology service started.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to start service: ' . $e->getMessage()]);
        }
    }

    public function markReady($id)
    {
        $audiologyResult = AudiologyResult::findOrFail($id);

        if ($audiologyResult->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to audiology result.');
        }

        try {
            $audiologyResult->result_status = 'ready';
            $audiologyResult->completed_at = now();
            $audiologyResult->save();

            $visit = $audiologyResult->visit;
            $audiologyDept = $visit->visitDepartments()
                ->whereHas('department', function ($q) {
                    $q->where('type', 'audiology');
                })
                ->where('status', 'in_service')
                ->first();

            if ($audiologyDept) {
                $audiologyDept->status = 'completed';
                $audiologyDept->service_ended_at = now();
                $audiologyDept->calculateServiceTime();
                $audiologyDept->save();
            }

            return redirect()->route('hospital.audiology.show', $audiologyResult->id)
                ->with('success', 'Audiology result marked as ready.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to mark result as ready: ' . $e->getMessage()]);
        }
    }

    public function printResult($id)
    {
        $audiologyResult = AudiologyResult::with(['patient', 'visit'])->findOrFail($id);

        if ($audiologyResult->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to audiology result.');
        }

        $audiologyResult->result_status = 'printed';
        $audiologyResult->printed_at = now();
        $audiologyResult->save();

        return view('hospital.audiology.print', compact('audiologyResult'));
    }
}

