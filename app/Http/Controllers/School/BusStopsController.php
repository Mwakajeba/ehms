<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\BusStop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Hashids\Hashids;

class BusStopsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            // Check if this is a simple client-side DataTables request
            if ($request->has('simple')) {
                $busStops = BusStop::with('routes')
                    ->where(function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                    })
                    ->ordered()
                    ->get();
                $data = [];

                foreach ($busStops as $index => $busStop) {
                    $routeCount = $busStop->routes->count();
                    $data[] = [
                        'DT_RowIndex' => $index + 1,
                        'stop_code' => $busStop->stop_code,
                        'stop_name' => $busStop->stop_name,
                        'formatted_fare' => $busStop->fare ? number_format($busStop->fare, 2) . ' TZS' : '-',
                        'sequence_order' => $busStop->sequence_order,
                        'status_badge' => $busStop->is_active ?
                            '<span class="badge bg-success">Active</span>' :
                            '<span class="badge bg-danger">Inactive</span>',
                        'formatted_date' => $busStop->created_at->format('M d, Y'),
                        'route_count' => $routeCount,
                        'actions' => '<div class="btn-group" role="group">' .
                            '<a href="' . route('school.bus-stops.show', $busStop) . '" class="btn btn-sm btn-outline-info" title="View"><i class="bx bx-show"></i></a>' .
                            '<a href="' . route('school.bus-stops.edit', $busStop) . '" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bx bx-edit"></i></a>' .
                            '<button type="button" class="btn btn-sm btn-outline-danger delete-bus-stop-btn" title="Delete" data-bus-stop-id="' . $busStop->getRouteKey() . '" data-bus-stop-name="' . $busStop->stop_name . '" data-route-count="' . $routeCount . '"><i class="bx bx-trash"></i></button>' .
                            '</div>'
                    ];
                }

                return response()->json(['data' => $data]);
            }

            // Server-side DataTables processing
            $busStops = BusStop::with('routes')
                ->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                })
                ->ordered();

            return DataTables::of($busStops)
                ->addIndexColumn()
                ->addColumn('actions', function ($busStop) {
                    $routeCount = $busStop->routes->count();
                    $actions = '<div class="btn-group" role="group">';
                    $actions .= '<a href="' . route('school.bus-stops.show', $busStop) . '" class="btn btn-sm btn-outline-info" title="View"><i class="bx bx-show"></i></a>';
                    $actions .= '<a href="' . route('school.bus-stops.edit', $busStop) . '" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bx bx-edit"></i></a>';
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-danger delete-bus-stop-btn" title="Delete" data-bus-stop-id="' . $busStop->getRouteKey() . '" data-bus-stop-name="' . $busStop->stop_name . '" data-route-count="' . $routeCount . '"><i class="bx bx-trash"></i></button>';
                    $actions .= '</div>';
                    return $actions;
                })
                ->addColumn('status_badge', function ($busStop) {
                    if ($busStop->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactive</span>';
                    }
                })
                ->addColumn('formatted_fare', function ($busStop) {
                    if ($busStop->fare) {
                        return number_format($busStop->fare, 2) . ' TZS';
                    }
                    return '-';
                })
                ->addColumn('formatted_date', function ($busStop) {
                    return $busStop->created_at->format('M d, Y');
                })
                ->rawColumns(['actions', 'status_badge'])
                ->make(true);
        }

        return view('school.bus-stops.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('school.bus-stops.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:bus_stops,stop_code',
            'fare' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'sequence_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean'
        ]);

        BusStop::create([
            'stop_name' => $request->name,
            'stop_code' => strtoupper($request->code),
            'fare' => $request->fare,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'sequence_order' => $request->sequence_order ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'company_id' => Auth::user()->company_id,
            'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('school.bus-stops.index')
            ->with('success', 'Bus stop created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BusStop $busStop)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($busStop->branch_id !== $branchId && $busStop->branch_id !== null) {
            abort(403, 'Unauthorized access to this bus stop.');
        }

        $busStop->load('routes');
        return view('school.bus-stops.show', compact('busStop'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusStop $busStop)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($busStop->branch_id !== $branchId && $busStop->branch_id !== null) {
            abort(403, 'Unauthorized access to this bus stop.');
        }

        return view('school.bus-stops.edit', compact('busStop'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusStop $busStop)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($busStop->branch_id !== $branchId && $busStop->branch_id !== null) {
            abort(403, 'Unauthorized access to this bus stop.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:bus_stops,stop_code,' . $busStop->id,
            'fare' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'sequence_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean'
        ]);

        $busStop->update([
            'stop_name' => $request->name,
            'stop_code' => strtoupper($request->code),
            'fare' => $request->fare,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'sequence_order' => $request->sequence_order ?? 0,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('school.bus-stops.index')
            ->with('success', 'Bus stop updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusStop $busStop)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($busStop->branch_id !== $branchId && $busStop->branch_id !== null) {
            abort(403, 'Unauthorized access to this bus stop.');
        }

        // Check if bus stop is assigned to any routes
        if ($busStop->routes()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete bus stop "' . $busStop->stop_name . '" because it is assigned to one or more routes. Please remove it from all routes first.');
        }

        $busStop->delete();

        return redirect()->route('school.bus-stops.index')
            ->with('success', 'Bus stop deleted successfully.');
    }
}
