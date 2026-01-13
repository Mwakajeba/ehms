<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index()
    {
        return view('college.academic-years.index');
    }

    public function create()
    {
        return view('college.academic-years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'year_name' => 'required|string|max:255|unique:academic_years,year_name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:upcoming,active,completed,cancelled',
            'description' => 'nullable|string',
            'set_as_current' => 'boolean'
        ]);

        $academicYear = \App\Models\School\AcademicYear::create([
            'year_name' => $request->year_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
            'description' => $request->description,
            'company_id' => auth()->user()->company_id,
            'branch_id' => session('branch_id'),
            'is_current' => false
        ]);

        // If this is set as current, unset others
        if ($request->set_as_current) {
            $academicYear->setAsCurrent();
        }

        return redirect()->route('college.academic-years.index')
            ->with('success', 'Academic year created successfully.');
    }

    public function show($id)
    {
        $academicYear = \App\Models\School\AcademicYear::with(['students', 'enrollments'])->findOrFail($id);

        // Check if academic year belongs to current branch
        if ($academicYear->branch_id !== session('branch_id')) {
            abort(403, 'You do not have permission to view this academic year.');
        }

        $stats = $academicYear->getStats();
        return view('college.academic-years.show', compact('academicYear', 'stats'));
    }

    public function edit($id)
    {
        $academicYear = \App\Models\School\AcademicYear::findOrFail($id);

        // Check if academic year belongs to current branch
        if ($academicYear->branch_id !== session('branch_id')) {
            abort(403, 'You do not have permission to edit this academic year.');
        }

        return view('college.academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, $id)
    {
        $academicYear = \App\Models\School\AcademicYear::findOrFail($id);

        // Check if academic year belongs to current branch
        if ($academicYear->branch_id !== session('branch_id')) {
            abort(403, 'You do not have permission to update this academic year.');
        }

        $request->validate([
            'year_name' => 'required|string|max:255|unique:academic_years,year_name,' . $id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:upcoming,active,completed,cancelled',
            'description' => 'nullable|string',
            'set_as_current' => 'boolean'
        ]);

        $academicYear->update([
            'year_name' => $request->year_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
            'description' => $request->description,
            'is_current' => false
        ]);

        // If this is set as current, unset others
        if ($request->set_as_current) {
            $academicYear->setAsCurrent();
        }

        return redirect()->route('college.academic-years.index')
            ->with('success', 'Academic year updated successfully.');
    }

    public function destroy($id)
    {
        $academicYear = \App\Models\School\AcademicYear::findOrFail($id);

        // Check if academic year belongs to current branch
        if ($academicYear->branch_id !== session('branch_id')) {
            abort(403, 'You do not have permission to delete this academic year.');
        }

        // Prevent deletion if academic year has students
        if ($academicYear->students()->count() > 0) {
            return redirect()->route('college.academic-years.index')
                ->with('error', 'Cannot delete academic year with enrolled students.');
        }

        $academicYear->delete();

        return redirect()->route('college.academic-years.index')
            ->with('success', 'Academic year deleted successfully.');
    }

    /**
     * Set an academic year as current
     */
    public function setCurrent(string $id)
    {
        $academicYear = \App\Models\School\AcademicYear::findOrFail($id);

        // Check if academic year belongs to current branch
        if ($academicYear->branch_id !== session('branch_id')) {
            abort(403, 'You do not have permission to modify this academic year.');
        }

        $academicYear->setAsCurrent();

        return redirect()->route('college.academic-years.index')
            ->with('success', 'Academic year set as current successfully.');
    }

    /**
     * Mark an academic year as completed
     */
    public function markCompleted(string $id)
    {
        $academicYear = \App\Models\School\AcademicYear::findOrFail($id);

        // Check if academic year belongs to current branch
        if ($academicYear->branch_id !== session('branch_id')) {
            abort(403, 'You do not have permission to modify this academic year.');
        }

        if (!$academicYear->isActive()) {
            return redirect()->route('college.academic-years.index')
                ->with('error', 'Only active academic years can be marked as completed.');
        }

        $academicYear->markAsCompleted();

        return redirect()->route('college.academic-years.index')
            ->with('success', 'Academic year marked as completed successfully.');
    }

    /**
     * Get data for DataTables
     */
    public function data()
    {
        $query = \App\Models\School\AcademicYear::with(['company', 'branch']);

        // Filter by company
        if (auth()->check()) {
            $query->forCompany(auth()->user()->company_id);
        }

        // Filter by branch if set in session
        if (session('branch_id')) {
            $query->forBranch(session('branch_id'));
        }

        // For debugging - temporarily remove filters if no results
        $count = $query->count();
        if ($count == 0) {
            // If no results with filters, try without filters
            $query = \App\Models\School\AcademicYear::with(['company', 'branch']);
        }

        $academicYears = $query->select([
            'id', 'year_name', 'start_date', 'end_date', 'is_current',
            'status', 'description', 'created_at'
        ]);

        return \Yajra\DataTables\DataTables::of($academicYears)
            ->addColumn('duration', function ($academicYear) {
                return $academicYear->formatted_duration;
            })
            ->addColumn('status_badge', function ($academicYear) {
                return $academicYear->getStatusBadge();
            })
            ->addColumn('current_badge', function ($academicYear) {
                return $academicYear->getCurrentBadge();
            })
            ->addColumn('progress', function ($academicYear) {
                if ($academicYear->isActive()) {
                    $progress = $academicYear->progress_percentage;
                    return '<div class="progress" style="width: 100px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: ' . $progress . '%" aria-valuenow="' . $progress . '"
                             aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>';
                }
                return '-';
            })
            ->addColumn('actions', function ($academicYear) {
                $actions = '<div class="btn-group" role="group">';

                // View button
                $actions .= '<a href="' . route('college.academic-years.show', $academicYear->id) . '"
                               class="btn btn-sm btn-outline-info" title="View Details">
                               <i class="bx bx-show"></i>
                            </a>';

                // Edit button (only if not completed/cancelled)
                if ($academicYear->canBeEdited()) {
                    $actions .= '<a href="' . route('college.academic-years.edit', $academicYear->id) . '"
                                   class="btn btn-sm btn-outline-warning" title="Edit">
                                   <i class="bx bx-edit"></i>
                                </a>';
                }

                // Set as current button (only if not current and active/upcoming)
                if (!$academicYear->is_current && in_array($academicYear->status, ['active', 'upcoming'])) {
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-primary"
                                   title="Set as Current" onclick="setAsCurrent(' . $academicYear->id . ')">
                                   <i class="bx bx-star"></i>
                                </button>';
                }

                // Mark as completed button (only if active)
                if ($academicYear->isActive()) {
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-secondary"
                                   title="Mark as Completed" onclick="markCompleted(' . $academicYear->id . ')">
                                   <i class="bx bx-check"></i>
                                </button>';
                }

                // Delete button (only if upcoming and not current)
                if ($academicYear->canBeDeleted()) {
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-danger"
                                   title="Delete" onclick="confirmDelete(' . $academicYear->id . ', \'' . addslashes($academicYear->year_name) . '\')">
                                   <i class="bx bx-trash"></i>
                                </button>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['status_badge', 'current_badge', 'progress', 'actions'])
            ->make(true);
    }

    /**
     * Generate next academic year suggestion
     */
    public function generateNext()
    {
        $currentYear = \App\Models\School\AcademicYear::current();

        if ($currentYear) {
            $nextStartYear = (int) explode('-', $currentYear->year_name)[1];
            $nextYearName = \App\Models\School\AcademicYear::generateYearName($nextStartYear);
            $nextStartDate = \Carbon\Carbon::createFromFormat('Y-m-d', ($nextStartYear) . '-09-01'); // September 1st
            $nextEndDate = \Carbon\Carbon::createFromFormat('Y-m-d', ($nextStartYear + 1) . '-08-31'); // August 31st
        } else {
            $nextYearName = \App\Models\School\AcademicYear::generateYearName();
            $nextStartDate = \Carbon\Carbon::createFromFormat('Y-m-d', date('Y') . '-09-01');
            $nextEndDate = \Carbon\Carbon::createFromFormat('Y-m-d', (date('Y') + 1) . '-08-31');
        }

        return response()->json([
            'year_name' => $nextYearName,
            'start_date' => $nextStartDate->format('Y-m-d'),
            'end_date' => $nextEndDate->format('Y-m-d')
        ]);
    }
}