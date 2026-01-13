<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use App\Models\FeeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class FeeGroupController extends Controller
{
    public function index()
    {
        return view('college.fee-groups.index');
    }

    public function create()
    {
        $chartAccounts = ChartAccount::orderBy('account_name')->get();

        return view('college.fee-groups.create', compact('chartAccounts'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fee_code' => 'required|string|max:50|unique:fee_groups,fee_code',
            'name' => 'required|string|max:255',
            'receivable_account_id' => 'required|exists:chart_accounts,id',
            'income_account_id' => 'required|exists:chart_accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        FeeGroup::create([
            'fee_code' => $request->fee_code,
            'name' => $request->name,
            'receivable_account_id' => $request->receivable_account_id,
            'income_account_id' => $request->income_account_id,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
            'type' => 'college', // Specify this is for college
            'company_id' => Auth::user()->company_id,
            'branch_id' => Auth::user()->branch_id,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('college.fee-groups.index')
            ->with('success', 'Fee Group created successfully.');
    }

    public function show($hashid)
    {
        $feeGroup = FeeGroup::findByHashid($hashid);

        if (!$feeGroup) {
            return redirect()->route('college.fee-groups.index')
                ->with('error', 'Fee Group not found.');
        }

        return view('college.fee-groups.show', compact('feeGroup'));
    }

    public function edit($hashid)
    {
        $feeGroup = FeeGroup::findByHashid($hashid);

        if (!$feeGroup) {
            return redirect()->route('college.fee-groups.index')
                ->with('error', 'Fee Group not found.');
        }

        $chartAccounts = ChartAccount::orderBy('account_name')->get();

        return view('college.fee-groups.edit', compact('feeGroup', 'chartAccounts'));
    }

    public function update(Request $request, $hashid)
    {
        $feeGroup = FeeGroup::findByHashid($hashid);

        if (!$feeGroup) {
            return redirect()->route('college.fee-groups.index')
                ->with('error', 'Fee Group not found.');
        }

        $validator = Validator::make($request->all(), [
            'fee_code' => 'required|string|max:50|unique:fee_groups,fee_code,' . $feeGroup->id,
            'name' => 'required|string|max:255',
            'receivable_account_id' => 'required|exists:chart_accounts,id',
            'income_account_id' => 'required|exists:chart_accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $feeGroup->update([
            'fee_code' => $request->fee_code,
            'name' => $request->name,
            'receivable_account_id' => $request->receivable_account_id,
            'income_account_id' => $request->income_account_id,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('college.fee-groups.index')
            ->with('success', 'Fee Group updated successfully.');
    }

    public function destroy($hashid)
    {
        $feeGroup = FeeGroup::findByHashid($hashid);

        if (!$feeGroup) {
            return redirect()->route('college.fee-groups.index')
                ->with('error', 'Fee Group not found.');
        }

        $feeGroup->forceDelete();

        return redirect()->route('college.fee-groups.index')
            ->with('success', 'Fee Group deleted successfully.');
    }

    public function data()
    {
        // Handle unauthenticated requests for testing (use default company_id = 1)
        $companyId = Auth::check() ? Auth::user()->company_id : 1;
        $branchId = Auth::check() ? Auth::user()->branch_id : null;

        $feeGroups = FeeGroup::with(['receivableAccount', 'incomeAccount'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                } else {
                    // For unauthenticated requests, include all branches or null
                    $query->whereNotNull('branch_id')
                          ->orWhereNull('branch_id');
                }
            })
            ->get();

        return DataTables::of($feeGroups)
            ->addIndexColumn()
            ->addColumn('fee_code', function ($feeGroup) {
                return '<span class="badge bg-warning text-dark">' . $feeGroup->fee_code . '</span>';
            })
            ->addColumn('receivable_account', function ($feeGroup) {
                return $feeGroup->receivableAccount ? $feeGroup->receivableAccount->account_name : 'N/A';
            })
            ->addColumn('income_account', function ($feeGroup) {
                return $feeGroup->incomeAccount ? $feeGroup->incomeAccount->account_name : 'N/A';
            })
            ->addColumn('status', function ($feeGroup) {
                return $feeGroup->is_active 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('actions', function ($feeGroup) {
                return view('college.fee-groups.partials.actions', compact('feeGroup'))->render();
            })
            ->rawColumns(['fee_code', 'status', 'actions'])
            ->make(true);
    }
}