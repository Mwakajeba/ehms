<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\Bus;
use App\Models\School\Route;
use App\Models\School\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class BusesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Check if this is a simple client-side DataTables request
            if ($request->has('simple')) {
                $buses = Bus::where('branch_id', session('branch_id') ?: Auth::user()->branch_id)
                    ->with(['routes', 'branch'])
                    ->get();
                $data = [];

                foreach ($buses as $index => $bus) {
                    // Get route IDs assigned to this bus
                    $routeIds = $bus->routes->pluck('id')->toArray();
                    
                    // Count students directly by route_id
                    $branchId = session('branch_id') ?: Auth::user()->branch_id;
                    $companyId = session('company_id') ?: Auth::user()->company_id;
                    
                    // If no routes assigned, student count is 0
                    if (empty($routeIds)) {
                        $studentCount = 0;
                    } else {
                        $studentQuery = Student::whereIn('route_id', $routeIds);
                        
                        // Filter by company and branch if available
                        if ($companyId) {
                            $studentQuery->where('company_id', $companyId);
                        }
                        if ($branchId) {
                            $studentQuery->where('branch_id', $branchId);
                        }
                        
                        $studentCount = $studentQuery->count();
                    }
                    $data[] = [
                        'DT_RowIndex' => $index + 1,
                        'bus_number' => '<span class="badge bg-primary">' . $bus->bus_number . '</span>',
                        'branch_name' => '<span class="badge bg-secondary">' . ($bus->branch->name ?? 'N/A') . '</span>',
                        'driver_name' => $bus->driver_name,
                        'driver_phone' => $bus->driver_phone,
                        'capacity' => '<span class="badge bg-info">' . $bus->capacity . '</span>',
                        'students_count' => '<span class="badge bg-warning">' . $studentCount . '</span>',
                        'status' => $bus->is_active ?
                            '<span class="badge bg-success">Active</span>' :
                            '<span class="badge bg-secondary">Inactive</span>',
                        'formatted_date' => $bus->created_at->format('M d, Y'),
                        'actions' => '<div class="btn-group" role="group">' .
                            '<a href="' . route('school.buses.show', $bus) . '" class="btn btn-sm btn-outline-info" title="View"><i class="bx bx-show"></i></a>' .
                            '<a href="' . route('school.buses.assign-routes', $bus) . '" class="btn btn-sm btn-outline-success" title="Assign Routes">Assign Routes</a>' .
                            '<a href="' . route('school.buses.edit', $bus) . '" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bx bx-edit"></i></a>' .
                            '<button type="button" class="btn btn-sm btn-outline-danger delete-bus-btn" title="Delete" data-bus-id="' . $bus->getRouteKey() . '" data-bus-number="' . $bus->bus_number . '"><i class="bx bx-trash"></i></button>' .
                            '</div>'
                    ];
                }

                return response()->json(['data' => $data]);
            }
        }

        // For non-AJAX requests, pass buses data to the view
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $companyId = session('company_id') ?: Auth::user()->company_id;
        
        $buses = Bus::where('branch_id', $branchId)
            ->with(['routes', 'branch'])
            ->get()
            ->map(function ($bus) use ($companyId, $branchId) {
                // Get route IDs assigned to this bus (use the loaded relationship)
                $routeIds = $bus->routes->pluck('id')->toArray();
                
                // If no routes assigned, student count is 0
                if (empty($routeIds)) {
                    $bus->students_count = 0;
                } else {
                    // Count students directly by route_id
                    $studentQuery = Student::whereIn('route_id', $routeIds);
                    
                    // Filter by company and branch if available
                    if ($companyId) {
                        $studentQuery->where('company_id', $companyId);
                    }
                    if ($branchId) {
                        $studentQuery->where('branch_id', $branchId);
                    }
                    
                    $bus->students_count = $studentQuery->count();
                }
                return $bus;
            });

        return view('school.buses.index', compact('buses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('school.buses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bus_number' => 'required|string|max:50|unique:buses,bus_number',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'required|string|max:20',
            'capacity' => 'required|integer|min:1',
            'model' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        $bus = Bus::create([
            'bus_number' => $request->bus_number,
            'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
            'driver_name' => $request->driver_name,
            'driver_phone' => $request->driver_phone,
            'capacity' => $request->capacity,
            'model' => $request->model,
            'registration_number' => $request->registration_number,
            'is_active' => $request->is_active,
            'company_id' => session('company_id') ?: Auth::user()->company_id,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('school.buses.index')
            ->with('success', 'Bus created successfully.');
    }

    public function show(Bus $bus)
    {
        // Check if bus belongs to user's branch
        if ($bus->branch_id !== (session('branch_id') ?: Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to bus.');
        }

        // Load routes relationship first
        $bus->load('routes');
        
        // Get route IDs assigned to this bus
        $routeIds = $bus->routes->pluck('id')->toArray();
        
        // Load students directly by route_id
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $companyId = session('company_id') ?: Auth::user()->company_id;
        
        if (empty($routeIds)) {
            $students = collect([]);
        } else {
            $studentQuery = Student::whereIn('route_id', $routeIds)
                ->with(['route', 'class']);
            
            // Filter by company and branch if available
            if ($companyId) {
                $studentQuery->where('company_id', $companyId);
            }
            if ($branchId) {
                $studentQuery->where('branch_id', $branchId);
            }
            
            $students = $studentQuery->get();
        }

        return view('school.buses.show', compact('bus', 'students'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bus $bus)
    {
        // Check if bus belongs to user's branch
        if ($bus->branch_id !== (session('branch_id') ?: Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to bus.');
        }

        return view('school.buses.edit', compact('bus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bus $bus)
    {
        // Check if bus belongs to user's branch
        if ($bus->branch_id !== (session('branch_id') ?: Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to bus.');
        }

        $request->validate([
            'bus_number' => 'required|string|max:50|unique:buses,bus_number,' . $bus->id,
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'required|string|max:20',
            'capacity' => 'required|integer|min:1',
            'model' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        $bus->update($request->only([
            'bus_number', 'driver_name', 'driver_phone', 'capacity',
            'model', 'registration_number', 'is_active'
        ]));

        return redirect()->route('school.buses.index')
            ->with('success', 'Bus updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bus $bus)
    {
        // Check if bus belongs to user's branch
        if ($bus->branch_id !== (session('branch_id') ?: Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to bus.');
        }

        $bus->delete();

        return redirect()->route('school.buses.index')
            ->with('success', 'Bus deleted successfully.');
    }

    /**
     * Show the form for assigning routes to a bus.
     */
    public function assignRoutes(Bus $bus)
    {
        // Check if bus belongs to user's branch
        if ($bus->branch_id !== (session('branch_id') ?: Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to bus.');
        }

        $routes = Route::where('branch_id', session('branch_id') ?: Auth::user()->branch_id)->get();
        $assignedRoutes = $bus->routes()->pluck('routes.id')->toArray();

        return view('school.buses.assign-routes', compact('bus', 'routes', 'assignedRoutes'));
    }

    /**
     * Update the routes assigned to a bus.
     */
    public function updateAssignedRoutes(Request $request, Bus $bus)
    {
        // Check if bus belongs to user's branch
        if ($bus->branch_id !== (session('branch_id') ?: Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to bus.');
        }

        $request->validate([
            'routes' => 'nullable|array',
            'routes.*' => 'exists:routes,id'
        ]);

        $routeIds = $request->input('routes', []);

        // Sync the routes with the bus
        $bus->routes()->sync($routeIds);

        return redirect()->route('school.buses.show', $bus)
            ->with('success', 'Routes assigned to bus successfully.');
    }

    /**
     * Remove an assigned route from a bus.
     */
    public function removeAssignedRoute(Bus $bus, Route $route)
    {
        // Check if bus belongs to user's branch
        if ($bus->branch_id !== (session('branch_id') ?: Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to bus.');
        }

        $bus->routes()->detach($route->id);

        return redirect()->back()
            ->with('success', 'Route removed from bus successfully.');
    }
}