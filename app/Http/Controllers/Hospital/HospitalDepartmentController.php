<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\HospitalDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class HospitalDepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?? Auth::user()->branch_id;

            $departments = HospitalDepartment::where('company_id', $companyId);
            
            if ($branchId) {
                $departments->where('branch_id', $branchId);
            } else {
                $departments->whereNull('branch_id');
            }

            return DataTables::of($departments)
                ->addIndexColumn()
                ->addColumn('type_display', function ($department) {
                    return ucfirst(str_replace('_', ' ', $department->type));
                })
                ->addColumn('status', function ($department) {
                    if ($department->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    }
                    return '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('description_display', function ($department) {
                    return $department->description ? \Str::limit($department->description, 50) : '-';
                })
                ->addColumn('action', function ($department) {
                    $editBtn = '<a href="' . route('hospital.admin.departments.edit', $department->id) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bx bx-edit"></i></a>';
                    $deleteBtn = '<button class="btn btn-sm btn-outline-danger delete-btn" data-id="' . $department->id . '" data-name="' . $department->name . '"><i class="bx bx-trash"></i></button>';
                    return $editBtn . $deleteBtn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('hospital.admin.departments.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hospital.admin.departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('hospital_departments')->where(function ($query) use ($companyId, $branchId) {
                    $query->where('company_id', $companyId);
                    if ($branchId) {
                        $query->where('branch_id', $branchId);
                    } else {
                        $query->whereNull('branch_id');
                    }
                })
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:hospital_departments,code'
            ],
            'type' => [
                'required',
                'in:reception,cashier,triage,doctor,lab,ultrasound,dental,pharmacy,rch,family_planning,vaccine,injection,observation'
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            HospitalDepartment::create([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]);

            return redirect()->route('hospital.admin.departments.index')
                ->with('success', 'Hospital department created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create department: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $department = HospitalDepartment::with(['company', 'branch', 'services', 'visitDepartments'])
            ->findOrFail($id);

        // Verify access
        if ($department->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to department.');
        }

        return view('hospital.admin.departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $department = HospitalDepartment::findOrFail($id);

        // Verify access
        if ($department->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to department.');
        }

        return view('hospital.admin.departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $department = HospitalDepartment::findOrFail($id);
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        // Verify access
        if ($department->company_id !== $companyId) {
            abort(403, 'Unauthorized access to department.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('hospital_departments')->where(function ($query) use ($companyId, $branchId) {
                    $query->where('company_id', $companyId);
                    if ($branchId) {
                        $query->where('branch_id', $branchId);
                    } else {
                        $query->whereNull('branch_id');
                    }
                })->ignore($department->id)
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('hospital_departments')->ignore($department->id)
            ],
            'type' => [
                'required',
                'in:reception,cashier,triage,doctor,lab,ultrasound,dental,pharmacy,rch,family_planning,vaccine,injection,observation'
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $department->update([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return redirect()->route('hospital.admin.departments.index')
                ->with('success', 'Hospital department updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update department: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $department = HospitalDepartment::findOrFail($id);

        // Verify access
        if ($department->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to department.');
        }

        // Check if department has associated visits
        $visitCount = $department->visitDepartments()->count();
        if ($visitCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department "' . $department->name . '" because it has ' . $visitCount . ' visit(s) associated with it.'
            ], 400);
        }

        // Check if department has services
        $serviceCount = $department->services()->count();
        if ($serviceCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department "' . $department->name . '" because it has ' . $serviceCount . ' service(s) associated with it. Please remove services first.'
            ], 400);
        }

        try {
            $department->delete();

            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete department: ' . $e->getMessage()
            ], 500);
        }
    }
}
