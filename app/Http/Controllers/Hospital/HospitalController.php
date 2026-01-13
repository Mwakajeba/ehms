<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Patient;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ],
            'bills' => [
                'pending' => VisitBill::where('company_id', $companyId)->where('branch_id', $branchId)->where('payment_status', 'pending')->count(),
                'paid_today' => VisitBill::where('company_id', $companyId)->where('branch_id', $branchId)->where('payment_status', 'paid')->whereDate('created_at', today())->count(),
            ],
        ];

        return view('hospital.index', compact('stats'));
    }
}
