<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $branchId = session('branch_id');
        $query = Level::where('branch_id', $branchId)
            ->with(['createdBy', 'updatedBy']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $levels = $query->ordered()->paginate(15)->withQueryString();
        $categories = Level::CATEGORIES;

        return view('college.levels.index', compact('levels', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Level::CATEGORIES;
        return view('college.levels.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'short_name' => 'required|string|max:20',
            'code' => 'nullable|string|max:20|unique:college_levels,code,NULL,id,branch_id,' . session('branch_id'),
            'category' => 'required|string|in:' . implode(',', array_keys(Level::CATEGORIES)),
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['branch_id'] = session('branch_id');
        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');

        Level::create($validated);

        return redirect()->route('college.levels.index')
            ->with('success', 'Academic Level created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Level $level)
    {
        $this->authorizeLevel($level);
        
        $level->load(['createdBy', 'updatedBy']);
        
        // Count exam schedules that use this level (by code or short_name)
        $examCount = \App\Models\College\ExamSchedule::where('level', $level->code)
            ->orWhere('level', $level->short_name)
            ->count();
        
        return view('college.levels.show', compact('level', 'examCount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Level $level)
    {
        $this->authorizeLevel($level);
        
        $categories = Level::CATEGORIES;
        return view('college.levels.edit', compact('level', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Level $level)
    {
        $this->authorizeLevel($level);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'short_name' => 'required|string|max:20',
            'code' => 'nullable|string|max:20|unique:college_levels,code,' . $level->id . ',id,branch_id,' . session('branch_id'),
            'category' => 'required|string|in:' . implode(',', array_keys(Level::CATEGORIES)),
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['updated_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');

        $level->update($validated);

        return redirect()->route('college.levels.index')
            ->with('success', 'Academic Level updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Level $level)
    {
        $this->authorizeLevel($level);

        // Check if level is in use by exam schedules
        $inUse = \App\Models\College\ExamSchedule::where('level', $level->code)
            ->orWhere('level', $level->short_name)
            ->exists();
            
        if ($inUse) {
            return redirect()->route('college.levels.index')
                ->with('error', 'Cannot delete level. It is being used in exam schedules.');
        }

        $level->delete();

        return redirect()->route('college.levels.index')
            ->with('success', 'Academic Level deleted successfully.');
    }

    /**
     * Toggle level active status
     */
    public function toggleStatus(Level $level)
    {
        $this->authorizeLevel($level);
        
        $level->update([
            'is_active' => !$level->is_active,
            'updated_by' => Auth::id(),
        ]);

        $status = $level->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Level {$status} successfully.");
    }

    /**
     * Get levels data for AJAX calls
     */
    public function data(Request $request)
    {
        $branchId = session('branch_id');
        $query = Level::where('branch_id', $branchId);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('active_only') && $request->active_only) {
            $query->active();
        }

        $levels = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $levels->map(function ($level) {
                return [
                    'id' => $level->id,
                    'name' => $level->name,
                    'short_name' => $level->short_name,
                    'code' => $level->code,
                    'category' => $level->category,
                    'category_name' => $level->category_name,
                    'is_active' => $level->is_active,
                    'display_name' => $level->display_name,
                ];
            })
        ]);
    }

    /**
     * Check if user has access to this level
     */
    private function authorizeLevel(Level $level)
    {
        $branchId = session('branch_id');
        if ($level->branch_id !== $branchId) {
            abort(403, 'Unauthorized access to this level.');
        }
    }
}
