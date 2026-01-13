<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\Subject;
use App\Models\School\SubjectGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class SubjectsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('school.subjects.index');
    }

    /**
     * Get subjects data for DataTables.
     */
    public function data(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        \Log::info('SubjectsController@data called', [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'session_branch_id' => session('branch_id'),
            'user_branch_id' => Auth::user()->branch_id,
            'user_id' => Auth::id()
        ]);

        $query = Subject::with('subjectGroup')
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        $subjects = $query->get();
        \Log::info('Subjects query results', [
            'total_subjects' => $subjects->count(),
            'subjects' => $subjects->map(function($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'branch_id' => $s->branch_id
                ];
            })->toArray()
        ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('type_badge', function ($subject) {
                $badgeClass = $subject->subject_type === 'practical' ? 'warning' : 'info';
                return '<span class="badge bg-' . $badgeClass . '">' . ucfirst($subject->subject_type ?? 'theory') . '</span>';
            })
            ->addColumn('subject_group_name', function ($subject) {
                $groups = $subject->subjectGroups;
                if ($groups->count() === 0) {
                    return '<span class="text-muted">-</span>';
                } elseif ($groups->count() === 1) {
                    return '<span class="badge bg-primary">' . $groups->first()->name . '</span>';
                } else {
                    return '<span class="badge bg-info">' . $groups->count() . ' groups</span>';
                }
            })
            ->addColumn('status_badge', function ($subject) {
                return $subject->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('actions', function ($subject) {
                return view('school.subjects.partials.actions', compact('subject'))->render();
            })
            ->rawColumns(['type_badge', 'subject_group_name', 'status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('school.subjects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code',
            'short_name' => 'nullable|string|max:100',
            'subject_type' => 'nullable|in:theory,practical',
            'requirement_type' => 'nullable|in:compulsory,optional',
            'passing_marks' => 'nullable|numeric|min:0|max:100',
            'total_marks' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Subject::create([
            'name' => $request->name,
            'code' => $request->code,
            'short_name' => $request->short_name,
            'subject_type' => $request->subject_type ?? 'theory',
            'requirement_type' => $request->requirement_type ?? 'compulsory',
            'passing_marks' => $request->passing_marks ?? 40.00,
            'total_marks' => $request->total_marks ?? 100.00,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'company_id' => Auth::user()->company_id,
            'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('school.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($hashid)
    {
        $subject = Subject::findByHashid($hashid);

        if (!$subject) {
            return redirect()->route('school.subjects.index')
                ->with('error', 'Subject not found.');
        }

        // Check if subject belongs to user's branch
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId && $subject->branch_id !== $branchId) {
            return redirect()->route('school.subjects.index')
                ->with('error', 'You do not have permission to view this subject.');
        }

        return view('school.subjects.show', compact('subject'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($hashid)
    {
        $subject = Subject::findByHashid($hashid);

        if (!$subject) {
            return redirect()->route('school.subjects.index')
                ->with('error', 'Subject not found.');
        }

        // Check if subject belongs to user's branch
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId && $subject->branch_id !== $branchId) {
            return redirect()->route('school.subjects.index')
                ->with('error', 'You do not have permission to edit this subject.');
        }

        $companyId = Auth::user()->company_id;

        return view('school.subjects.edit', compact('subject'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $hashid)
    {
        $subject = Subject::findByHashid($hashid);

        if (!$subject) {
            return redirect()->route('school.subjects.index')
                ->with('error', 'Subject not found.');
        }

        // Check if subject belongs to user's branch
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId && $subject->branch_id !== $branchId) {
            return redirect()->route('school.subjects.index')
                ->with('error', 'You do not have permission to update this subject.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code,' . $subject->id,
            'short_name' => 'nullable|string|max:100',
            'subject_type' => 'nullable|in:theory,practical',
            'requirement_type' => 'nullable|in:compulsory,optional',
            'passing_marks' => 'nullable|numeric|min:0|max:100',
            'total_marks' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $subject->update([
            'name' => $request->name,
            'code' => $request->code,
            'short_name' => $request->short_name,
            'subject_type' => $request->subject_type ?? 'theory',
            'requirement_type' => $request->requirement_type ?? 'compulsory',
            'passing_marks' => $request->passing_marks ?? $subject->passing_marks,
            'total_marks' => $request->total_marks ?? $subject->total_marks,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('school.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($hashid)
    {
        $subject = Subject::findByHashid($hashid);

        if (!$subject) {
            return redirect()->route('school.subjects.index')
                ->with('error', 'Subject not found.');
        }

        // Check if subject belongs to user's branch
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId && $subject->branch_id !== $branchId) {
            return redirect()->route('school.subjects.index')
                ->with('error', 'You do not have permission to delete this subject.');
        }

        // Check if subject is assigned to any subject groups
        if ($subject->subjectGroups()->count() > 0) {
            return redirect()->route('school.subjects.index')
                ->with('error', 'Cannot delete subject "' . $subject->name . '" because it is assigned to ' . $subject->subjectGroups()->count() . ' subject group(s). Please remove it from all subject groups first.');
        }

        $subject->delete();

        return redirect()->route('school.subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}