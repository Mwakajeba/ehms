<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use App\Models\FeeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class FeeGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('school.fee-groups.index');
    }

    /**
     * Get fee groups data for DataTables.
     */
    public function data(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = FeeGroup::with(['receivableAccount', 'incomeAccount', 'transportIncomeAccount', 'discountAccount', 'openingBalanceAccount'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('receivable_account', function ($feeGroup) {
                return $feeGroup->receivableAccount ? $feeGroup->receivableAccount->account_name : 'N/A';
            })
            ->addColumn('income_account', function ($feeGroup) {
                return $feeGroup->incomeAccount ? $feeGroup->incomeAccount->account_name : 'N/A';
            })
            ->addColumn('transport_income_account', function ($feeGroup) {
                return $feeGroup->transportIncomeAccount ? $feeGroup->transportIncomeAccount->account_name : 'N/A';
            })
            ->addColumn('discount_account', function ($feeGroup) {
                return $feeGroup->discountAccount ? $feeGroup->discountAccount->account_name : 'N/A';
            })
            ->addColumn('opening_balance_account', function ($feeGroup) {
                return $feeGroup->openingBalanceAccount ? $feeGroup->openingBalanceAccount->account_name : 'N/A';
            })
            ->addColumn('status_badge', function ($feeGroup) {
                return $feeGroup->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('actions', function ($feeGroup) {
                return view('school.fee-groups.partials.actions', compact('feeGroup'))->render();
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $chartAccounts = ChartAccount::orderBy('account_name')->get();

        return view('school.fee-groups.create', compact('chartAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fee_code' => 'required|string|max:50|unique:fee_groups,fee_code',
            'name' => 'required|string|max:255',
            'receivable_account_id' => 'nullable|exists:chart_accounts,id',
            'income_account_id' => 'nullable|exists:chart_accounts,id',
            'transport_income_account_id' => 'nullable|exists:chart_accounts,id',
            'discount_account_id' => 'nullable|exists:chart_accounts,id',
            'opening_balance_account_id' => 'nullable|exists:chart_accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        FeeGroup::create([
            'fee_code' => $request->fee_code,
            'name' => $request->name,
            'receivable_account_id' => $request->receivable_account_id,
            'income_account_id' => $request->income_account_id,
            'transport_income_account_id' => $request->transport_income_account_id,
            'discount_account_id' => $request->discount_account_id,
            'opening_balance_account_id' => $request->opening_balance_account_id,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'company_id' => Auth::user()->company_id,
            'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('school.fee-groups.index')
            ->with('success', 'Fee Group created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($hashid)
    {
        $feeGroup = FeeGroup::findByHashid($hashid);

        if (!$feeGroup) {
            return redirect()->route('school.fee-groups.index')
                ->with('error', 'Fee Group not found.');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($feeGroup->branch_id !== $branchId && $feeGroup->branch_id !== null) {
            return redirect()->route('school.fee-groups.index')
                ->with('error', 'You do not have permission to view this fee group.');
        }

        // Load relationships
        $feeGroup->load(['receivableAccount', 'incomeAccount', 'transportIncomeAccount', 'discountAccount', 'openingBalanceAccount', 'creator']);

        return view('school.fee-groups.show', compact('feeGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($hashid)
    {
        $feeGroup = FeeGroup::findByHashid($hashid);

        if (!$feeGroup) {
            return redirect()->route('school.fee-groups.index')
                ->with('error', 'Fee Group not found.');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($feeGroup->branch_id !== $branchId && $feeGroup->branch_id !== null) {
            return redirect()->route('school.fee-groups.index')
                ->with('error', 'You do not have permission to edit this fee group.');
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $chartAccounts = ChartAccount::orderBy('account_name')->get();

        return view('school.fee-groups.edit', compact('feeGroup', 'chartAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $hashid)
    {
        $feeGroup = FeeGroup::findByHashid($hashid);

        if (!$feeGroup) {
            return redirect()->route('school.fee-groups.index')
                ->with('error', 'Fee Group not found.');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($feeGroup->branch_id !== $branchId && $feeGroup->branch_id !== null) {
            return redirect()->route('school.fee-groups.index')
                ->with('error', 'You do not have permission to update this fee group.');
        }

        $request->validate([
            'fee_code' => 'required|string|max:50|unique:fee_groups,fee_code,' . $feeGroup->id,
            'name' => 'required|string|max:255',
            'receivable_account_id' => 'nullable|exists:chart_accounts,id',
            'income_account_id' => 'nullable|exists:chart_accounts,id',
            'transport_income_account_id' => 'nullable|exists:chart_accounts,id',
            'discount_account_id' => 'nullable|exists:chart_accounts,id',
            'opening_balance_account_id' => 'nullable|exists:chart_accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $feeGroup->update([
            'fee_code' => $request->fee_code,
            'name' => $request->name,
            'receivable_account_id' => $request->receivable_account_id,
            'income_account_id' => $request->income_account_id,
            'transport_income_account_id' => $request->transport_income_account_id,
            'discount_account_id' => $request->discount_account_id,
            'opening_balance_account_id' => $request->opening_balance_account_id,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('school.fee-groups.index')
            ->with('success', 'Fee Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($hashid)
    {
        $feeGroup = FeeGroup::findByHashid($hashid);

        if (!$feeGroup) {
            return redirect()->route('school.fee-groups.index')
                ->with('error', 'Fee Group not found.');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($feeGroup->branch_id !== $branchId && $feeGroup->branch_id !== null) {
            return redirect()->route('school.fee-groups.index')
                ->with('error', 'You do not have permission to delete this fee group.');
        }

        $feeGroup->delete();

        return redirect()->route('school.fee-groups.index')
            ->with('success', 'Fee Group deleted successfully.');
    }
}
