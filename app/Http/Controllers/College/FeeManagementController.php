<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeeManagementController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $branchId = auth()->user()->branch_id;

        // Get basic statistics
        $totalInvoices = \App\Models\College\FeeInvoice::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->count();

        $totalCollected = \App\Models\College\FeeInvoice::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->where('status', 'paid')
            ->sum('total_amount');

        $pendingAmount = \App\Models\College\FeeInvoice::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->where('status', 'issued')
            ->sum('total_amount');

        $overdueAmount = \App\Models\College\FeeInvoice::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->where('status', 'overdue')
            ->sum('total_amount');

        $activeStudents = \App\Models\College\Student::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('status', 'active')
            ->count();

        // Calculate total invoice amount (sum of all invoice amounts regardless of status)
        $totalInvoiceAmount = \App\Models\College\FeeInvoice::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->sum('total_amount');

        // Calculate collection rate: (collected / total_invoice_amount) * 100
        $collectionRate = $totalInvoiceAmount > 0 ? round(($totalCollected / $totalInvoiceAmount) * 100, 1) : 0;

        return view('college.fee-management.index', compact(
            'totalInvoices',
            'totalCollected',
            'pendingAmount',
            'overdueAmount',
            'activeStudents',
            'collectionRate',
            'totalInvoiceAmount'
        ));
    }

    public function create()
    {
        return view('college.fee-management.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement store logic
        return redirect()->route('college.fee-management.index');
    }

    public function show($id)
    {
        return view('college.fee-management.show', compact('id'));
    }

    public function edit($id)
    {
        return view('college.fee-management.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement update logic
        return redirect()->route('college.fee-management.index');
    }

    public function destroy($id)
    {
        // TODO: Implement destroy logic
        return redirect()->route('college.fee-management.index');
    }
}