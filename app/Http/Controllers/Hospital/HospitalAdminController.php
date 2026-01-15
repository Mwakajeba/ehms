<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\HospitalDepartment;
use App\Models\Hospital\PatientDeletionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HospitalAdminController extends Controller
{
    /**
     * Display hospital admin dashboard with cards
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Get statistics for cards
        $stats = [
            'departments' => [
                'total' => HospitalDepartment::where('company_id', $companyId)
                    ->where(function($query) use ($branchId) {
                        if ($branchId) {
                            $query->where('branch_id', $branchId)
                                  ->orWhereNull('branch_id');
                        } else {
                            $query->whereNull('branch_id');
                        }
                    })
                    ->count(),
                'active' => HospitalDepartment::where('company_id', $companyId)
                    ->where(function($query) use ($branchId) {
                        if ($branchId) {
                            $query->where('branch_id', $branchId)
                                  ->orWhereNull('branch_id');
                        } else {
                            $query->whereNull('branch_id');
                        }
                    })
                    ->where('is_active', true)
                    ->count(),
            ],
            'deletion_requests' => [
                'pending' => PatientDeletionRequest::where('company_id', $companyId)
                    ->where(function($query) use ($branchId) {
                        if ($branchId) {
                            $query->where('branch_id', $branchId)
                                  ->orWhereNull('branch_id');
                        } else {
                            $query->whereNull('branch_id');
                        }
                    })
                    ->where('status', 'pending')
                    ->count(),
                'total' => PatientDeletionRequest::where('company_id', $companyId)
                    ->where(function($query) use ($branchId) {
                        if ($branchId) {
                            $query->where('branch_id', $branchId)
                                  ->orWhereNull('branch_id');
                        } else {
                            $query->whereNull('branch_id');
                        }
                    })
                    ->count(),
            ],
        ];

        return view('hospital.admin.index', compact('stats'));
    }
}
