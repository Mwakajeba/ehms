<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\CollegeStudent;
use App\Models\CollegeProgram;
use Illuminate\Http\Request;

class AcademicReportController extends Controller
{
    public function index()
    {
        return view('college.academic-reports.index');
    }

    public function studentReport(CollegeStudent $student)
    {
        return view('college.academic-reports.student', compact('student'));
    }

    public function programReport(CollegeProgram $program)
    {
        return view('college.academic-reports.program', compact('program'));
    }
}