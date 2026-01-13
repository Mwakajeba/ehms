<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeeManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Get basic statistics
        $totalInvoices = \App\Models\FeeInvoice::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->count();

        $totalCollected = \App\Models\FeeInvoice::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->where('status', 'paid')
            ->sum('total_amount');

        $pendingAmount = \App\Models\FeeInvoice::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->where('status', 'issued')
            ->sum('total_amount');

        $overdueAmount = \App\Models\FeeInvoice::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->where('status', 'overdue')
            ->sum('total_amount');

        $totalCollection = $totalCollected + $pendingAmount + $overdueAmount;

        $paidInvoices = \App\Models\FeeInvoice::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->where('status', 'paid')
            ->count();

        $activeStudents = \App\Models\School\Student::whereHas('class', function($query) use ($companyId, $branchId) {
            $query->where('company_id', $companyId);
            if ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            }
        })
        ->where('status', 'active')
        ->count();

        $collectionRate = $totalInvoices > 0 ? round(($totalCollected / ($totalCollected + $pendingAmount + $overdueAmount)) * 100, 1) : 0;

        // Get counts for fee management items
        $feeGroupsCount = \App\Models\FeeGroup::where('company_id', $companyId)->count();
        
        $feeSettingsCount = \App\Models\FeeSetting::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();
        
        $otherIncomeCount = \App\Models\OtherIncome::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();
        
        $studentOpeningBalanceCount = \App\Models\School\StudentFeeOpeningBalance::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->count();

        // Get prepaid account statistics
        $prepaidAccountsCount = \App\Models\School\StudentPrepaidAccount::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->count();
        
        $totalPrepaidCredit = \App\Models\School\StudentPrepaidAccount::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->sum('credit_balance');

        return view('school.fee-management.index', compact(
            'totalInvoices',
            'totalCollected',
            'pendingAmount',
            'overdueAmount',
            'totalCollection',
            'paidInvoices',
            'activeStudents',
            'collectionRate',
            'feeGroupsCount',
            'feeSettingsCount',
            'otherIncomeCount',
            'studentOpeningBalanceCount',
            'prepaidAccountsCount',
            'totalPrepaidCredit'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
