<?php

namespace App\Http\Controllers;

use App\Models\PayrollApprovalSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollSettingsController extends Controller
{
    public function index()
    {
        $userCompanyId = Auth::user()->company_id;
        $userBranchId = Auth::user()->branch_id ?? null;

        // Get current approval settings for display
        $approvalSettings = PayrollApprovalSettings::where('company_id', $userCompanyId)
            ->where(function($query) use ($userBranchId) {
                if ($userBranchId) {
                    $query->where('branch_id', $userBranchId);
                } else {
                    $query->whereNull('branch_id');
                }
            })
            ->first();

        // Get current payment approval settings for display
        $paymentApprovalSettings = \App\Models\PayrollPaymentApprovalSettings::getSettingsForCompany(
            $userCompanyId,
            $userBranchId
        );

        // Get current overtime approval settings for display
        $overtimeApprovalSettings = \App\Models\OvertimeApprovalSettings::where('company_id', $userCompanyId)
            ->where(function($query) use ($userBranchId) {
                if ($userBranchId) {
                    $query->where('branch_id', $userBranchId);
                } else {
                    $query->whereNull('branch_id');
                }
            })
            ->first();

        return view('hr-payroll.payroll-settings.index', compact('approvalSettings', 'paymentApprovalSettings', 'overtimeApprovalSettings'));
    }
}
