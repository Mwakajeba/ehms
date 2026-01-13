<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class DocumentCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('college.document-categories.index');
    }

    /**
     * Get data for DataTables
     */
    public function data(Request $request)
    {
        $query = DocumentCategory::forCompany(Auth::user()->company_id)
            ->with(['creator']);

        // Only filter by branch if branch_id is set in session
        if (session('branch_id')) {
            $query->forBranch(session('branch_id'));
        }

        return DataTables::of($query)
            ->addColumn('actions', function ($category) {
                $showUrl = route('college.document-categories.show', $category->id);
                $editUrl = route('college.document-categories.edit', $category->id);
                $deleteUrl = route('college.document-categories.destroy', $category->id);

                return '<div class="btn-group" role="group">
                    <a href="' . $showUrl . '" class="btn btn-sm btn-info" title="View Details">
                        <i class="bx bx-show"></i>
                    </a>
                    <a href="' . $editUrl . '" class="btn btn-sm btn-warning" title="Edit Category">
                        <i class="bx bx-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-danger delete-btn"
                            data-name="' . $category->name . '"
                            data-url="' . $deleteUrl . '"
                            title="Delete Category">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>';
            })
            ->editColumn('created_at', function ($category) {
                return $category->created_at->format('M d, Y');
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('college.document-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:document_categories,code,NULL,id,company_id,' . Auth::user()->company_id . ',branch_id,' . (session('branch_id') ?? 'NULL'),
        ]);

        DocumentCategory::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'company_id' => Auth::user()->company_id,
            'branch_id' => session('branch_id'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('college.document-categories.index')
            ->with('success', 'Document category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DocumentCategory $documentCategory)
    {
        // Check if user has access to this category
        if ($documentCategory->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        if (session('branch_id') && $documentCategory->branch_id !== session('branch_id')) {
            abort(403);
        }

        return view('college.document-categories.show', compact('documentCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentCategory $documentCategory)
    {
        // Check if user has access to this category
        if ($documentCategory->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        if (session('branch_id') && $documentCategory->branch_id !== session('branch_id')) {
            abort(403);
        }

        return view('college.document-categories.edit', compact('documentCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentCategory $documentCategory)
    {
        // Check if user has access to this category
        if ($documentCategory->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        if (session('branch_id') && $documentCategory->branch_id !== session('branch_id')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:document_categories,code,' . $documentCategory->id . ',id,company_id,' . Auth::user()->company_id . ',branch_id,' . (session('branch_id') ?? 'NULL'),
        ]);

        $documentCategory->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
        ]);

        return redirect()->route('college.document-categories.index')
            ->with('success', 'Document category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentCategory $documentCategory)
    {
        // Check if user has access to this category
        if ($documentCategory->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        if (session('branch_id') && $documentCategory->branch_id !== session('branch_id')) {
            abort(403);
        }

        $documentCategory->delete();

        return redirect()->route('college.document-categories.index')
            ->with('success', 'Document category deleted successfully.');
    }
}
