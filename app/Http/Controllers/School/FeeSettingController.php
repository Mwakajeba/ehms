<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\FeeSetting;
use App\Models\FeeSettingItem;
use App\Models\School\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class FeeSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('school.fee-settings.index');
    }

    /**
     * Get fee settings data for DataTables.
     */
    public function data(Request $request)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = FeeSetting::with(['classe', 'feeSettingItems'])
            ->where('company_id', Auth::user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('class_name', function ($feeSetting) {
                return $feeSetting->classe->name ?? 'N/A';
            })
            ->addColumn('fee_period', function ($feeSetting) {
                return $feeSetting->getFeePeriodOptions()[$feeSetting->fee_period] ?? $feeSetting->fee_period;
            })
            ->addColumn('amount', function ($feeSetting) {
                $categoryAmounts = [];
                $feeSetting->feeSettingItems->groupBy('category')->each(function ($items, $category) use (&$categoryAmounts) {
                    $total = $items->sum('amount');
                    $categoryName = $category === 'day' ? 'Day' : 'Boarding';
                    $colorClass = $category === 'day' ? 'text-primary' : 'text-success';
                    $categoryAmounts[] = '<span class="' . $colorClass . '">' . $categoryName . ': ' . config('app.currency', 'TZS') . ' ' . number_format($total, 2) . '</span>';
                });
                return implode('<br>', $categoryAmounts);
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
                return '<a href="' . route('school.fee-settings.show', $hashid) . '" class="btn btn-info btn-sm" title="View">
                            <i class="bx bx-show"></i>
                        </a>
                        <a href="' . route('school.fee-settings.edit', $hashid) . '" class="btn btn-warning btn-sm" title="Edit">
                            <i class="bx bx-edit"></i>
                        </a>
                        <form action="' . route('school.fee-settings.destroy', $hashid) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this fee setting? This action cannot be undone.\')">
                            <input type="hidden" name="_token" value="' . csrf_token() . '">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                <i class="bx bx-trash"></i>
                            </button>
                        </form>';
            })
            ->rawColumns(['status_badge', 'actions', 'amount'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $feePeriodOptions = FeeSetting::getFeePeriodOptions();
        $categoryOptions = FeeSetting::getCategoryOptions();

        return view('school.fee-settings.create', compact('classes', 'feePeriodOptions', 'categoryOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'fee_period' => 'required|in:' . implode(',', array_keys(FeeSetting::getFeePeriodOptions())),
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
            'fee_lines' => 'required|array|min:1',
            'fee_lines.*.category' => 'required|in:' . implode(',', array_keys(FeeSetting::getCategoryOptions())),
            'fee_lines.*.amount' => 'required|numeric|min:0',
            'fee_lines.*.include_transport' => 'boolean',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Fee settings are now reusable across academic years
        // academic_year_id is optional - if not provided, fee settings can be used for all academic years
        $academicYearId = null;
        if ($request->has('academic_year_id') && $request->academic_year_id) {
            $academicYearId = $request->academic_year_id;
        } else {
            // Optionally set to current academic year for reference, but it's not required
            $academicYear = \App\Models\School\AcademicYear::where('company_id', Auth::user()->company_id)
                ->where('is_current', true)
                ->first();
            if ($academicYear) {
                $academicYearId = $academicYear->id;
            }
        }

        $feeSetting = FeeSetting::create([
            'class_id' => $request->class_id,
            'academic_year_id' => $academicYearId, // Now nullable - allows reuse across academic years
            'fee_period' => $request->fee_period,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'company_id' => Auth::user()->company_id,
            'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
            'created_by' => Auth::id(),
        ]);

        // Create fee setting items
        if ($request->has('fee_lines')) {
            foreach ($request->fee_lines as $lineData) {
                $feeSetting->feeSettingItems()->create([
                    'category' => $lineData['category'],
                    'amount' => $lineData['amount'],
                    'includes_transport' => isset($lineData['include_transport']) && $lineData['include_transport'] == '1',
                    'company_id' => Auth::user()->company_id,
                    'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
                ]);
            }
        }

        return redirect()->route('school.fee-settings.index')
            ->with('success', 'Fee Setting created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($fee_setting)
    {
        $feeSetting = FeeSetting::findByHashid($fee_setting);

        if (!$feeSetting) {
            return redirect()->route('school.fee-settings.index')
                ->with('error', 'Fee Setting not found.');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($feeSetting->branch_id !== $branchId && $feeSetting->branch_id !== null) {
            return redirect()->route('school.fee-settings.index')
                ->with('error', 'You do not have permission to view this fee setting.');
        }

        return view('school.fee-settings.show', compact('feeSetting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($fee_setting)
    {
        \Log::info('FeeSettingController edit called', [
            'fee_setting_param' => $fee_setting,
            'auth_check' => Auth::check(),
            'auth_user' => Auth::check() ? Auth::user()->id : 'not authenticated',
            'session_branch_id' => session('branch_id'),
        ]);
        
        $feeSetting = FeeSetting::findByHashid($fee_setting);

        if (!$feeSetting) {
            \Log::error('FeeSetting not found in edit method', [
                'fee_setting_param' => $fee_setting,
                'auth_user' => Auth::check() ? Auth::user()->id : 'not authenticated',
            ]);
            
            return redirect()->route('school.fee-settings.index')
                ->with('error', 'Fee Setting not found.');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($feeSetting->branch_id !== $branchId && $feeSetting->branch_id !== null) {
            return redirect()->route('school.fee-settings.index')
                ->with('error', 'You do not have permission to edit this fee setting.');
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $feePeriodOptions = FeeSetting::getFeePeriodOptions();
        $categoryOptions = FeeSetting::getCategoryOptions();

        return view('school.fee-settings.edit', compact('feeSetting', 'classes', 'feePeriodOptions', 'categoryOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $fee_setting)
    {
        $feeSetting = FeeSetting::findByHashid($fee_setting);

        if (!$feeSetting) {
            return redirect()->route('school.fee-settings.index')
                ->with('error', 'Fee Setting not found.');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($feeSetting->branch_id !== $branchId && $feeSetting->branch_id !== null) {
            return redirect()->route('school.fee-settings.index')
                ->with('error', 'You do not have permission to update this fee setting.');
        }

        \Log::info('FeeSetting update request data', [
            'request_data' => $request->all(),
            'fee_lines' => $request->fee_lines,
        ]);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'fee_period' => 'required|in:' . implode(',', array_keys(FeeSetting::getFeePeriodOptions())),
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
            'fee_lines' => 'required|array|min:1',
            'fee_lines.*.category' => 'required|in:' . implode(',', array_keys(FeeSetting::getCategoryOptions())),
            'fee_lines.*.amount' => 'required|numeric|min:0',
            'fee_lines.*.include_transport' => 'nullable|boolean',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $feeSetting->update([
            'class_id' => $request->class_id,
            'fee_period' => $request->fee_period,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        // Delete existing fee setting items and create new ones
        $feeSetting->feeSettingItems()->delete();

        if ($request->has('fee_lines')) {
            foreach ($request->fee_lines as $lineData) {
                \Log::info('Creating fee setting item', ['lineData' => $lineData]);
                $feeSetting->feeSettingItems()->create([
                    'category' => $lineData['category'],
                    'amount' => $lineData['amount'],
                    'includes_transport' => isset($lineData['include_transport']) && $lineData['include_transport'] == '1',
                    'company_id' => Auth::user()->company_id,
                    'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
                ]);
            }
        }

        return redirect()->route('school.fee-settings.index')
            ->with('success', 'Fee Setting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($fee_setting)
    {
        $feeSetting = FeeSetting::findByHashid($fee_setting);

        if (!$feeSetting) {
            return redirect()->route('school.fee-settings.index')
                ->with('error', 'Fee Setting not found.');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($feeSetting->branch_id !== $branchId && $feeSetting->branch_id !== null) {
            return redirect()->route('school.fee-settings.index')
                ->with('error', 'You do not have permission to delete this fee setting.');
        }

        $feeSetting->delete();

        return redirect()->route('school.fee-settings.index')
            ->with('success', 'Fee Setting deleted successfully.');
    }
}
