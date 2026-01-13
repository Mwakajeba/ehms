<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\FeeSetting;
use App\Models\College\FeeSettingItem;
use App\Models\College\Program;
use App\Models\FeeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class FeeSettingController extends Controller
{
    public function index()
    {
        return view('college.fee-settings.index');
    }

    /**
     * Get fee settings data for DataTables.
     */
    public function data(Request $request)
    {
        $companyId = Auth::check() ? Auth::user()->company_id : null;
        $branchId = Auth::check() ? Auth::user()->branch_id : null;
        
        $query = FeeSetting::with(['program', 'collegeFeeSettingItems.feeGroup'])
            ->when($companyId, function ($query) use ($companyId) {
                return $query->forCompany($companyId);
            })
            ->when($branchId, function ($query) use ($branchId) {
                return $query->forBranch($branchId);
            });

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('program_name', function ($feeSetting) {
                return $feeSetting->program->name ?? 'N/A';
            })
            ->addColumn('fee_period', function ($feeSetting) {
                return $feeSetting->getFeePeriodOptions()[$feeSetting->fee_period] ?? $feeSetting->fee_period;
            })
            ->addColumn('amount', function ($feeSetting) {
                $total = $feeSetting->collegeFeeSettingItems->sum('amount');
                return config('app.currency', 'TZS') . ' ' . number_format($total, 2);
            })
            ->addColumn('start_date', function ($feeSetting) {
                return $feeSetting->date_from ? $feeSetting->date_from->format('M d, Y') : 'N/A';
            })
            ->addColumn('end_date', function ($feeSetting) {
                return $feeSetting->date_to ? $feeSetting->date_to->format('M d, Y') : 'N/A';
            })
            ->addColumn('status_badge', function ($feeSetting) {
                return $feeSetting->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('actions', function ($feeSetting) {
                $hashid = $feeSetting->hashid;
                return '<a href="' . route('college.fee-settings.show', $hashid) . '" class="btn btn-info btn-sm" title="View">
                            <i class="bx bx-show"></i>
                        </a>
                        <a href="' . route('college.fee-settings.edit', $hashid) . '" class="btn btn-warning btn-sm" title="Edit">
                            <i class="bx bx-edit"></i>
                        </a>
                        <button type="button" class="btn btn-danger btn-sm" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteModal" data-url="' . route('college.fee-settings.destroy', $hashid) . '">
                            <i class="bx bx-trash"></i>
                        </button>';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = Auth::check() ? Auth::user()->company_id : null;
        $branchId = Auth::check() ? Auth::user()->branch_id : null;

        $programs = Program::when($companyId, function ($query) use ($companyId) {
            return $query->where('company_id', $companyId);
        })
            ->active()
            ->orderBy('name')
            ->get();

        $feeGroups = FeeGroup::when($companyId, function ($query) use ($companyId) {
            return $query->where('company_id', $companyId);
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $feePeriodOptions = FeeSetting::getFeePeriodOptions();

        return view('college.fee-settings.create', compact('programs', 'feeGroups', 'feePeriodOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:college_programs,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'fee_lines' => 'required|array|min:1',
            'fee_lines.*.fee_group_id' => 'required|exists:fee_groups,id',
            'fee_lines.*.fee_period' => 'required|in:' . implode(',', array_keys(FeeSetting::getFeePeriodOptions())),
            'fee_lines.*.amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Custom validation for date logic
        if ($request->date_from && $request->date_to) {
            $request->validate([
                'date_to' => 'after_or_equal:date_from',
            ], [
                'date_to.after_or_equal' => 'The end date must be after or equal to the start date.',
            ]);
        }

        $feeSetting = FeeSetting::create([
            'program_id' => $request->program_id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'company_id' => Auth::check() ? Auth::user()->company_id : null,
            'branch_id' => Auth::check() ? Auth::user()->branch_id : null,
            'created_by' => Auth::check() ? Auth::id() : null,
        ]);

        // Create fee setting items
        if ($request->has('fee_lines')) {
            foreach ($request->fee_lines as $lineData) {
                $feeSetting->collegeFeeSettingItems()->create([
                    'fee_group_id' => $lineData['fee_group_id'],
                    'fee_period' => $lineData['fee_period'],
                    'amount' => $lineData['amount'],
                ]);
            }
        }

        return redirect()->route('college.fee-settings.index')
            ->with('success', 'College Fee Setting created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FeeSetting $feeSetting)
    {
        // Skip the not found check

        return view('college.fee-settings.show', compact('feeSetting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FeeSetting $feeSetting)
    {
        $companyId = Auth::check() ? Auth::user()->company_id : null;
        $branchId = Auth::check() ? Auth::user()->branch_id : null;

        $programs = Program::when($companyId, function ($query) use ($companyId) {
            return $query->where('company_id', $companyId);
        })
            ->active()
            ->orderBy('name')
            ->get();

        $feeGroups = FeeGroup::when($companyId, function ($query) use ($companyId) {
            return $query->where('company_id', $companyId);
        })
            ->orderBy('name')
            ->get();

        $feePeriodOptions = FeeSetting::getFeePeriodOptions();

        return view('college.fee-settings.edit', compact('feeSetting', 'programs', 'feeGroups', 'feePeriodOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FeeSetting $feeSetting)
    {
        $request->validate([
            'program_id' => 'required|exists:college_programs,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'fee_lines' => 'required|array|min:1',
            'fee_lines.*.fee_group_id' => 'required|exists:fee_groups,id',
            'fee_lines.*.fee_period' => 'required|in:' . implode(',', array_keys(FeeSetting::getFeePeriodOptions())),
            'fee_lines.*.amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Custom validation for date logic
        if ($request->date_from && $request->date_to) {
            $request->validate([
                'date_to' => 'after_or_equal:date_from',
            ], [
                'date_to.after_or_equal' => 'The end date must be after or equal to the start date.',
            ]);
        }

        $feeSetting->update([
            'program_id' => $request->program_id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        // Delete existing fee setting items and create new ones
        $feeSetting->collegeFeeSettingItems()->delete();

        if ($request->has('fee_lines')) {
            foreach ($request->fee_lines as $lineData) {
                $feeSetting->collegeFeeSettingItems()->create([
                    'fee_group_id' => $lineData['fee_group_id'],
                    'fee_period' => $lineData['fee_period'],
                    'amount' => $lineData['amount'],
                ]);
            }
        }

        return redirect()->route('college.fee-settings.index')
            ->with('success', 'College Fee Setting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FeeSetting $feeSetting)
    {
        // Skip the not found check

        $feeSetting->delete();

        return redirect()->route('college.fee-settings.index')
            ->with('success', 'College Fee Setting deleted successfully.');
    }
}
