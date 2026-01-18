<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        // Get active visits with their current department status
        $activeVisits = Visit::with(['patient', 'visitDepartments.department'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('visit_date', 'desc')
            ->get();

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

        return view('hospital.reception.index', compact('activeVisits', 'waitingByDepartment', 'inServiceByDepartment'));
    }

    /**
     * Show patient registration form
     */
    public function createPatient()
    {
        return view('hospital.reception.patients.create');
    }

    /**
     * Register a new patient
     */
    public function storePatient(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
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
            'insurance_type' => 'nullable|in:NHIF,CHF,Jubilee,Strategy,None',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

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

            return redirect()->route('hospital.reception.patients.show', $patient->id)
                ->with('success', 'Patient registered successfully. MRN: ' . $mrn);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to register patient: ' . $e->getMessage()]);
        }
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
        $patient = Patient::with(['visits', 'company', 'branch'])->findOrFail($id);
        return view('hospital.reception.patients.show', compact('patient'));
    }

    /**
     * Edit patient form
     */
    public function editPatient($id)
    {
        $patient = Patient::findOrFail($id);
        return view('hospital.reception.patients.edit', compact('patient'));
    }

    /**
     * Update patient
     */
    public function updatePatient(Request $request, $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
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
            'insurance_type' => 'nullable|in:NHIF,CHF,Jubilee,Strategy,None',
        ]);

        try {
            $patient = Patient::findOrFail($id);
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
     * Request patient deletion (requires approval)
     */
    public function requestPatientDeletion(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        try {
            $patient = Patient::findOrFail($id);
            $user = Auth::user();

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
        $patient = Patient::findOrFail($patientId);
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

            // Generate visit number
            $visitNumber = 'VIS-' . now()->format('Ymd') . '-' . str_pad(Visit::whereDate('visit_date', today())->count() + 1, 4, '0', STR_PAD_LEFT);

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
                'visit_date' => now(),
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
                    'waiting_started_at' => now(),
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
                        'invoice_date' => now(),
                        'due_date' => now(), // Pre-bills are due immediately
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
}
