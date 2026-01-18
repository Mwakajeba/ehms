<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Patient;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitBill;
use App\Models\Sales\SalesInvoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HospitalController extends Controller
{
    /**
     * Display hospital management dashboard with cards
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Get statistics for cards
        $stats = [
            'patients' => [
                'total' => Patient::byCompany($companyId)->byBranch($branchId)->active()->count(),
                'today' => Patient::byCompany($companyId)->byBranch($branchId)->whereDate('created_at', today())->count(),
            ],
            'visits' => [
                'total' => Visit::where('company_id', $companyId)->where('branch_id', $branchId)->count(),
                'today' => Visit::where('company_id', $companyId)->where('branch_id', $branchId)->whereDate('visit_date', today())->count(),
                'pending' => Visit::where('company_id', $companyId)->where('branch_id', $branchId)->where('status', 'pending')->count(),
                'in_progress' => Visit::where('company_id', $companyId)->where('branch_id', $branchId)->where('status', 'in_progress')->count(),
                // Triage pending: visits waiting for triage with cleared bills OR paid SalesInvoice
                'triage_pending' => Visit::where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->whereHas('visitDepartments', function ($q) {
                        $q->whereHas('department', function ($query) {
                            $query->where('type', 'triage');
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
                    ->count(),
                // Doctor pending: visits waiting for doctor (triage completed, bills cleared or paid)
                'doctor_pending' => Visit::where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->whereHas('visitDepartments', function ($q) {
                        $q->whereHas('department', function ($query) {
                            $query->where('type', 'doctor');
                        })->where('status', 'waiting');
                    })
                    ->where(function ($query) {
                        // Either no bills exist, or at least one bill is cleared
                        $query->doesntHave('bills')
                              ->orWhereHas('bills', function ($q) {
                                  $q->where('clearance_status', 'cleared');
                              });
                    })
                    ->whereHas('triageVitals') // Must have completed triage
                    ->count(),
                // Lab pending: visits waiting for lab with cleared bills OR paid SalesInvoice
                'lab_pending' => Visit::where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->whereHas('visitDepartments', function ($q) {
                        $q->whereHas('department', function ($query) {
                            $query->where('type', 'lab');
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
                    ->count(),
                // Pharmacy pending: visits waiting for pharmacy with cleared bills OR paid SalesInvoice (pharmacy bill)
                'pharmacy_pending' => Visit::where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->whereHas('visitDepartments', function ($q) {
                        $q->whereHas('department', function ($query) {
                            $query->where('type', 'pharmacy');
                        })->where('status', 'waiting');
                    })
                    ->where(function ($query) use ($companyId, $branchId) {
                        // Either has cleared VisitBill (old flow)
                        $query->whereHas('bills', function ($q) {
                            $q->where('clearance_status', 'cleared');
                        })
                        // OR has Customer with paid SalesInvoice matching patient (new pre-billing flow - pharmacy bill)
                        ->orWhereExists(function ($subQuery) use ($companyId, $branchId) {
                            $subQuery->select(DB::raw(1))
                                ->from('sales_invoices')
                                ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
                                ->join('patients', 'patients.id', '=', 'visits.patient_id')
                                ->where('sales_invoices.company_id', $companyId)
                                ->where('sales_invoices.branch_id', $branchId)
                                ->where('sales_invoices.status', 'paid')
                                ->where('sales_invoices.notes', 'like', '%Pharmacy bill for Visit%')
                                ->where(function ($q) {
                                    $q->whereColumn('customers.phone', 'patients.phone')
                                        ->orWhereColumn('customers.email', 'patients.email')
                                        ->orWhereColumn('customers.name', DB::raw("CONCAT(patients.first_name, ' ', patients.last_name)"));
                                });
                        });
                    })
                    ->count(),
                // Ultrasound pending: visits waiting for ultrasound with cleared bills OR paid SalesInvoice
                'ultrasound_pending' => Visit::where('company_id', $companyId)
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
                    ->count(),
                // Dental pending: visits waiting for dental with cleared bills OR paid SalesInvoice
                'dental_pending' => Visit::where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->whereHas('visitDepartments', function ($q) {
                        $q->whereHas('department', function ($query) {
                            $query->where('type', 'dental');
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
                    ->count(),
                // Injection pending: visits waiting for vaccine department with cleared bills OR paid SalesInvoice for injection
                'injection_pending' => Visit::where('company_id', $companyId)
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
                    ->count(),
                // Vaccination pending: visits waiting for vaccine department with cleared bills OR paid SalesInvoice for vaccination
                'vaccination_pending' => Visit::where('company_id', $companyId)
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
                    ->count(),
                // Family Planning pending: visits waiting for family_planning department with cleared bills OR paid SalesInvoice for family planning
                'family_planning_pending' => Visit::where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->whereHas('visitDepartments', function ($q) {
                        $q->whereHas('department', function ($query) {
                            $query->where('type', 'family_planning');
                        })->where('status', 'waiting');
                    })
                    ->where(function ($query) use ($companyId, $branchId) {
                        // Either has cleared VisitBill (old flow)
                        $query->whereHas('bills', function ($q) {
                            $q->where('clearance_status', 'cleared');
                        })
                        // OR has Customer with paid SalesInvoice for family planning matching patient (new pre-billing flow)
                        ->orWhereExists(function ($subQuery) use ($companyId, $branchId) {
                            $subQuery->select(DB::raw(1))
                                ->from('sales_invoices')
                                ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
                                ->join('patients', 'patients.id', '=', 'visits.patient_id')
                                ->where('sales_invoices.company_id', $companyId)
                                ->where('sales_invoices.branch_id', $branchId)
                                ->where('sales_invoices.status', 'paid')
                                ->where('sales_invoices.notes', 'like', '%Family Planning bill for Visit #%')
                                ->where(function ($q) {
                                    $q->whereColumn('customers.phone', 'patients.phone')
                                        ->orWhereColumn('customers.email', 'patients.email')
                                        ->orWhereColumn('customers.name', DB::raw("CONCAT(patients.first_name, ' ', patients.last_name)"));
                                });
                        });
                    })
                    ->count(),
            ],
            'bills' => [
                // Use SalesInvoice for pending bills (pre-billing services)
                'pending' => SalesInvoice::where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->whereIn('status', ['draft', 'sent']) // Pending payment
                    ->count(),
                'paid_today' => SalesInvoice::where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->where('status', 'paid')
                    ->whereDate('created_at', today())
                    ->count(),
            ],
        ];

        return view('hospital.index', compact('stats'));
    }
}
