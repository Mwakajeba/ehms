<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceManagementController extends Controller
{
    /**
     * Display the attendance management dashboard.
     */
    public function index()
    {
        return view('school.attendance-management.index');
    }
}