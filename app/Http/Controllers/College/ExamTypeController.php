<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ExamTypeController extends Controller
{
    public function index()
    {
        return view('college.exam-types.index');
    }

    public function create()
    {
        return view('college.exam-types.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        ExamType::create([
            'name' => $request->name,
            'description' => $request->description,
            'weight' => $request->weight,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('college.exam-types.index')
            ->with('success', 'Exam Type created successfully.');
    }

    public function show(ExamType $examType)
    {
        return view('college.exam-types.show', compact('examType'));
    }

    public function edit(ExamType $examType)
    {
        return view('college.exam-types.edit', compact('examType'));
    }

    public function update(Request $request, ExamType $examType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $examType->update([
            'name' => $request->name,
            'description' => $request->description,
            'weight' => $request->weight,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('college.exam-types.index')
            ->with('success', 'Exam Type updated successfully.');
    }

    public function destroy(ExamType $examType)
    {
        $examType->delete();

        return redirect()->route('college.exam-types.index')
            ->with('success', 'Exam Type deleted successfully.');
    }

    public function data(Request $request)
    {
        $examTypes = ExamType::query();

        return DataTables::of($examTypes)
            ->addIndexColumn()
            ->addColumn('name', function ($examType) {
                return $examType->name;
            })
            ->addColumn('weight', function ($examType) {
                return $examType->weight . '%';
            })
            ->addColumn('status', function ($examType) {
                return $examType->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('actions', function ($examType) {
                return view('college.exam-types.partials.actions', compact('examType'))->render();
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }
}