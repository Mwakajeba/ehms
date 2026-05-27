<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Helpers\SmsHelper;
use App\Models\Hospital\HospitalInsuranceType;
use App\Models\Hospital\Patient;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitBill;
use App\Models\Hospital\VisitBillItem;
use App\Models\Hospital\VisitDepartment;
use App\Models\Hospital\HospitalDepartment;
use App\Models\Hospital\PatientDeletionRequest;
use App\Models\Inventory\Item;
use App\Models\Customer;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesInvoiceItem;
use App\Services\Hospital\MrnService;
use App\Services\Hospital\PatientDeletionGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class ReceptionController extends Controller
{
    /**
     * Display reception dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        $activeVisitsCount = Visit::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        // Get waiting patients by department
        $waitingByDepartment = VisitDepartment::with(['visit.patient', 'department'])
            ->whereHas('visit', function ($q) use ($companyId, $branchId) {
                $q->where('company_id', $companyId)
                    ->where('branch_id', $branchId);
            })
            ->where('status', 'waiting')
            ->get()
            ->groupBy('department.type');

        // Get in-service patients by department
        $inServiceByDepartment = VisitDepartment::with(['visit.patient', 'department'])
            ->whereHas('visit', function ($q) use ($companyId, $branchId) {
                $q->where('company_id', $companyId)
                    ->where('branch_id', $branchId);
            })
            ->where('status', 'in_service')
            ->get()
            ->groupBy('department.type');

        return view('hospital.reception.index', compact('activeVisitsCount', 'waitingByDepartment', 'inServiceByDepartment'));
    }

    /**
     * Active visits (Ajax DataTable)
     */
    public function activeVisitsIndex(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        $visits = Visit::query()
            ->with(['patient', 'visitDepartments.department'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('visit_date', 'desc');

        return DataTables::of($visits)
            ->addIndexColumn()
            ->addColumn('visit_number', fn ($visit) => e($visit->visit_number))
            ->addColumn('patient_name', fn ($visit) => e($visit->patient?->full_name ?? 'N/A'))
            ->addColumn('mrn', fn ($visit) => e($visit->patient?->mrn ?? 'N/A'))
            ->addColumn('phone', fn ($visit) => e($visit->patient?->phone ?? 'N/A'))
            ->addColumn('current_department', function ($visit) {
                $currentDept = $visit->visitDepartments
                    ->whereIn('status', ['waiting', 'in_service'])
                    ->sortBy('sequence')
                    ->first();

                if (!$currentDept) {
                    return '<span class="text-muted">-</span>';
                }

                $deptName = $currentDept->department?->name ?? 'N/A';
                $deptType = $currentDept->department?->type ?? '';

                return '<span class="badge bg-info">' . e($deptName) . '</span>'
                    . '<br><small class="text-muted">' . e(ucfirst(str_replace('_', ' ', $deptType))) . '</small>';
            })
            ->addColumn('dept_status', function ($visit) {
                $currentDept = $visit->visitDepartments
                    ->whereIn('status', ['waiting', 'in_service'])
                    ->sortBy('sequence')
                    ->first();

                $status = $currentDept?->status ?? 'waiting';
                $label = ucfirst(str_replace('_', ' ', $status));
                $css = 'status-' . str_replace('_', '-', $status);

                return '<span class="status-badge ' . e($css) . '">' . e($label) . '</span>';
            })
            ->addColumn('waiting_time', function ($visit) {
                $currentDept = $visit->visitDepartments
                    ->whereIn('status', ['waiting', 'in_service'])
                    ->sortBy('sequence')
                    ->first();

                if (!$currentDept) {
                    return '<span class="text-muted">-</span>';
                }

                return '<span class="text-warning"><i class="bx bx-time"></i> ' . e($currentDept->waiting_time_formatted ?? '00:00:00') . '</span>';
            })
            ->addColumn('service_time', function ($visit) {
                $currentDept = $visit->visitDepartments
                    ->whereIn('status', ['waiting', 'in_service'])
                    ->sortBy('sequence')
                    ->first();

                if (!$currentDept || !$currentDept->service_started_at) {
                    return '<span class="text-muted">-</span>';
                }

                return '<span class="text-primary"><i class="bx bx-time-five"></i> ' . e($currentDept->service_time_formatted ?? '00:00:00') . '</span>';
            })
            ->addColumn('start_time', function ($visit) {
                $currentDept = $visit->visitDepartments
                    ->whereIn('status', ['waiting', 'in_service'])
                    ->sortBy('sequence')
                    ->first();

                if ($currentDept?->service_started_at) {
                    return '<small>' . e($currentDept->service_started_at->format('H:i')) . '</small>';
                }
                if ($currentDept?->waiting_started_at) {
                    return '<small class="text-muted">' . e($currentDept->waiting_started_at->format('H:i')) . '</small>';
                }

                return '<span class="text-muted">-</span>';
            })
            ->addColumn('visit_date', fn ($visit) => $visit->visit_date ? e($visit->visit_date->format('d M Y, H:i')) : 'N/A')
            ->addColumn('action', function ($visit) {
                return '<a href="' . route('hospital.reception.visits.show', $visit->id) . '" class="btn btn-sm btn-info" title="View Details"><i class="bx bx-show"></i></a>';
            })
            ->filterColumn('patient_name', function ($query, $keyword) {
                $query->whereHas('patient', function ($q) use ($keyword) {
                    $q->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$keyword}%"]);
                });
            })
            ->filterColumn('mrn', function ($query, $keyword) {
                $query->whereHas('patient', function ($q) use ($keyword) {
                    $q->where('mrn', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('phone', function ($query, $keyword) {
                $query->whereHas('patient', function ($q) use ($keyword) {
                    $q->where('phone', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['current_department', 'dept_status', 'waiting_time', 'service_time', 'start_time', 'action'])
            ->make(true);
    }

    /**
     * List all patients (Ajax DataTable)
     */
    public function patientsIndex(Request $request)
    {
        if ($request->ajax()) {
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?? Auth::user()->branch_id;

            $patients = Patient::query()
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->with('insuranceType')
                ->withCount('visits');

            return DataTables::of($patients)
                ->addIndexColumn()
                ->addColumn('full_name', function ($patient) {
                    return e($patient->full_name);
                })
                ->addColumn('date_of_birth_display', function ($patient) {
                    return $patient->date_of_birth
                        ? $patient->date_of_birth->format('d M Y')
                        : '-';
                })
                ->addColumn('gender_display', function ($patient) {
                    return $patient->gender ? ucfirst($patient->gender) : '-';
                })
                ->addColumn('insurance_display', function ($patient) {
                    $name = $patient->insurance_type_name;
                    if ($name && $name !== 'None') {
                        return '<span class="badge bg-info">' . e($name) . '</span>';
                    }
                    return '<span class="text-muted">None</span>';
                })
                ->addColumn('status', function ($patient) {
                    if ($patient->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    }
                    return '<span class="badge bg-secondary">Inactive</span>';
                })
                ->addColumn('registered_at', function ($patient) {
                    return $patient->created_at ? $patient->created_at->format('d M Y, H:i') : '-';
                })
                ->addColumn('action', function ($patient) {
                    $viewBtn = '<a href="' . route('hospital.reception.patients.show', $patient->id) . '" class="btn btn-sm btn-outline-info me-1" title="View"><i class="bx bx-show"></i></a>';
                    $editBtn = '<a href="' . route('hospital.reception.patients.edit', $patient->id) . '" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="bx bx-edit"></i></a>';
                    $visitBtn = '<a href="' . route('hospital.reception.visits.create', $patient->id) . '" class="btn btn-sm btn-outline-success" title="Create Visit"><i class="bx bx-plus"></i></a>';
                    return $viewBtn . $editBtn . $visitBtn;
                })
                ->editColumn('phone', fn ($patient) => $patient->phone ?? '-')
                ->editColumn('email', fn ($patient) => $patient->email ?? '-')
                ->editColumn('blood_group', fn ($patient) => $patient->blood_group ?? '-')
                ->editColumn('age', fn ($patient) => $patient->age !== null ? $patient->age : '-')
                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('first_name', 'like', "%{$keyword}%")
                            ->orWhere('last_name', 'like', "%{$keyword}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$keyword}%"]);
                    });
                })
                ->filterColumn('insurance_type', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('insurance_type', 'like', "%{$keyword}%")
                            ->orWhereHas('insuranceType', function ($q2) use ($keyword) {
                                $q2->where('name', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->filterColumn('mrn', function ($query, $keyword) {
                    $query->where('mrn', 'like', "%{$keyword}%");
                })
                ->orderColumn('registered_at', 'patients.created_at $1')
                ->rawColumns(['insurance_display', 'status', 'action'])
                ->make(true);
        }

        return view('hospital.reception.patients.index');
    }

    /**
     * Show patient registration form
     */
    public function createPatient()
    {
        $insuranceTypes = HospitalInsuranceType::optionsForCompany(Auth::user()->company_id);

        return view('hospital.reception.patients.create', compact('insuranceTypes'));
    }

    /**
     * Register a new patient
     */
    public function storePatient(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'admitted_date' => 'nullable|date',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'next_of_kin_name' => 'nullable|string|max:255',
            'next_of_kin_phone' => 'nullable|string|max:20',
            'next_of_kin_relationship' => 'nullable|string|max:255',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'blood_group' => 'nullable|string|max:10',
            'id_number' => 'nullable|string|max:50',
            'insurance_number' => 'nullable|string|max:50',
            'insurance_type_id' => 'nullable|exists:hospital_insurance_types,id',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            $validated = $this->applyInsuranceTypeToPatientData($validated, $companyId);

            if (empty($validated['admitted_date'])) {
                $validated['admitted_date'] = now()->toDateString();
            }

            // Generate MRN
            $mrn = MrnService::generate($companyId, $branchId);

            // Create patient
            $patient = Patient::create(array_merge($validated, [
                'mrn' => $mrn,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]));

            DB::commit();

            $redirect = redirect()->route('hospital.reception.patients.show', $patient->id)
                ->with('success', 'Patient registered successfully. MRN: ' . $mrn);

            $smsResult = $this->sendPatientWelcomeSms($patient);
            if (!($smsResult['success'] ?? false) && !empty($patient->phone)) {
                $redirect->with('warning', 'Welcome SMS was not sent: ' . ($smsResult['error'] ?? 'Unknown error'));
            } elseif (!empty($patient->phone) && ($smsResult['success'] ?? false)) {
                $redirect->with('info', 'Welcome SMS sent to patient.');
            }

            return $redirect;
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to register patient: ' . $e->getMessage()]);
        }
    }

    /**
     * Send welcome SMS to newly registered patient
     */
    /**
     * Resolve insurance_type_id to company-scoped type and set insurance_type label.
     */
    protected function applyInsuranceTypeToPatientData(array $data, int $companyId): array
    {
        $noneType = HospitalInsuranceType::forCompany($companyId)
            ->where('is_none', true)
            ->active()
            ->first();

        if (empty($data['insurance_type_id'])) {
            $data['insurance_type_id'] = $noneType?->id;
            $data['insurance_type'] = $noneType?->name ?? 'None';

            return $data;
        }

        $insuranceType = HospitalInsuranceType::forCompany($companyId)
            ->active()
            ->find($data['insurance_type_id']);

        if (!$insuranceType) {
            throw new \InvalidArgumentException('Invalid insurance type for this company.');
        }

        $data['insurance_type'] = $insuranceType->name;

        return $data;
    }

    protected function sendPatientWelcomeSms(Patient $patient): array
    {
        if (empty($patient->phone)) {
            return [
                'success' => false,
                'error' => 'Patient has no phone number',
            ];
        }

        if (!SmsHelper::isConfigured()) {
            Log::warning('Patient welcome SMS skipped - SMS not configured', [
                'patient_id' => $patient->id,
                'mrn' => $patient->mrn,
            ]);

            return [
                'success' => false,
                'error' => 'SMS is not configured',
            ];
        }

        $patient->load(['company', 'branch']);

        $branchName = $patient->branch->name ?? 'tawi letu';
        $companyPhone = $patient->company->phone ?? '';

        $admittedDate = $patient->admitted_date
            ? $patient->admitted_date->format('d/m/Y')
            : now()->format('d/m/Y');

        $message = sprintf(
            'Hello %s tunashukuru kwa kufika katika Kliniki yetu tawi la %s. Nambari yako ya matibabu (MRN) ni %s. Tarehe ya kujiunga: %s. Kwa mawasiliano tupigie kupitia %s.',
            $patient->full_name,
            $branchName,
            $patient->mrn,
            $admittedDate,
            $companyPhone ?: 'namba yetu ya huduma'
        );

        $phone = function_exists('normalize_phone_number')
            ? normalize_phone_number($patient->phone)
            : $patient->phone;

        return SmsHelper::send($phone, $message);
    }

    /**
     * Search patients
     */
    public function searchPatients(Request $request)
    {
        $term = $request->get('term', '');
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        $patients = Patient::byCompany($companyId)
            ->byBranch($branchId)
            ->search($term)
            ->active()
            ->limit(20)
            ->get();

        return response()->json($patients);
    }

    /**
     * Show patient details
     */
    public function showPatient($id)
    {
        $patient = Patient::with(['visits', 'company', 'branch', 'insuranceType'])->findOrFail($id);
        $this->authorizePatientCompany($patient);

        $canDeletePatient = auth()->user()->can('delete patient') && $patient->canBeDeleted();
        $patientAttachedRecords = $patient->attachedRecordCounts();

        return view('hospital.reception.patients.show', compact(
            'patient',
            'canDeletePatient',
            'patientAttachedRecords'
        ));
    }

    /**
     * Edit patient form
     */
    public function editPatient($id)
    {
        $patient = Patient::with('insuranceType')->findOrFail($id);
        $insuranceTypes = HospitalInsuranceType::optionsForCompany(Auth::user()->company_id);

        return view('hospital.reception.patients.edit', compact('patient', 'insuranceTypes'));
    }

    /**
     * Update patient
     */
    public function updatePatient(Request $request, $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'admitted_date' => 'nullable|date',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'next_of_kin_name' => 'nullable|string|max:255',
            'next_of_kin_phone' => 'nullable|string|max:20',
            'next_of_kin_relationship' => 'nullable|string|max:255',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'blood_group' => 'nullable|string|max:10',
            'id_number' => 'nullable|string|max:50',
            'insurance_number' => 'nullable|string|max:50',
            'insurance_type_id' => 'nullable|exists:hospital_insurance_types,id',
        ]);

        try {
            $patient = Patient::findOrFail($id);
            $validated = $this->applyInsuranceTypeToPatientData($validated, $patient->company_id);
            $patient->update(array_merge($validated, [
                'updated_by' => Auth::id(),
            ]));

            return redirect()->route('hospital.reception.patients.show', $patient->id)
                ->with('success', 'Patient updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to update patient: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete patient immediately (admin with delete patient permission, no linked records only).
     */
    public function destroyPatient($id)
    {
        if (!auth()->user()->can('delete patient')) {
            abort(403, 'Unauthorized action.');
        }

        $patient = Patient::findOrFail($id);
        $this->authorizePatientCompany($patient);

        if (!PatientDeletionGuard::canDelete($patient)) {
            return redirect()
                ->route('hospital.reception.patients.show', $patient->id)
                ->with('error', PatientDeletionGuard::blockingMessage($patient));
        }

        try {
            $patient->deletionRequests()->where('status', 'pending')->update([
                'status' => 'rejected',
                'approval_notes' => 'Cancelled: patient deleted directly by administrator.',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $patient->delete();

            return redirect()
                ->route('hospital.reception.patients.index')
                ->with('success', 'Patient deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->route('hospital.reception.patients.show', $patient->id)
                ->with('error', 'Failed to delete patient: ' . $e->getMessage());
        }
    }

    /**
     * Request patient deletion (requires approval)
     */
    public function requestPatientDeletion(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        try {
            $patient = Patient::findOrFail($id);
            $this->authorizePatientCompany($patient);
            $user = Auth::user();

            if (PatientDeletionGuard::canDelete($patient)) {
                return redirect()
                    ->route('hospital.reception.patients.show', $patient->id)
                    ->with('info', 'This patient has no linked records. An administrator can delete them directly using Delete Patient.');
            }

            PatientDeletionRequest::create([
                'patient_id' => $patient->id,
                'reason' => $validated['reason'],
                'status' => 'pending',
                'initiated_by' => $user->id,
                'company_id' => $user->company_id,
                'branch_id' => session('branch_id') ?? $user->branch_id,
            ]);

            return redirect()->route('hospital.reception.patients.show', $patient->id)
                ->with('success', 'Deletion request submitted. Waiting for approval.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to submit deletion request: ' . $e->getMessage()]);
        }
    }

    /**
     * Create a new visit
     */
    public function createVisit($patientId)
    {
        $patient = Patient::with('insuranceType')->findOrFail($patientId);
        $departments = HospitalDepartment::active()
            ->where('company_id', Auth::user()->company_id)
            ->get();
        
        // Get services from inventory_items where item_type = 'service'
        $services = Item::where('company_id', Auth::user()->company_id)
            ->where('item_type', 'service')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('hospital.reception.visits.create', compact('patient', 'departments', 'services'));
    }

    /**
     * Store a new visit
     */
    public function storeVisit(Request $request, $patientId)
    {
        $validated = $request->validate([
            'visit_date' => 'required|date',
            'visit_type' => 'required|in:new,follow_up,emergency',
            'chief_complaint' => 'nullable|string',
            'departments' => 'required|array|min:1',
            'departments.*' => 'exists:hospital_departments,id',
            'services' => 'nullable|array',
            'services.*.service_id' => 'exists:inventory_items,id',
            'services.*.quantity' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;
            $patient = Patient::findOrFail($patientId);
            $visitAt = Carbon::parse($validated['visit_date']);

            // Generate visit number from chosen visiting date
            $visitNumber = 'VIS-' . $visitAt->format('Ymd') . '-' . str_pad(
                Visit::where('company_id', $companyId)
                    ->whereDate('visit_date', $visitAt->toDateString())
                    ->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            // Create visit
            $visit = Visit::create([
                'visit_number' => $visitNumber,
                'patient_id' => $patient->id,
                'visit_type' => $validated['visit_type'],
                'status' => 'pending',
                'chief_complaint' => $validated['chief_complaint'] ?? null,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'created_by' => $user->id,
                'visit_date' => $visitAt,
            ]);

            // Create visit departments
            // Auto-add Triage if not selected (unless going directly to Pharmacy only)
            $selectedDepartments = collect($validated['departments']);
            $hasTriage = HospitalDepartment::whereIn('id', $validated['departments'])
                ->where('type', 'triage')
                ->exists();
            
            $hasOnlyPharmacy = HospitalDepartment::whereIn('id', $validated['departments'])
                ->where('type', 'pharmacy')
                ->count() === $selectedDepartments->count() && $selectedDepartments->count() === 1;
            
            // If no Triage and not going directly to Pharmacy, add Triage automatically
            if (!$hasTriage && !$hasOnlyPharmacy) {
                $triageDept = HospitalDepartment::where('company_id', $companyId)
                    ->where('type', 'triage')
                    ->where('is_active', true)
                    ->first();
                
                if ($triageDept) {
                    $validated['departments'][] = $triageDept->id;
                }
            }
            
            $sequence = 1;
            foreach ($validated['departments'] as $departmentId) {
                VisitDepartment::create([
                    'visit_id' => $visit->id,
                    'department_id' => $departmentId,
                    'status' => 'waiting',
                    'waiting_started_at' => $visitAt,
                    'sequence' => $sequence++,
                ]);
            }

            // Create pre-bill if services are selected (using SalesInvoice structure)
            if (!empty($validated['services'])) {
                // Filter out empty service selections
                $validServices = array_filter($validated['services'], function($serviceData) {
                    return !empty($serviceData['service_id']);
                });

                if (!empty($validServices)) {
                    // Get or create customer from patient
                    $customer = $this->getOrCreateCustomerFromPatient($patient);

                    // Create sales invoice for pre-billing services
                    $invoice = SalesInvoice::create([
                        'customer_id' => $customer->id,
                        'invoice_date' => $visitAt,
                        'due_date' => $visitAt, // Pre-bills are due immediately
                        'status' => 'draft',
                        'currency' => 'TZS',
                        'exchange_rate' => 1.000000,
                        'branch_id' => $branchId,
                        'company_id' => $companyId,
                        'created_by' => $user->id,
                        'notes' => "Pre-billing services for Visit #{$visit->visit_number} - Patient: {$patient->full_name}",
                    ]);

                    $subtotal = 0;

                    // Add services to invoice
                    foreach ($validServices as $serviceData) {
                        $service = Item::find($serviceData['service_id']);
                        if (!$service || $service->item_type !== 'service') {
                            continue;
                        }

                        $quantity = $serviceData['quantity'] ?? 1;
                        $unitPrice = $service->unit_price;
                        $lineTotal = $unitPrice * $quantity;
                        $subtotal += $lineTotal;

                        // Create invoice item
                        SalesInvoiceItem::create([
                            'sales_invoice_id' => $invoice->id,
                            'inventory_item_id' => $service->id,
                            'item_name' => $service->name,
                            'item_code' => $service->code,
                            'description' => $service->description,
                            'unit_of_measure' => $service->unit_of_measure ?? 'Unit',
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'line_total' => $lineTotal,
                            'vat_type' => 'no_vat',
                            'vat_rate' => 0,
                            'vat_amount' => 0,
                            'discount_type' => null,
                            'discount_rate' => 0,
                            'discount_amount' => 0,
                        ]);
                    }

                    // Update invoice totals
                    $invoice->subtotal = $subtotal;
                    $invoice->vat_amount = 0;
                    $invoice->discount_amount = 0;
                    $invoice->total_amount = $subtotal;
                    $invoice->balance_due = $subtotal;
                    $invoice->status = 'sent'; // Mark as sent to cashier
                    $invoice->save();

                    // Create GL transactions (double-entry accounting)
                    $invoice->createDoubleEntryTransactions();

                    // Link invoice to visit for reference
                    $visit->update(['notes' => ($visit->notes ?? '') . "\n\nPre-billing Invoice: {$invoice->invoice_number}"]);
                }
            }

            DB::commit();

            return redirect()->route('hospital.reception.visits.show', $visit->id)
                ->with('success', 'Visit created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create visit: ' . $e->getMessage()]);
        }
    }

    /**
     * Show visit details
     */
    public function showVisit($id)
    {
        $visit = Visit::with([
            'patient',
            'visitDepartments.department',
            'visitDepartments.servedBy',
            'bills.items',
            'triageVitals',
            'consultation.doctor',
            'labResults.service',
            'labResults.performedBy',
            'ultrasoundResults.service',
            'ultrasoundResults.performedBy',
            'dentalRecords.service',
            'dentalRecords.performedBy',
            'vaccinationRecords.item',
            'vaccinationRecords.performedBy',
            'injectionRecords.item',
            'injectionRecords.performedBy',
            'diagnosisExplanation',
            'pharmacyDispensations.items.product',
            'pharmacyDispensations.dispensedBy',
        ])->findOrFail($id);

        // Get paid invoices for this visit
        $paidInvoices = collect();
        $patient = $visit->patient;
        
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
                // Get all paid invoices for this visit (check notes for visit number)
                $paidInvoices = SalesInvoice::where('customer_id', $customer->id)
                    ->where('company_id', $patient->company_id)
                    ->where('branch_id', $patient->branch_id)
                    ->where('status', 'paid')
                    ->where(function ($q) use ($visit) {
                        $q->where('notes', 'like', "%Visit #{$visit->visit_number}%")
                          ->orWhere('notes', 'like', "%for Visit #{$visit->visit_number}%");
                    })
                    ->with(['items.inventoryItem', 'customer', 'receipts'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        return view('hospital.reception.visits.show', compact('visit', 'paidInvoices'));
    }

    /**
     * Print lab/ultrasound results
     */
    public function printResults(Request $request, $visit)
    {
        $visit = Visit::findOrFail($visit);
        $type = $request->get('type'); // 'lab' or 'ultrasound'
        $format = $request->get('format', 'a4'); // 'a4' or 'thermal'
        $resultId = $request->get('result_id');

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        if ($type === 'lab' && $resultId) {
            $labResult = \App\Models\Hospital\LabResult::with(['patient', 'visit', 'performedBy'])->findOrFail($resultId);
            if ($labResult->company_id !== Auth::user()->company_id) {
                abort(403, 'Unauthorized access to result.');
            }
            
            // Mark as printed
            $labResult->result_status = 'printed';
            $labResult->printed_at = now();
            $labResult->save();

            if ($format === 'thermal') {
                return view('hospital.reception.visits.print.thermal-lab', compact('labResult'));
            }
            return view('hospital.lab.print', compact('labResult'));
        } elseif ($type === 'ultrasound' && $resultId) {
            $ultrasoundResult = \App\Models\Hospital\UltrasoundResult::with(['patient', 'visit', 'performedBy'])->findOrFail($resultId);
            if ($ultrasoundResult->company_id !== Auth::user()->company_id) {
                abort(403, 'Unauthorized access to result.');
            }
            
            // Mark as printed
            $ultrasoundResult->result_status = 'printed';
            $ultrasoundResult->printed_at = now();
            $ultrasoundResult->save();

            // Get images for ultrasound
            $images = $ultrasoundResult->images ?? [];

            if ($format === 'thermal') {
                return view('hospital.reception.visits.print.thermal-ultrasound', compact('ultrasoundResult'));
            }
            return view('hospital.ultrasound.print', compact('ultrasoundResult', 'images'));
        }

        return back()->withErrors(['error' => 'Invalid result type or ID.']);
    }

    /**
     * Create a new bill for a visit
     */
    public function createBill($visitId)
    {
        $visit = Visit::with(['patient', 'bills'])->findOrFail($visitId);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        // Check if there's already a final bill
        $hasFinalBill = $visit->bills()->where('bill_type', 'final')->exists();
        if ($hasFinalBill) {
            return redirect()->route('hospital.reception.visits.show', $visit->id)
                ->with('info', 'A final bill already exists for this visit.');
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Generate bill number
            $billNumber = 'BILL-' . now()->format('Ymd') . '-' . str_pad(VisitBill::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Create bill
            $bill = VisitBill::create([
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

            DB::commit();

            return redirect()->route('hospital.cashier.bills.show', $bill->id)
                ->with('success', 'Bill created successfully. You can now add items.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('hospital.reception.visits.show', $visit->id)
                ->withErrors(['error' => 'Failed to create bill: ' . $e->getMessage()]);
        }
    }

    /**
     * Send results back to Doctor
     */
    public function sendToDoctor(Request $request, $visit)
    {
        $visit = Visit::findOrFail($visit);

        // Verify access
        if ($visit->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to visit.');
        }

        // Check if visit has a doctor consultation
        $doctorDept = $visit->visitDepartments()
            ->whereHas('department', function ($q) {
                $q->where('type', 'doctor');
            })
            ->first();

        if (!$doctorDept) {
            return back()->withErrors(['error' => 'No doctor department found for this visit.']);
        }

        // If doctor department is completed, create a new visit department entry
        if ($doctorDept->status === 'completed') {
            // Create a new visit department entry for follow-up
            VisitDepartment::create([
                'visit_id' => $visit->id,
                'department_id' => $doctorDept->department_id,
                'status' => 'waiting',
                'waiting_started_at' => now(),
                'sequence' => $visit->visitDepartments()->max('sequence') + 1,
            ]);
        } else {
            // Reset doctor department to waiting
            $doctorDept->status = 'waiting';
            $doctorDept->waiting_started_at = now();
            $doctorDept->service_started_at = null;
            $doctorDept->service_ended_at = null;
            $doctorDept->waiting_time_seconds = 0;
            $doctorDept->service_time_seconds = 0;
            $doctorDept->save();
        }

        return redirect()->route('hospital.reception.visits.show', $visit->id)
            ->with('success', 'Patient sent back to Doctor for review.');
    }

    /**
     * Get patient location/department status
     */
    public function getPatientLocation($visitId)
    {
        $visit = Visit::with(['visitDepartments.department', 'patient'])
            ->findOrFail($visitId);

        $currentDepartment = $visit->visitDepartments()
            ->whereIn('status', ['waiting', 'in_service'])
            ->orderBy('sequence')
            ->first();

        return response()->json([
            'visit' => $visit,
            'current_department' => $currentDepartment ? [
                'id' => $currentDepartment->department->id,
                'name' => $currentDepartment->department->name,
                'type' => $currentDepartment->department->type,
                'status' => $currentDepartment->status,
                'waiting_time' => $currentDepartment->waiting_time_formatted,
                'service_time' => $currentDepartment->service_time_formatted,
            ] : null,
        ]);
    }

    /**
     * Get or create customer from patient
     */
    private function getOrCreateCustomerFromPatient($patient)
    {
        // Check if customer already exists with same phone, email, or name
        $customer = Customer::where('company_id', $patient->company_id)
            ->where(function ($q) use ($patient) {
                if ($patient->phone) {
                    $q->where('phone', $patient->phone);
                }
                if ($patient->email) {
                    $q->orWhere('email', $patient->email);
                }
                // If no phone or email, search by name to avoid duplicates
                if (!$patient->phone && !$patient->email) {
                    $q->orWhere('name', $patient->full_name);
                }
            })
            ->first();

        if ($customer) {
            return $customer;
        }

        // Create new customer from patient
        // Use placeholder phone if patient phone is null (Customer requires phone)
        // Format: MRN-based or timestamp-based to ensure uniqueness
        $phone = $patient->phone ?? ('000' . str_pad($patient->id ?? time(), 9, '0', STR_PAD_LEFT)); // 12 digits
        
        // Generate customerNo explicitly (same as CustomerController)
        $customerNo = 100000 + (Customer::max('id') ?? 0) + 1;
        
        $customer = Customer::create([
            'customerNo' => $customerNo,
            'name' => $patient->full_name,
            'phone' => $phone,
            'email' => $patient->email,
            'company_id' => $patient->company_id,
            'branch_id' => $patient->branch_id,
            'status' => 'active',
        ]);

        return $customer;
    }

    protected function authorizePatientCompany(Patient $patient): void
    {
        if ($patient->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this patient.');
        }
    }
}
