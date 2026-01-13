<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\SubjectGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class SubjectGroupsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('school.subject-groups.index');
    }

    /**
     * Get subject groups data for DataTables.
     */
    public function data(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = SubjectGroup::with('subjects', 'classe')
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('subjects_count', function ($group) {
                return $group->subjects()->count();
            })
            ->addColumn('classe_name', function ($group) {
                return $group->classe ? $group->classe->name : 'N/A';
            })
            ->addColumn('status_badge', function ($group) {
                return $group->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('actions', function ($group) {
                return view('school.subject-groups.partials.actions', compact('group'))->render();
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $classes = \App\Models\School\Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $subjects = \App\Models\School\Subject::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('school.subject-groups.create', compact('classes', 'subjects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subject_groups,code',
            'class_id' => 'required|exists:classes,id',
            'description' => 'nullable|string',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
            'is_active' => 'boolean',
        ]);

        // Custom validation for sort orders - only required for selected subjects
        if ($request->has('subject_ids') && is_array($request->subject_ids)) {
            $validator = Validator::make($request->all(), [
                'sort_orders' => 'nullable|array',
            ]);

            $validator->sometimes('sort_orders.*', 'required|integer|min:1', function ($input) use ($request) {
                return in_array(key($input), $request->subject_ids ?? []);
            });

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $subjectGroup = SubjectGroup::create([
            'name' => $request->name,
            'code' => $request->code,
            'class_id' => $request->class_id,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'company_id' => Auth::user()->company_id,
            'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
            'created_by' => Auth::id(),
        ]);

        // Assign selected subjects to this group with sort orders
        if ($request->has('subject_ids') && is_array($request->subject_ids)) {
            $subjectData = [];
            foreach ($request->subject_ids as $subjectId) {
                $sortOrder = $request->input("sort_orders.{$subjectId}", 1);
                $subjectData[$subjectId] = ['sort_order' => $sortOrder];
            }
            $subjectGroup->subjects()->sync($subjectData);
        }

        return redirect()->route('school.subject-groups.index')
            ->with('success', 'Subject Group created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($hashid)
    {
        $subjectGroup = SubjectGroup::findByHashid($hashid, ['subjects', 'classe']);

        if (!$subjectGroup) {
            return redirect()->route('school.subject-groups.index')
                ->with('error', 'Subject Group not found.');
        }

        // Check if subject group belongs to user's branch
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId && $subjectGroup->branch_id !== $branchId) {
            return redirect()->route('school.subject-groups.index')
                ->with('error', 'You do not have permission to view this subject group.');
        }

        return view('school.subject-groups.show', compact('subjectGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($hashid)
    {
        $subjectGroup = SubjectGroup::findByHashid($hashid, ['subjects', 'classe']);

        if (!$subjectGroup) {
            return redirect()->route('school.subject-groups.index')
                ->with('error', 'Subject Group not found.');
        }

        // Check if subject group belongs to user's branch
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId && $subjectGroup->branch_id !== $branchId) {
            return redirect()->route('school.subject-groups.index')
                ->with('error', 'You do not have permission to edit this subject group.');
        }

        $companyId = Auth::user()->company_id;

        $classes = \App\Models\School\Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $subjects = \App\Models\School\Subject::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('school.subject-groups.edit', compact('subjectGroup', 'classes', 'subjects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $hashid)
    {
        $subjectGroup = SubjectGroup::findByHashid($hashid);

        if (!$subjectGroup) {
            return redirect()->route('school.subject-groups.index')
                ->with('error', 'Subject Group not found.');
        }

        // Check if subject group belongs to user's branch
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId && $subjectGroup->branch_id !== $branchId) {
            return redirect()->route('school.subject-groups.index')
                ->with('error', 'You do not have permission to update this subject group.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subject_groups,code,' . $subjectGroup->id,
            'class_id' => 'required|exists:classes,id',
            'description' => 'nullable|string',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
            'is_active' => 'boolean',
        ]);

        // Custom validation for sort orders - only required for selected subjects
        if ($request->has('subject_ids') && is_array($request->subject_ids)) {
            $validator = Validator::make($request->all(), [
                'sort_orders' => 'nullable|array',
            ]);

            $validator->sometimes('sort_orders.*', 'required|integer|min:1', function ($input) use ($request) {
                return in_array(key($input), $request->subject_ids ?? []);
            });

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $subjectGroup->update([
            'name' => $request->name,
            'code' => $request->code,
            'class_id' => $request->class_id,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        // Update subject assignments with sort orders
        $selectedSubjectIds = $request->subject_ids ?? [];
        $subjectData = [];
        foreach ($selectedSubjectIds as $subjectId) {
            $sortOrder = $request->input("sort_orders.{$subjectId}", 1);
            $subjectData[$subjectId] = ['sort_order' => $sortOrder];
        }
        $subjectGroup->subjects()->sync($subjectData);

        return redirect()->route('school.subject-groups.index')
            ->with('success', 'Subject Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($hashid)
    {
        $subjectGroup = SubjectGroup::findByHashid($hashid);

        if (!$subjectGroup) {
            return redirect()->route('school.subject-groups.index')
                ->with('error', 'Subject Group not found.');
        }

        // Check if subject group belongs to user's branch
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId && $subjectGroup->branch_id !== $branchId) {
            return redirect()->route('school.subject-groups.index')
                ->with('error', 'You do not have permission to delete this subject group.');
        }

        // Unassign all subjects from this group before deleting
        $subjectGroup->subjects()->detach();

        $subjectGroup->delete();

        return redirect()->route('school.subject-groups.index')
            ->with('success', 'Subject Group deleted successfully.');
    }
}