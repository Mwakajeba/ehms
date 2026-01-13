<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\CollegeAcademicYear;
use App\Models\CollegeProgram;
use App\Models\Course;
use App\Models\ExamType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ExamController extends Controller
{
    public function index()
    {
        return view('college.exams.index');
    }

    public function create()
    {
        $academicYears = CollegeAcademicYear::orderBy('name')->get();
        $programs = CollegeProgram::orderBy('name')->get();
        $courses = Course::orderBy('name')->get();
        $examTypes = ExamType::where('is_active', true)->orderBy('name')->get();

        return view('college.exams.create', compact('academicYears', 'programs', 'courses', 'examTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'academic_year_id' => 'required|exists:college_academic_years,id',
            'program_id' => 'required|exists:college_programs,id',
            'course_id' => 'required|exists:courses,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'exam_date' => 'required|date',
            'max_marks' => 'required|numeric|min:0',
            'pass_marks' => 'required|numeric|min:0|max:' . $request->max_marks,
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Exam::create($request->all());

        return redirect()->route('college.exams.index')
            ->with('success', 'Exam created successfully.');
    }

    public function show(Exam $exam)
    {
        return view('college.exams.show', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        $academicYears = CollegeAcademicYear::orderBy('name')->get();
        $programs = CollegeProgram::orderBy('name')->get();
        $courses = Course::orderBy('name')->get();
        $examTypes = ExamType::where('is_active', true)->orderBy('name')->get();

        return view('college.exams.edit', compact('exam', 'academicYears', 'programs', 'courses', 'examTypes'));
    }

    public function update(Request $request, Exam $exam)
    {
        $validator = Validator::make($request->all(), [
            'academic_year_id' => 'required|exists:college_academic_years,id',
            'program_id' => 'required|exists:college_programs,id',
            'course_id' => 'required|exists:courses,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'exam_date' => 'required|date',
            'max_marks' => 'required|numeric|min:0',
            'pass_marks' => 'required|numeric|min:0|max:' . $request->max_marks,
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $exam->update($request->all());

        return redirect()->route('college.exams.index')
            ->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return redirect()->route('college.exams.index')
            ->with('success', 'Exam deleted successfully.');
    }

    public function data(Request $request)
    {
        $exams = Exam::with(['academicYear', 'program', 'course', 'examType']);

        return DataTables::of($exams)
            ->addIndexColumn()
            ->addColumn('academic_year', function ($exam) {
                return $exam->academicYear ? $exam->academicYear->name : 'N/A';
            })
            ->addColumn('program', function ($exam) {
                return $exam->program ? $exam->program->name : 'N/A';
            })
            ->addColumn('course', function ($exam) {
                return $exam->course ? $exam->course->name : 'N/A';
            })
            ->addColumn('exam_type', function ($exam) {
                return $exam->examType ? $exam->examType->name : 'N/A';
            })
            ->addColumn('exam_date', function ($exam) {
                return $exam->exam_date ? $exam->exam_date->format('M d, Y') : 'N/A';
            })
            ->addColumn('marks', function ($exam) {
                return 'Max: ' . $exam->max_marks . ' | Pass: ' . $exam->pass_marks;
            })
            ->addColumn('actions', function ($exam) {
                return view('college.exams.partials.actions', compact('exam'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}