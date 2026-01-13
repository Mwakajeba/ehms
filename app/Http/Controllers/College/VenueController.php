<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class VenueController extends Controller
{
    /**
     * Display a listing of venues
     */
    public function index()
    {
        return view('college.venues.index');
    }

    /**
     * Get venues data for DataTable
     */
    public function getData(Request $request)
    {
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        $query = Venue::with(['createdBy'])
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        if ($request->filled('venue_type')) {
            $query->where('venue_type', $request->venue_type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $query->orderBy('name');

        return DataTables::of($query)
            ->addColumn('venue_type_name', fn($row) => Venue::VENUE_TYPES[$row->venue_type] ?? $row->venue_type)
            ->addColumn('venue_type_badge', function ($row) {
                $colors = [
                    'lecture_hall' => 'primary',
                    'lab' => 'success',
                    'computer_lab' => 'info',
                    'seminar_room' => 'warning',
                    'auditorium' => 'danger',
                    'classroom' => 'secondary',
                    'workshop' => 'dark',
                    'other' => 'secondary'
                ];
                $color = $colors[$row->venue_type] ?? 'secondary';
                $name = Venue::VENUE_TYPES[$row->venue_type] ?? $row->venue_type;
                return '<span class="badge bg-' . $color . '">' . $name . '</span>';
            })
            ->addColumn('location', function ($row) {
                $location = [];
                if ($row->building) $location[] = $row->building;
                if ($row->floor) $location[] = $row->floor;
                return implode(' - ', $location) ?: 'N/A';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->is_active 
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('facilities_badges', function ($row) {
                if (empty($row->facilities)) return 'N/A';
                $badges = '';
                foreach ($row->facilities as $facility) {
                    $name = Venue::FACILITIES[$facility] ?? $facility;
                    $badges .= '<span class="badge bg-info me-1 mb-1">' . $name . '</span>';
                }
                return $badges;
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="' . route('college.venues.show', $row->id) . '" class="btn btn-info" title="View">
                            <i class="bx bx-show"></i>
                        </a>
                        <a href="' . route('college.venues.edit', $row->id) . '" class="btn btn-warning" title="Edit">
                            <i class="bx bx-edit"></i>
                        </a>
                        <button class="btn btn-danger delete-btn" data-id="' . $row->id . '" title="Delete">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status_badge', 'facilities_badges', 'venue_type_badge', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new venue
     */
    public function create()
    {
        $venueTypes = Venue::VENUE_TYPES;
        $facilities = Venue::FACILITIES;

        return view('college.venues.create', compact('venueTypes', 'facilities'));
    }

    /**
     * Store a newly created venue
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:college_venues,code',
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1|max:10000',
            'venue_type' => 'required|string|max:50',
            'facilities' => 'nullable|array',
            'facilities.*' => 'string|max:50',
            'is_active' => 'nullable',
        ]);

        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        Venue::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'building' => $request->building,
            'floor' => $request->floor,
            'capacity' => $request->capacity,
            'venue_type' => $request->venue_type,
            'facilities' => $request->facilities ?? [],
            'is_active' => $request->has('is_active') ? ($request->is_active == '1' || $request->is_active === true) : true,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('college.venues.index')
            ->with('success', 'Venue created successfully!');
    }

    /**
     * Display the specified venue
     */
    public function show(Venue $venue)
    {
        $venue->load(['createdBy', 'updatedBy']);
        
        // Get today's schedule
        $todaySchedule = $venue->getScheduleForDay(now()->format('l'));
        
        // Get weekly schedule
        $weeklySchedule = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($days as $day) {
            $weeklySchedule[$day] = $venue->getScheduleForDay($day);
        }

        return view('college.venues.show', compact('venue', 'todaySchedule', 'weeklySchedule'));
    }

    /**
     * Show the form for editing the venue
     */
    public function edit(Venue $venue)
    {
        $venueTypes = Venue::VENUE_TYPES;
        $facilities = Venue::FACILITIES;

        return view('college.venues.edit', compact('venue', 'venueTypes', 'facilities'));
    }

    /**
     * Update the specified venue
     */
    public function update(Request $request, Venue $venue)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:college_venues,code,' . $venue->id,
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1|max:1000',
            'venue_type' => 'required|in:' . implode(',', array_keys(Venue::VENUE_TYPES)),
            'facilities' => 'nullable|array',
            'facilities.*' => 'in:' . implode(',', array_keys(Venue::FACILITIES)),
            'is_active' => 'boolean',
        ]);

        $venue->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'building' => $request->building,
            'floor' => $request->floor,
            'capacity' => $request->capacity,
            'venue_type' => $request->venue_type,
            'facilities' => $request->facilities ?? [],
            'is_active' => $request->boolean('is_active', true),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('college.venues.index')
            ->with('success', 'Venue updated successfully!');
    }

    /**
     * Remove the specified venue
     */
    public function destroy(Venue $venue)
    {
        // Check if venue is in use
        if ($venue->timetableSlots()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete venue. It is being used in timetables.'
            ], 422);
        }

        $venue->delete();

        return response()->json([
            'success' => true,
            'message' => 'Venue deleted successfully!'
        ]);
    }

    /**
     * Get all active venues (for dropdowns)
     */
    public function getActiveVenues()
    {
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        $venues = Venue::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'building', 'capacity', 'venue_type']);

        return response()->json($venues);
    }
}
