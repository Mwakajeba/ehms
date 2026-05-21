<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use App\Models\Hospital\HospitalInsuranceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class HospitalInsuranceTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $companyId = Auth::user()->company_id;

            $types = HospitalInsuranceType::forCompany($companyId)
                ->with('receivableChartAccount')
                ->withCount('patients')
                ->orderBy('sort_order')
                ->orderBy('name');

            return DataTables::of($types)
                ->addIndexColumn()
                ->addColumn('type_label', fn ($type) => e($type->name))
                ->editColumn('code', fn ($type) => $type->code ? e($type->code) : '—')
                ->addColumn('none_flag', function ($type) {
                    return $type->is_none
                        ? '<span class="badge bg-secondary">No insurance</span>'
                        : '<span class="text-muted">—</span>';
                })
                ->addColumn('status', function ($type) {
                    return $type->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('receivable_account', function ($type) {
                    if ($type->is_none) {
                        return '<span class="text-muted">—</span>';
                    }
                    $account = $type->receivableChartAccount;
                    if (!$account) {
                        return '<span class="text-warning">Not set</span>';
                    }

                    return e($account->account_code . ' - ' . $account->account_name);
                })
                ->addColumn('patients_count_display', fn ($type) => (string) $type->patients_count)
                ->addColumn('action', function ($type) {
                    $edit = '<a href="' . route('settings.insurance-types.edit', $type->id) . '" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="bx bx-edit"></i></a>';
                    $delete = '<button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="' . $type->id . '" data-name="' . e($type->name) . '" title="Delete"><i class="bx bx-trash"></i></button>';
                    return $edit . $delete;
                })
                ->rawColumns(['none_flag', 'status', 'receivable_account', 'action'])
                ->make(true);
        }

        return view('settings.insurance-types.index');
    }

    public function create()
    {
        return view('settings.insurance-types.create', [
            'chartAccounts' => ChartAccount::orderBy('account_code')->orderBy('account_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate($this->validationRules($companyId, null, $request));

        if ($request->boolean('is_none')) {
            $existingNone = HospitalInsuranceType::forCompany($companyId)->where('is_none', true)->exists();
            if ($existingNone) {
                return back()->withInput()->withErrors([
                    'is_none' => 'Only one "No insurance" option is allowed per company. Edit the existing one instead.',
                ]);
            }
        }

        HospitalInsuranceType::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'receivable_chart_account_id' => $request->boolean('is_none') ? null : ($validated['receivable_chart_account_id'] ?? null),
            'is_none' => $request->boolean('is_none'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('settings.insurance-types.index')
            ->with('success', 'Insurance type created successfully.');
    }

    public function edit(HospitalInsuranceType $insurance_type)
    {
        $this->authorizeCompany($insurance_type);

        return view('settings.insurance-types.edit', [
            'insuranceType' => $insurance_type,
            'chartAccounts' => ChartAccount::orderBy('account_code')->orderBy('account_name')->get(),
        ]);
    }

    public function update(Request $request, HospitalInsuranceType $insurance_type)
    {
        $this->authorizeCompany($insurance_type);
        $insuranceType = $insurance_type;
        $companyId = Auth::user()->company_id;

        $validated = $request->validate($this->validationRules($companyId, $insuranceType->id, $request));

        if ($request->boolean('is_none') && !$insuranceType->is_none) {
            $existingNone = HospitalInsuranceType::forCompany($companyId)
                ->where('is_none', true)
                ->where('id', '!=', $insuranceType->id)
                ->exists();
            if ($existingNone) {
                return back()->withInput()->withErrors([
                    'is_none' => 'Only one "No insurance" option is allowed per company.',
                ]);
            }
        }

        $isNone = $request->boolean('is_none') || $insuranceType->is_none;

        $insuranceType->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'receivable_chart_account_id' => $isNone ? null : ($validated['receivable_chart_account_id'] ?? null),
            'is_none' => $request->boolean('is_none') || $insuranceType->is_none,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $insuranceType->patients()->update(['insurance_type' => $insuranceType->name]);

        return redirect()->route('settings.insurance-types.index')
            ->with('success', 'Insurance type updated successfully.');
    }

    public function destroy(HospitalInsuranceType $insurance_type)
    {
        $this->authorizeCompany($insurance_type);
        $insuranceType = $insurance_type;

        if ($insuranceType->patients()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete: patients are linked to this insurance type.',
            ], 422);
        }

        if ($insuranceType->is_none) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the default "No insurance" option.',
            ], 422);
        }

        $insuranceType->delete();

        return response()->json(['success' => true, 'message' => 'Insurance type deleted successfully.']);
    }

    protected function authorizeCompany(HospitalInsuranceType $insuranceType): void
    {
        if ($insuranceType->company_id !== Auth::user()->company_id) {
            abort(403);
        }
    }

    protected function validationRules(int $companyId, ?int $ignoreId, Request $request): array
    {
        $isNone = $request->boolean('is_none');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('hospital_insurance_types', 'name')
                    ->where('company_id', $companyId)
                    ->ignore($ignoreId),
            ],
            'code' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'receivable_chart_account_id' => [
                $isNone ? 'nullable' : 'required',
                'exists:chart_accounts,id',
            ],
            'is_none' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
