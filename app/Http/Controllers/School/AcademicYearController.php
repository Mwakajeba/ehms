<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $academicYears = AcademicYear::where('company_id', auth()->user()->company_id)
            ->where(function ($query) {
                $query->where('branch_id', auth()->user()->branch_id)
                      ->orWhereNull('branch_id');
            })
            ->withCount('students')
            ->orderBy('start_date', 'desc')
            ->paginate(10);
        return view('school.academic-years.index', compact('academicYears'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('school.academic-years.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'year_name' => 'required|string|max:255|unique:academic_years,year_name,NULL,id,company_id,' . auth()->user()->company_id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'nullable|boolean'
        ]);

        $academicYear = AcademicYear::create([
            'year_name' => $request->year_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_current' => $request->has('is_current') ? (bool)$request->is_current : false,
            'company_id' => auth()->user()->company_id,
            'branch_id' => auth()->user()->branch_id,
        ]);

        // If this is set as current, unset others
        if ($request->is_current) {
            $academicYear->setAsCurrent();
        }

        return redirect()->route('school.academic-years.index')
            ->with('success', 'Academic year created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $academicYear = AcademicYear::where('company_id', auth()->user()->company_id)
            ->where(function ($query) {
                $query->where('branch_id', auth()->user()->branch_id)
                      ->orWhereNull('branch_id');
            })
            ->with(['students', 'enrollments'])
            ->findOrFail($id);
        return view('school.academic-years.show', compact('academicYear'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $academicYear = AcademicYear::where('company_id', auth()->user()->company_id)
            ->where(function ($query) {
                $query->where('branch_id', auth()->user()->branch_id)
                      ->orWhereNull('branch_id');
            })
            ->findOrFail($id);
        return view('school.academic-years.edit', compact('academicYear'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $academicYear = AcademicYear::findOrFail($id);

        $request->validate([
            'year_name' => 'required|string|max:255|unique:academic_years,year_name,' . $id . ',id,company_id,' . auth()->user()->company_id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'nullable|boolean'
        ]);

        $academicYear->update([
            'year_name' => $request->year_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_current' => $request->has('is_current') ? (bool)$request->is_current : false,
            'company_id' => auth()->user()->company_id,
            'branch_id' => auth()->user()->branch_id,
        ]);

        // If this is set as current, unset others
        if ($request->is_current) {
            $academicYear->setAsCurrent();
        }

        return redirect()->route('school.academic-years.index')
            ->with('success', 'Academic year updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $academicYear = AcademicYear::where('company_id', auth()->user()->company_id)
            ->where(function ($query) {
                $query->where('branch_id', auth()->user()->branch_id)
                      ->orWhereNull('branch_id');
            })
            ->findOrFail($id);

        // Prevent deletion if academic year has students
        if ($academicYear->students()->count() > 0) {
            return redirect()->route('school.academic-years.index')
                ->with('error', 'Cannot delete academic year with enrolled students.');
        }

        $academicYear->delete();

        return redirect()->route('school.academic-years.index')
            ->with('success', 'Academic year deleted successfully.');
    }

    /**
     * Set an academic year as current
     */
    public function setCurrent(string $id)
    {
        $academicYear = AcademicYear::where('company_id', auth()->user()->company_id)
            ->where(function ($query) {
                $query->where('branch_id', auth()->user()->branch_id)
                      ->orWhereNull('branch_id');
            })
            ->findOrFail($id);
        $academicYear->setAsCurrent();

        return redirect()->route('school.academic-years.index')
            ->with('success', 'Academic year set as current successfully.');
    }
}
