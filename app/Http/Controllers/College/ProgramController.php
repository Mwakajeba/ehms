<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\Program;
use App\Models\College\Department;
use App\Models\College\ProgramDetail;
use App\Models\College\AcademicYear;
use App\Models\HR\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ProgramController extends Controller
{
    public function index()
    {
        return view('college.programs.index');
    }

    public function create()
    {
        $departments = Department::active()->forCompany(Auth::user()->company_id)->get();
        return view('college.programs.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:college_programs,code',
            'description' => 'nullable|string',
            'objectives' => 'nullable|string',
            'requirements' => 'nullable|string',
            'department_id' => 'required|exists:college_departments,id',
            'duration_years' => 'required|integer|min:1|max:10',
            'level' => 'required|in:certificate,diploma,bachelor,master,phd',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            Program::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'objectives' => $request->objectives,
                'requirements' => $request->requirements,
                'department_id' => $request->department_id,
                'duration_years' => $request->duration_years,
                'level' => $request->level,
                'is_active' => $request->boolean('is_active', true),
                'company_id' => Auth::user()->company_id,
                'branch_id' => session('branch_id')
            ]);

            DB::commit();

            return redirect()->route('college.programs.index')
                ->with('success', 'Program created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create program: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $program = Program::with(['department', 'students'])->findOrFail($id);
        
        // Get instructor assignment history
        $instructorHistory = ProgramDetail::with(['employee', 'assignedByUser'])
            ->where('program_id', $program->id)
            ->orderBy('date_assigned', 'desc')
            ->get();
        
        // Get all current active instructors
        $activeInstructors = ProgramDetail::with(['employee', 'assignedByUser'])
            ->where('program_id', $program->id)
            ->where('status', 'active')
            ->orderBy('date_assigned', 'desc')
            ->get();
        
        // Get employees for the modal dropdown
        $employees = Employee::where('status', 'active')
            ->orderBy('first_name')
            ->get();
        
        // Get academic years for the modal dropdown
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        return view('college.programs.show', compact('program', 'instructorHistory', 'activeInstructors', 'employees', 'academicYears'));
    }

    public function edit($id)
    {
        $program = Program::findOrFail($id);
        $departments = Department::active()->forCompany(Auth::user()->company_id)->get();
        return view('college.programs.edit', compact('program', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $program = Program::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:college_programs,code,' . $id,
            'description' => 'nullable|string',
            'objectives' => 'nullable|string',
            'requirements' => 'nullable|string',
            'department_id' => 'required|exists:college_departments,id',
            'duration_years' => 'required|integer|min:1|max:10',
            'level' => 'required|in:certificate,diploma,bachelor,master,phd',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $program->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'objectives' => $request->objectives,
                'requirements' => $request->requirements,
                'department_id' => $request->department_id,
                'duration_years' => $request->duration_years,
                'level' => $request->level,
                'is_active' => $request->boolean('is_active', true)
            ]);

            DB::commit();

            return redirect()->route('college.programs.index')
                ->with('success', 'Program updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update program: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $program = Program::findOrFail($id);

        // Check if program has students
        if ($program->students()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete program with existing students.');
        }

        try {
            $program->delete();
            return redirect()->route('college.programs.index')
                ->with('success', 'Program deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete program: ' . $e->getMessage());
        }
    }

    public function data()
    {
        $query = Program::with(['department']);

        // Only filter by company if user is authenticated
        if (Auth::check()) {
            $query->forCompany(Auth::user()->company_id);
        }

        // Only filter by branch if branch_id is set in session
        if (session('branch_id')) {
            $query->forBranch(session('branch_id'));
        }

        $programs = $query->select(['id', 'name', 'code', 'description', 'department_id', 'duration_years', 'is_active', 'created_at']);

        return DataTables::of($programs)
            ->addColumn('department', function ($program) {
                return $program->department ? $program->department->name : 'Not Assigned';
            })
            ->addColumn('students_count', function ($program) {
                return $program->students()->count();
            })
            ->addColumn('actions', function ($program) {
                $id = $program->id;
                $name = addslashes($program->name);
                return '<div class="btn-group" role="group">
                    <a href="' . route('college.programs.show', $id) . '" class="btn btn-sm btn-outline-info" title="View Details">
                        <i class="bx bx-show"></i>
                    </a>
                    <a href="' . route('college.programs.edit', $id) . '" class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="bx bx-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                            onclick="confirmDelete(' . $id . ', \'' . $name . '\')">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Assign an instructor to a program
     */
    public function assignInstructor(Request $request, $id)
    {
        $program = Program::findOrFail($id);

        $request->validate([
            'employee_id' => 'required|exists:hr_employees,id',
            'academic_year' => 'required|string',
            'semester' => 'required|string|in:Semester 1,Semester 2',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            ProgramDetail::updateOrCreate(
                [
                    'program_id' => $program->id,
                    'employee_id' => $request->employee_id,
                    'academic_year' => $request->academic_year,
                    'semester' => $request->semester,
                ],
                [
                    'date_assigned' => now(),
                    'status' => 'active',
                    'assigned_by' => Auth::id(),
                    'notes' => $request->notes,
                ]
            );

            return redirect()->route('college.programs.show', $program->id)
                ->with('success', 'Instructor assigned to program successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to assign instructor: ' . $e->getMessage());
        }
    }

    /**
     * Remove (archive) an instructor from a program
     */
    public function removeInstructor($programId, $programDetailId)
    {
        $program = Program::findOrFail($programId);
        $programDetail = ProgramDetail::where('program_id', $program->id)
            ->findOrFail($programDetailId);

        try {
            $programDetail->update(['status' => 'archived']);

            return redirect()->route('college.programs.show', $program->id)
                ->with('success', 'Instructor assignment archived successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to archive instructor: ' . $e->getMessage());
        }
    }
}