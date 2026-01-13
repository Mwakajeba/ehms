<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\Route;
use App\Models\School\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class RoutesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Check if this is a simple client-side DataTables request
            if ($request->has('simple')) {
                try {
                    $branchId = session('branch_id') ?: Auth::user()->branch_id;
                    $companyId = session('company_id') ?: Auth::user()->company_id;
                    
                    $routes = Route::where('branch_id', $branchId)
                        ->with(['busStops', 'buses'])
                        ->get();
                    $data = [];

                    foreach ($routes as $index => $route) {
                        // Count students directly with proper filtering
                        $studentsQuery = Student::where('route_id', $route->id);
                        
                        // Filter by company and branch if available
                        if ($companyId) {
                            $studentsQuery->where('company_id', $companyId);
                        }
                        if ($branchId) {
                            $studentsQuery->where('branch_id', $branchId);
                        }
                        
                        $studentsCount = $studentsQuery->count();
                        
                        $data[] = [
                            'DT_RowIndex' => $index + 1,
                            'route_code' => '<span class="badge bg-primary">' . $route->route_code . '</span>',
                            'route_name' => $route->route_name,
                            'bus_stops_count' => '<span class="badge bg-info">' . $route->busStops->count() . '</span>',
                            'students_count' => '<span class="badge bg-success">' . $studentsCount . '</span>',
                            'formatted_date' => $route->created_at->format('M d, Y'),
                            'actions' => '<div class="btn-group" role="group">' .
                                '<a href="' . url('school/routes/' . $route->getRouteKey()) . '" class="btn btn-sm btn-outline-info" title="View"><i class="bx bx-show"></i></a>' .
                                '<a href="' . url('school/routes/' . $route->getRouteKey() . '/edit') . '" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bx bx-edit"></i></a>' .
                                '<button type="button" class="btn btn-sm btn-outline-danger delete-route-btn" title="Delete" data-route-id="' . $route->getRouteKey() . '" data-route-name="' . $route->route_name . '" data-bus-count="' . $route->buses->count() . '"><i class="bx bx-trash"></i></button>' .
                                '</div>'
                        ];
                    }

                    // Handle server-side DataTables request
                    if ($request->has('draw')) {
                        $totalRecords = Route::where('branch_id', session('branch_id') ?: Auth::user()->branch_id)->count();
                        return response()->json([
                            'draw' => intval($request->get('draw')),
                            'recordsTotal' => $totalRecords,
                            'recordsFiltered' => $totalRecords,
                            'data' => $data
                        ]);
                    }

                    return response()->json(['data' => $data]);
                } catch (\Exception $e) {
                    \Log::error('Routes DataTables error: ' . $e->getMessage());
                    return response()->json(['error' => 'Internal server error'], 500);
                }
            }
        }

        // For non-AJAX requests, pass routes data to the view
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $companyId = session('company_id') ?: Auth::user()->company_id;
        
        $routes = Route::where('branch_id', $branchId)
            ->with(['busStops', 'buses'])
            ->get();
        
        // Add students count to each route
        foreach ($routes as $route) {
            $studentsQuery = Student::where('route_id', $route->id);
            
            // Filter by company and branch if available
            if ($companyId) {
                $studentsQuery->where('company_id', $companyId);
            }
            if ($branchId) {
                $studentsQuery->where('branch_id', $branchId);
            }
            
            $route->students_count = $studentsQuery->count();
        }
        
        return view('school.routes.index', compact('routes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $busStops = \App\Models\School\BusStop::all();
        return view('school.routes.create', compact('busStops'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'route_name' => 'required|string|max:255',
            'route_code' => 'required|string|max:50|unique:routes,route_code',
            'description' => 'nullable|string|max:500',
            'bus_stops' => 'nullable|array',
            'bus_stops.*' => 'exists:bus_stops,id'
        ]);

        $route = Route::create([
            'route_name' => $request->route_name,
            'route_code' => $request->route_code,
            'description' => $request->description,
            'company_id' => session('company_id') ?: Auth::user()->company_id,
            'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
            'created_by' => Auth::id(),
        ]);

        if ($request->has('bus_stops')) {
            $route->busStops()->attach($request->bus_stops);
        }

        return redirect()->route('school.routes.index')
            ->with('success', 'Route created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Route $route)
    {
        // Check if route belongs to user's branch
        if ($route->branch_id !== (session('branch_id') ?: Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to route.');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $companyId = session('company_id') ?: Auth::user()->company_id;
        
        $route->load(['busStops']);
        $route->loadCount(['students' => function($query) use ($companyId, $branchId) {
            $query->where('company_id', $companyId)
                  ->where('branch_id', $branchId);
        }]);
        $route->load(['students' => function($query) use ($companyId, $branchId) {
            $query->where('company_id', $companyId)
                  ->where('branch_id', $branchId);
        }]);
        return view('school.routes.show', compact('route'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Route $route)
    {
        // Check if route belongs to user's branch
        if ($route->branch_id !== (session('branch_id') ?: Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to route.');
        }

        $busStops = \App\Models\School\BusStop::all();
        return view('school.routes.edit', compact('route', 'busStops'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Route $route)
    {
        // Check if route belongs to user's branch
        if ($route->branch_id !== (session('branch_id') ?: Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to route.');
        }

        $request->validate([
            'route_name' => 'required|string|max:255',
            'route_code' => 'required|string|max:50|unique:routes,route_code,' . $route->id,
            'description' => 'nullable|string|max:500',
            'bus_stops' => 'nullable|array',
            'bus_stops.*' => 'exists:bus_stops,id'
        ]);

        $route->update($request->only(['route_name', 'route_code', 'description']));

        if ($request->has('bus_stops')) {
            $route->busStops()->sync($request->bus_stops);
        } else {
            $route->busStops()->detach();
        }

        return redirect()->route('school.routes.index')
            ->with('success', 'Route updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Route $route)
    {
        // Check if route belongs to user's branch
        if ($route->branch_id !== (session('branch_id') ?: Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to route.');
        }

        // Check if route is assigned to any buses
        if ($route->buses()->count() > 0) {
            return redirect()->route('school.routes.index')
                ->with('error', 'Cannot delete route "' . $route->route_name . '" because it is assigned to ' . $route->buses()->count() . ' bus(es). Please remove all bus assignments first.');
        }

        $route->delete();

        return redirect()->route('school.routes.index')
            ->with('success', 'Route deleted successfully.');
    }
}
