<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\LibraryMaterial;
use App\Models\School\AcademicYear;
use App\Models\School\Classe;
use App\Models\School\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class LibraryController extends Controller
{
    /**
     * Display a listing of library materials.
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('year_name', 'desc')
            ->get();

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $subjects = Subject::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('school.library.index', compact('academicYears', 'classes', 'subjects'));
    }

    /**
     * Get library materials data for DataTables.
     */
    public function data(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = LibraryMaterial::with(['academicYear', 'classe', 'subject'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        // Apply filters
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('class_id') && $request->class_id) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('subject_id') && $request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('type_badge', function ($material) {
                $colors = [
                    'pdf_book' => 'danger',
                    'notes' => 'info',
                    'past_paper' => 'success',
                    'assignment' => 'warning',
                ];
                $color = $colors[$material->type] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . $material->type_label . '</span>';
            })
            ->addColumn('status_badge', function ($material) {
                $colors = [
                    'draft' => 'secondary',
                    'published' => 'success',
                    'archived' => 'dark',
                ];
                $color = $colors[$material->status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($material->status) . '</span>';
            })
            ->addColumn('class_name', function ($material) {
                return $material->classe ? $material->classe->name : 'All Classes';
            })
            ->addColumn('subject_name', function ($material) {
                return $material->subject ? $material->subject->name : 'All Subjects';
            })
            ->addColumn('file_info', function ($material) {
                return '<small>' . $material->formatted_file_size . '</small>';
            })
            ->addColumn('actions', function ($material) {
                $actions = '';

                // View/Download button
                $actions .= '<a href="' . $material->url . '" target="_blank" class="btn btn-sm btn-info" title="View/Download">';
                $actions .= '<i class="bx bx-download"></i>';
                $actions .= '</a> ';

                // Edit button
                $actions .= '<a href="' . route('school.library.edit', $material->id) . '" class="btn btn-sm btn-warning" title="Edit">';
                $actions .= '<i class="bx bx-edit"></i>';
                $actions .= '</a> ';

                // Delete button
                $actions .= '<a href="' . route('school.library.destroy', $material->id) . '" class="btn btn-sm btn-danger delete-material" title="Delete" data-title="' . htmlspecialchars($material->title) . '">';
                $actions .= '<i class="bx bx-trash"></i>';
                $actions .= '</a>';

                return $actions;
            })
            ->rawColumns(['type_badge', 'status_badge', 'file_info', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new library material.
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('year_name', 'desc')
            ->get();

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $subjects = Subject::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('school.library.create', compact('academicYears', 'classes', 'subjects'));
    }

    /**
     * Store a newly created library material.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|in:pdf_book,notes,past_paper,assignment',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'class_id' => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'status' => 'required|in:draft,published,archived',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            // Handle file upload
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . $originalName;
            $filePath = $file->storeAs('library_materials', $fileName, 'public');

            $material = LibraryMaterial::create([
                'title' => $request->title,
                'type' => $request->type,
                'description' => $request->description,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'original_name' => $originalName,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'academic_year_id' => $request->academic_year_id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'status' => $request->status,
                'is_active' => true,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('school.library.index')
                ->with('success', 'Library material uploaded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to upload library material: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified library material.
     */
    public function edit($id)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $material = LibraryMaterial::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->findOrFail($id);

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('year_name', 'desc')
            ->get();

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $subjects = Subject::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('school.library.edit', compact('material', 'academicYears', 'classes', 'subjects'));
    }

    /**
     * Update the specified library material.
     */
    public function update(Request $request, $id)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $material = LibraryMaterial::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|in:pdf_book,notes,past_paper,assignment',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'class_id' => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'status' => 'required|in:draft,published,archived',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'title' => $request->title,
                'type' => $request->type,
                'description' => $request->description,
                'academic_year_id' => $request->academic_year_id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'status' => $request->status,
                'updated_by' => Auth::id(),
            ];

            // Handle file upload if new file is provided
            if ($request->hasFile('file')) {
                // Delete old file
                if (Storage::disk('public')->exists($material->file_path)) {
                    Storage::disk('public')->delete($material->file_path);
                }

                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . $originalName;
                $filePath = $file->storeAs('library_materials', $fileName, 'public');

                $updateData['file_path'] = $filePath;
                $updateData['file_name'] = $fileName;
                $updateData['original_name'] = $originalName;
                $updateData['file_size'] = $file->getSize();
                $updateData['mime_type'] = $file->getMimeType();
            }

            $material->update($updateData);

            DB::commit();

            return redirect()->route('school.library.index')
                ->with('success', 'Library material updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update library material: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified library material.
     */
    public function destroy($id)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $material = LibraryMaterial::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->findOrFail($id);

        try {
            // Delete file
            if (Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }

            $material->delete();

            return redirect()->route('school.library.index')
                ->with('success', 'Library material deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete library material: ' . $e->getMessage());
        }
    }
}

