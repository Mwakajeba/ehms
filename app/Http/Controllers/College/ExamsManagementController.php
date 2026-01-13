<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExamsManagementController extends Controller
{
    public function dashboard()
    {
        return view('college.exams-management.dashboard');
    }

    public function index()
    {
        return view('college.exams-management.index');
    }

    public function create()
    {
        return view('college.exams-management.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement store logic
        return redirect()->route('college.exams-management.index');
    }

    public function show($id)
    {
        return view('college.exams-management.show', compact('id'));
    }

    public function edit($id)
    {
        return view('college.exams-management.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement update logic
        return redirect()->route('college.exams-management.index');
    }

    public function destroy($id)
    {
        // TODO: Implement destroy logic
        return redirect()->route('college.exams-management.index');
    }
}