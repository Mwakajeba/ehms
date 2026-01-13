<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\Semester;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SemesterController extends Controller
{
    /**
     * Display a listing of semesters.
     */
    public function index()
    {
        return view('college.semesters.index');
    }

    /**
     * Get semesters data for DataTables.
     */
    public function data(Request $request)
    {
        $semesters = Semester::query()->orderBy('number');

        return DataTables::of($semesters)
            ->addColumn('status_badge', function ($semester) {
                $colors = [
                    'active' => 'success',
                    'inactive' => 'secondary',
                ];
                $color = $colors[$semester->status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($semester->status) . '</span>';
            })
            ->addColumn('actions', function ($semester) {
                $actions = '<div class="d-flex gap-2">';
                $actions .= '<a href="' . route('college.semesters.show', $semester->id) . '" class="btn btn-sm btn-info" title="View"><i class="bx bx-show"></i></a>';
                $actions .= '<a href="' . route('college.semesters.edit', $semester->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="bx bx-edit"></i></a>';
                $actions .= '<button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(' . $semester->id . ', \'' . addslashes($semester->name) . '\')" title="Delete"><i class="bx bx-trash"></i></button>';
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new semester.
     */
    public function create()
    {
        // Get the next semester number (max + 1)
        $nextNumber = Semester::max('number') + 1;
        if ($nextNumber < 1) {
            $nextNumber = 1;
        }
        
        return view('college.semesters.create', compact('nextNumber'));
    }

    /**
     * Store a newly created semester.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:college_semesters,name',
            'number' => 'required|integer|min:1|unique:college_semesters,number',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        Semester::create([
            'name' => $request->name,
            'number' => $request->number,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()->route('college.semesters.index')
            ->with('success', 'Semester created successfully.');
    }

    /**
     * Display the specified semester.
     */
    public function show($id)
    {
        $semester = Semester::findOrFail($id);
        return view('college.semesters.show', compact('semester'));
    }

    /**
     * Show the form for editing the specified semester.
     */
    public function edit($id)
    {
        $semester = Semester::findOrFail($id);
        return view('college.semesters.edit', compact('semester'));
    }

    /**
     * Update the specified semester.
     */
    public function update(Request $request, $id)
    {
        $semester = Semester::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:college_semesters,name,' . $id,
            'number' => 'required|integer|min:1|unique:college_semesters,number,' . $id,
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        $semester->update([
            'name' => $request->name,
            'number' => $request->number,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()->route('college.semesters.index')
            ->with('success', 'Semester updated successfully.');
    }

    /**
     * Remove the specified semester.
     */
    public function destroy($id)
    {
        $semester = Semester::findOrFail($id);
        
        // Check if semester is being used (you may want to add more checks here)
        // For now, we'll allow deletion
        
        $semester->delete();

        return redirect()->route('college.semesters.index')
            ->with('success', 'Semester deleted successfully.');
    }

    /**
     * Toggle semester status.
     */
    public function toggleStatus($id)
    {
        $semester = Semester::findOrFail($id);
        $semester->status = $semester->status === 'active' ? 'inactive' : 'active';
        $semester->save();

        return redirect()->route('college.semesters.index')
            ->with('success', 'Semester status updated successfully.');
    }
}
