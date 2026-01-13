<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('college.departments.index');
    }

    public function create()
    {
        // Get users filtered by company and branch for head of department selection
        $users = \App\Models\User::where('company_id', Auth::user()->company_id);

        if (session('branch_id')) {
            $users = $users->whereHas('branches', function ($query) {
                $query->where('branches.id', session('branch_id'));
            });
        }

        $users = $users->orderBy('name')->get();

        return view('college.departments.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:college_departments,code',
            'description' => 'nullable|string',
            'head_of_department_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean'
        ]);

        try {
            Department::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'head_of_department_id' => $request->head_of_department_id,
                'company_id' => Auth::user()->company_id,
                'branch_id' => session('branch_id'),
                'is_active' => $request->boolean('is_active', true)
            ]);

            return redirect()->route('college.departments.index')
                ->with('success', 'Department created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create department: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $department = Department::with(['headOfDepartment', 'programs'])->findOrFail($id);

        // Check if user has access to this department's branch
        if (session('branch_id') && $department->branch_id !== session('branch_id')) {
            abort(403, 'You do not have access to this department.');
        }

        return view('college.departments.show', compact('department'));
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);

        // Check if user has access to this department's branch
        if (session('branch_id') && $department->branch_id !== session('branch_id')) {
            abort(403, 'You do not have access to this department.');
        }

        // Get users filtered by company and branch for head of department selection
        $users = \App\Models\User::where('company_id', Auth::user()->company_id);

        if (session('branch_id')) {
            $users = $users->whereHas('branches', function ($query) {
                $query->where('branches.id', session('branch_id'));
            });
        }

        $users = $users->orderBy('name')->get();

        return view('college.departments.edit', compact('department', 'users'));
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        // Check if user has access to this department's branch
        if (session('branch_id') && $department->branch_id !== session('branch_id')) {
            abort(403, 'You do not have access to this department.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:college_departments,code,' . $id,
            'description' => 'nullable|string',
            'head_of_department_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean'
        ]);

        try {
            $department->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'head_of_department_id' => $request->head_of_department_id,
                'is_active' => $request->boolean('is_active', true)
            ]);

            return redirect()->route('college.departments.index')
                ->with('success', 'Department updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update department: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        // Check if user has access to this department's branch
        if (session('branch_id') && $department->branch_id !== session('branch_id')) {
            abort(403, 'You do not have access to this department.');
        }

        // Check if department has programs
        if ($department->programs()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete department with existing programs.');
        }

        try {
            $department->delete();
            return redirect()->route('college.departments.index')
                ->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete department: ' . $e->getMessage());
        }
    }

    public function data()
    {
        $query = Department::with(['headOfDepartment']);

        // Only filter by company if user is authenticated
        if (Auth::check()) {
            $query->forCompany(Auth::user()->company_id);
        }

        // Only filter by branch if branch_id is set in session
        if (session('branch_id')) {
            $query->forBranch(session('branch_id'));
        }

        $departments = $query->select(['id', 'name', 'code', 'description', 'head_of_department_id', 'is_active', 'created_at']);

        return DataTables::of($departments)
            ->addColumn('head_of_department', function ($department) {
                return $department->headOfDepartment ? $department->headOfDepartment->name : 'Not Assigned';
            })
            ->addColumn('actions', function ($department) {
                $id = $department->id;
                $name = $department->name;
                return '<a href="' . route('college.departments.show', $id) . '" class="btn btn-sm btn-outline-info" title="View Details">
                    <i class="bx bx-show"></i>
                </a>
                <a href="' . route('college.departments.edit', $id) . '" class="btn btn-sm btn-outline-warning" title="Edit">
                    <i class="bx bx-edit"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                        onclick="confirmDelete(' . $id . ', \'' . addslashes($name) . '\')">
                    <i class="bx bx-trash"></i>
                </button>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}