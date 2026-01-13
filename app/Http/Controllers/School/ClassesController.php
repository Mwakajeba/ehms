<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hashids\Hashids;
use Yajra\DataTables\Facades\DataTables;

class ClassesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('school.classes.index');
    }

    /**
     * Get classes data for DataTables.
     */
    public function data(Request $request)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $companyId = Auth::user()->company_id;

        $query = Classe::query()
            ->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
            })
            ->where('company_id', $companyId)
            ->with(['sections', 'streams'])
            ->withCount(['students' => function ($q) use ($companyId, $branchId) {
                $q->where('company_id', $companyId)
                  ->where(function ($query) use ($branchId) {
                      $query->where('branch_id', $branchId)
                            ->orWhereNull('branch_id');
                  });
            }, 'enrollments', 'sections']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('streams_display', function ($class) {
                $streams = $class->streams->take(2);
                $html = '';
                foreach ($streams as $stream) {
                    $html .= '<span class="badge bg-primary me-1">' . $stream->name . '</span>';
                }
                if ($class->streams->count() > 2) {
                    $html .= '<span class="badge bg-secondary">+' . ($class->streams->count() - 2) . '</span>';
                }
                return $html ?: '<span class="text-muted">None</span>';
            })
            ->addColumn('students_count', function ($class) {
                return '<span class="badge bg-success">' . $class->students_count . '</span>';
            })
            ->addColumn('created_at_formatted', function ($class) {
                return $class->created_at->format('M d, Y');
            })
            ->addColumn('actions', function ($class) {
                return view('school.classes.partials.actions', compact('class'))->render();
            })
            ->rawColumns(['streams_display', 'students_count', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $streams = \App\Models\School\Stream::where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        })->get();
        return view('school.classes.create', compact('streams'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:classes,name',
            'streams' => 'nullable|array',
            'streams.*' => 'exists:streams,id'
        ]);

        $class = Classe::create([
            'name' => $request->name,
            'company_id' => Auth::user()->company_id,
            'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
            'created_by' => Auth::id(),
        ]);

        if ($request->has('streams')) {
            $class->streams()->attach($request->streams);
        }

        return redirect()->route('school.classes.index')
            ->with('success', 'Class created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Classe $classe)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($classe->branch_id !== $branchId && $classe->branch_id !== null) {
            abort(403, 'Unauthorized access to this class.');
        }

        $classe->load('sections', 'students', 'streams');
        return view('school.classes.show', compact('classe'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Classe $classe)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($classe->branch_id !== $branchId && $classe->branch_id !== null) {
            abort(403, 'Unauthorized access to this class.');
        }

        $streams = \App\Models\School\Stream::where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        })->get();
        return view('school.classes.edit', compact('classe', 'streams'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Classe $classe)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($classe->branch_id !== $branchId && $classe->branch_id !== null) {
            abort(403, 'Unauthorized access to this class.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:classes,name,' . $classe->id,
            'streams' => 'nullable|array',
            'streams.*' => 'exists:streams,id'
        ]);

        $classe->update([
            'name' => $request->name,
            'company_id' => Auth::user()->company_id,
            'branch_id' => $branchId,
        ]);

        if ($request->has('streams')) {
            $classe->streams()->sync($request->streams);
        } else {
            $classe->streams()->detach();
        }

        return redirect()->route('school.classes.index')
            ->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classe $classe)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($classe->branch_id !== $branchId && $classe->branch_id !== null) {
            abort(403, 'Unauthorized access to this class.');
        }

        // Check if class has any students enrolled
        if ($classe->students()->count() > 0) {
            return redirect()->route('school.classes.index')
                ->with('error', 'Cannot delete class "' . $classe->name . '" because it has ' . $classe->students()->count() . ' student(s) enrolled. Please remove all student enrollments first.');
        }

        // Check if class has any sections
        if ($classe->sections()->count() > 0) {
            return redirect()->route('school.classes.index')
                ->with('error', 'Cannot delete class "' . $classe->name . '" because it has ' . $classe->sections()->count() . ' section(s) assigned. Please remove all sections first.');
        }

        $classe->delete();

        return redirect()->route('school.classes.index')
            ->with('success', 'Class deleted successfully.');
    }

    /**
     * Get streams for a specific class
     */
    public function getStreams($classId)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $class = Classe::where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        })->findOrFail($classId);

        $streams = $class->streams()->where(function ($q) use ($branchId) {
            $q->where('streams.branch_id', $branchId)
              ->orWhereNull('streams.branch_id');
        })->select('streams.id', 'streams.name')->get();

        return response()->json($streams);
    }

    /**
     * Search classes for Select2 dropdown
     */
    public function search(Request $request)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $companyId = Auth::user()->company_id;

        $query = Classe::query()
            ->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
            })
            ->where('company_id', $companyId);

        // Search functionality
        if ($request->has('q') && !empty($request->q)) {
            $searchTerm = $request->q;
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        // Pagination
        $perPage = 30;
        $page = $request->get('page', 1);
        $classes = $query->select('id', 'name')
                         ->orderBy('name')
                         ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'classes' => $classes->items(),
            'total_count' => $classes->total(),
            'current_page' => $classes->currentPage(),
            'last_page' => $classes->lastPage()
        ]);
    }
}
