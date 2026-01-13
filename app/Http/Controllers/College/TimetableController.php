<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\Timetable;
use App\Models\College\TimetableSlot;
use App\Models\College\Venue;
use App\Models\College\Program;
use App\Models\College\Course;
use App\Models\College\AcademicYear;
use App\Models\College\Semester;
use App\Models\College\Level;
use App\Models\Hr\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class TimetableController extends Controller
{
    /**
     * Display a listing of timetables
     */
    public function index(Request $request)
    {
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        $programs = Program::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('number')->get();

        return view('college.timetables.index', compact('programs', 'academicYears', 'semesters'));
    }

    /**
     * Get timetables data for DataTable
     */
    public function getData(Request $request)
    {
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        $query = Timetable::with(['program', 'academicYear', 'semester', 'createdBy'])
            ->withCount('activeSlots')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        // Apply filters
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addColumn('program_name', fn($row) => $row->program->name ?? 'N/A')
            ->addColumn('academic_year_name', fn($row) => $row->academicYear->name ?? 'N/A')
            ->addColumn('semester_name', fn($row) => $row->semester->name ?? 'N/A')
            ->addColumn('slots_count', fn($row) => $row->active_slots_count)
            ->addColumn('total_hours', fn($row) => $row->getTotalHoursPerWeek() . ' hrs')
            ->addColumn('status', fn($row) => $row->status)
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'draft' => '<span class="badge bg-warning">Draft</span>',
                    'published' => '<span class="badge bg-success">Published</span>',
                    'archived' => '<span class="badge bg-secondary">Archived</span>',
                ];
                return $badges[$row->status] ?? '<span class="badge bg-secondary">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('created_by_name', fn($row) => $row->createdBy->name ?? 'N/A')
            ->addColumn('action', function ($row) {
                $actions = '<div class="btn-group btn-group-sm" role="group">';
                $actions .= '<a href="' . route('college.timetables.show', $row->id) . '" class="btn btn-info" title="View"><i class="bx bx-show"></i></a>';
                
                if ($row->status !== 'archived') {
                    $actions .= '<a href="' . route('college.timetables.edit', $row->id) . '" class="btn btn-warning" title="Edit"><i class="bx bx-edit"></i></a>';
                }
                
                if ($row->status === 'draft') {
                    $actions .= '<button type="button" class="btn btn-success publish-btn" data-id="' . $row->id . '" title="Publish"><i class="bx bx-check-circle"></i></button>';
                }
                
                $actions .= '<button type="button" class="btn btn-secondary duplicate-btn" data-id="' . $row->id . '" title="Duplicate"><i class="bx bx-copy"></i></button>';
                $actions .= '<button type="button" class="btn btn-danger delete-btn" data-id="' . $row->id . '" title="Delete"><i class="bx bx-trash"></i></button>';
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new timetable
     */
    public function create()
    {
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        $programs = Program::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        // Get current academic year - check by status or date range, fallback to latest
        $currentAcademicYear = AcademicYear::where('status', 'active')
            ->first()
            ?? AcademicYear::whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first()
            ?? AcademicYear::orderBy('start_date', 'desc')->first();
        
        $semesters = Semester::orderBy('number')->get();

        // Get venues for the venue selector
        $venues = Venue::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get instructors (employees)
        $instructors = Employee::where('status', 'active')
            ->orderBy('first_name')
            ->get();

        // Get existing draft/published timetables for the dropdown
        $existingTimetables = Timetable::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['draft', 'published'])
            ->with(['program', 'academicYear', 'semester'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get academic levels
        $levels = Level::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('college.timetables.create', compact(
            'programs', 'academicYears', 'currentAcademicYear', 'semesters',
            'venues', 'instructors', 'existingTimetables', 'levels'
        ));
    }

    /**
     * Store a newly created timetable
     */
    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:college_programs,id',
            'academic_year_id' => 'required|exists:college_academic_years,id',
            'semester_id' => 'required|exists:college_semesters,id',
            'year_of_study' => 'required|integer|min:1|max:6',
            'name' => 'required|string|max:255',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'notes' => 'nullable|string|max:1000',
        ]);

        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        // Check for duplicate
        $exists = Timetable::where('program_id', $request->program_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->where('year_of_study', $request->year_of_study)
            ->whereIn('status', ['draft', 'published'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'A timetable already exists for this program, academic year, semester, and year of study combination.');
        }

        $timetable = Timetable::create([
            'program_id' => $request->program_id,
            'academic_year_id' => $request->academic_year_id,
            'semester_id' => $request->semester_id,
            'year_of_study' => $request->year_of_study,
            'name' => $request->name,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
            'notes' => $request->notes,
            'status' => 'draft',
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('college.timetables.edit', $timetable->id)
            ->with('success', 'Timetable created successfully! Now add your time slots.');
    }

    /**
     * Display the specified timetable
     */
    public function show(Timetable $timetable)
    {
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        $timetable->load(['program.department', 'academicYear', 'semester', 'createdBy', 'publishedBy', 'slots.course', 'slots.venue', 'slots.instructor']);
        
        $slotsByDay = $timetable->getSlotsByDay();
        $courses = Course::where('program_id', $timetable->program_id)
            ->where('status', 'active')
            ->orderBy('semester')
            ->orderBy('code')
            ->get();
        $totalHours = $timetable->getTotalHoursPerWeek();
        
        // Get venues
        $venues = Venue::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get instructors (employees) - include all active employees
        $instructors = Employee::where('status', 'active')
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        
        $days = Timetable::DAYS_OF_WEEK;
        $slotTypes = TimetableSlot::SLOT_TYPES;
        
        // Generate time slots from 7 AM to 9 PM
        $timeSlots = [];
        for ($hour = 7; $hour <= 21; $hour++) {
            $timeSlots[] = sprintf('%02d:00:00', $hour);
        }

        return view('college.timetables.show', compact(
            'timetable', 'slotsByDay', 'timeSlots', 'days', 'courses', 'totalHours',
            'venues', 'instructors', 'slotTypes'
        ));
    }

    /**
     * Show the form for editing the timetable
     */
    public function edit(Timetable $timetable)
    {
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        $timetable->load(['program', 'academicYear', 'semester', 'slots.course', 'slots.venue', 'slots.instructor']);

        // Get courses for this program
        $courses = Course::where('program_id', $timetable->program_id)
            ->where('status', 'active')
            ->orderBy('semester')
            ->orderBy('code')
            ->get();

        // Get venues
        $venues = Venue::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get instructors (employees)
        $instructors = Employee::where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $slotsByDay = $timetable->getSlotsByDay();

        $days = Timetable::DAYS_OF_WEEK;
        $slotTypes = TimetableSlot::SLOT_TYPES;
        
        // Generate time slots from 7 AM to 9 PM
        $timeSlots = [];
        for ($hour = 7; $hour <= 21; $hour++) {
            $timeSlots[] = sprintf('%02d:00:00', $hour);
        }

        return view('college.timetables.edit', compact(
            'timetable', 'courses', 'venues', 'instructors', 
            'slotsByDay', 'days', 'slotTypes', 'timeSlots'
        ));
    }

    /**
     * Update the specified timetable
     */
    public function update(Request $request, Timetable $timetable)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'notes' => 'nullable|string|max:1000',
        ]);

        $timetable->update([
            'name' => $request->name,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
            'notes' => $request->notes,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('college.timetables.show', $timetable->id)
            ->with('success', 'Timetable updated successfully!');
    }

    /**
     * Get a single slot data
     */
    public function getSlot(Timetable $timetable, TimetableSlot $slot)
    {
        return response()->json($slot->load(['course', 'venue', 'instructor']));
    }

    /**
     * Add a slot to the timetable
     */
    public function addSlot(Request $request, Timetable $timetable)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'venue_id' => 'nullable|exists:college_venues,id',
            'instructor_id' => 'nullable|exists:hr_employees,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'slot_type' => 'required|string',
            'group_name' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        // Append seconds if not present for time fields
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        if (strlen($startTime) == 5) $startTime .= ':00';
        if (strlen($endTime) == 5) $endTime .= ':00';

        // Check for venue conflict
        if ($request->venue_id) {
            $venue = Venue::find($request->venue_id);
            if ($venue && !$venue->isAvailable($request->day_of_week, $startTime, $endTime)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected venue is not available at this time.'
                ], 422);
            }
        }

        // Check for instructor conflict
        if ($request->instructor_id) {
            $instructorConflict = TimetableSlot::where('instructor_id', $request->instructor_id)
                ->where('day_of_week', $request->day_of_week)
                ->where('is_active', true)
                ->whereHas('timetable', function ($q) {
                    $q->whereIn('status', ['draft', 'published']);
                })
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function ($inner) use ($startTime, $endTime) {
                          $inner->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                      });
                })
                ->exists();

            if ($instructorConflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected instructor has a conflicting schedule at this time.'
                ], 422);
            }
        }

        $slot = TimetableSlot::create([
            'timetable_id' => $timetable->id,
            'course_id' => $request->course_id,
            'venue_id' => $request->venue_id,
            'instructor_id' => $request->instructor_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'slot_type' => $request->slot_type,
            'group_name' => $request->group_name,
            'notes' => $request->notes,
            'is_active' => true,
        ]);

        $slot->load(['course', 'venue', 'instructor']);

        return response()->json([
            'success' => true,
            'message' => 'Time slot added successfully!',
            'slot' => $slot,
        ]);
    }

    /**
     * Update a slot
     */
    public function updateSlot(Request $request, Timetable $timetable, TimetableSlot $slot)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'venue_id' => 'nullable|exists:college_venues,id',
            'instructor_id' => 'nullable|exists:hr_employees,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'slot_type' => 'required|string',
            'group_name' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        // Append seconds if not present for time fields
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        if (strlen($startTime) == 5) $startTime .= ':00';
        if (strlen($endTime) == 5) $endTime .= ':00';

        // Check for venue conflict (excluding this slot)
        if ($request->venue_id) {
            $venue = Venue::find($request->venue_id);
            if ($venue && !$venue->isAvailable($request->day_of_week, $startTime, $endTime, $slot->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected venue is not available at this time.'
                ], 422);
            }
        }

        $slot->update([
            'course_id' => $request->course_id,
            'venue_id' => $request->venue_id,
            'instructor_id' => $request->instructor_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'slot_type' => $request->slot_type,
            'group_name' => $request->group_name,
            'notes' => $request->notes,
        ]);

        $slot->load(['course', 'venue', 'instructor']);

        return response()->json([
            'success' => true,
            'message' => 'Time slot updated successfully!',
            'slot' => $slot,
        ]);
    }

    /**
     * Delete a slot
     */
    public function deleteSlot(Timetable $timetable, TimetableSlot $slot)
    {
        $slot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Time slot deleted successfully!',
        ]);
    }

    /**
     * Publish a timetable
     */
    public function publish(Timetable $timetable)
    {
        if ($timetable->activeSlots()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot publish an empty timetable. Please add at least one time slot.'
            ], 422);
        }

        $timetable->publish();

        return response()->json([
            'success' => true,
            'message' => 'Timetable published successfully!'
        ]);
    }

    /**
     * Archive a timetable
     */
    public function archive(Timetable $timetable)
    {
        $timetable->archive();

        return response()->json([
            'success' => true,
            'message' => 'Timetable archived successfully!'
        ]);
    }

    /**
     * Duplicate a timetable
     */
    public function duplicate(Request $request, Timetable $timetable)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:college_academic_years,id',
            'semester_id' => 'required|exists:college_semesters,id',
            'name' => 'nullable|string|max:255',
        ]);

        $newTimetable = $timetable->duplicate(
            $request->academic_year_id,
            $request->semester_id,
            $request->name
        );

        return response()->json([
            'success' => true,
            'message' => 'Timetable duplicated successfully!',
            'redirect' => route('college.timetables.edit', $newTimetable->id),
        ]);
    }

    /**
     * Delete a timetable
     */
    public function destroy(Timetable $timetable)
    {
        // Delete all associated slots first
        $timetable->slots()->delete();
        
        // Delete the timetable
        $timetable->delete();

        return response()->json([
            'success' => true,
            'message' => 'Timetable deleted successfully!'
        ]);
    }

    /**
     * Print timetable view
     */
    public function print(Timetable $timetable)
    {
        $timetable->load(['program.department', 'academicYear', 'semester', 'slots.course', 'slots.venue', 'slots.instructor']);
        
        $slotsByDay = $timetable->getSlotsByDay();
        $courses = $timetable->getCourses();
        $totalHours = $timetable->getTotalHoursPerWeek();
        
        $days = Timetable::DAYS_OF_WEEK;
        
        // Generate time slots from 7 AM to 9 PM
        $timeSlots = [];
        for ($hour = 7; $hour <= 21; $hour++) {
            $timeSlots[] = sprintf('%02d:00:00', $hour);
        }

        return view('college.timetables.print', compact('timetable', 'slotsByDay', 'timeSlots', 'days', 'courses', 'totalHours'));
    }

    /**
     * Export timetable to PDF
     */
    public function exportPdf(Timetable $timetable)
    {
        $timetable->load(['program.department', 'academicYear', 'semester', 'slots.course', 'slots.venue', 'slots.instructor']);
        
        $slotsByDay = $timetable->getSlotsByDay();
        $courses = $timetable->getCourses();
        $totalHours = $timetable->getTotalHoursPerWeek();
        
        $days = Timetable::DAYS_OF_WEEK;
        
        // Generate time slots from 7 AM to 9 PM
        $timeSlots = [];
        for ($hour = 7; $hour <= 21; $hour++) {
            $timeSlots[] = sprintf('%02d:00:00', $hour);
        }

        $pdf = Pdf::loadView('college.timetables.pdf', compact('timetable', 'slotsByDay', 'timeSlots', 'days', 'courses', 'totalHours'));
        
        $pdf->setPaper('a4', 'landscape');
        
        $filename = 'timetable_' . str_replace(' ', '_', $timetable->program->code) . '_year' . $timetable->year_of_study . '_' . date('Ymd') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Get courses for a program (AJAX)
     */
    public function getCourses($programId)
    {
        $courses = Course::where('program_id', $programId)
            ->where('status', 'active')
            ->orderBy('semester')
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'semester', 'credit_hours']);

        return response()->json($courses);
    }

    /**
     * Check venue availability (AJAX)
     */
    public function checkVenueAvailability(Request $request)
    {
        $request->validate([
            'venue_id' => 'required|exists:college_venues,id',
            'day_of_week' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'exclude_slot_id' => 'nullable|integer',
        ]);

        $venue = Venue::find($request->venue_id);
        $isAvailable = $venue->isAvailable(
            $request->day_of_week,
            $request->start_time,
            $request->end_time,
            $request->exclude_slot_id
        );

        return response()->json([
            'available' => $isAvailable,
            'message' => $isAvailable ? 'Venue is available' : 'Venue is not available at this time',
        ]);
    }

    /**
     * Check instructor availability (AJAX)
     */
    public function checkInstructorAvailability(Request $request)
    {
        $request->validate([
            'instructor_id' => 'required|exists:hr_employees,id',
            'day_of_week' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'exclude_slot_id' => 'nullable|integer',
        ]);

        // Check if instructor has any conflicting slots
        $conflict = TimetableSlot::where('instructor_id', $request->instructor_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('is_active', true)
            ->when($request->exclude_slot_id, fn($q) => $q->where('id', '!=', $request->exclude_slot_id))
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                    });
            })
            ->exists();

        return response()->json([
            'available' => !$conflict,
            'message' => !$conflict ? 'Instructor is available' : 'Instructor has a conflicting session',
        ]);
    }

    /**
     * Store a new timetable entry (combined timetable + slot creation)
     */
    public function storeEntry(Request $request)
    {
        $request->validate([
            'timetable_id' => 'nullable|exists:college_timetables,id',
            'academic_year_id' => 'required_without:timetable_id|exists:college_academic_years,id',
            'semester_id' => 'required_without:timetable_id|exists:college_semesters,id',
            'program_id' => 'required_without:timetable_id|exists:college_programs,id',
            'year_of_study' => 'required_without:timetable_id|integer|min:1|max:6',
            'course_id' => 'required|exists:courses,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'session_type' => 'required|in:lecture,tutorial,practical,lab,seminar,workshop,exam',
            'venue_id' => 'nullable|exists:college_venues,id',
            'employee_id' => 'nullable|exists:hr_employees,id',
            'remarks' => 'nullable|string|max:500',
        ]);

        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        DB::beginTransaction();

        try {
            // Get or create timetable
            if ($request->timetable_id) {
                $timetable = Timetable::findOrFail($request->timetable_id);
            } else {
                // Check if timetable exists for this combination
                $timetable = Timetable::where('program_id', $request->program_id)
                    ->where('academic_year_id', $request->academic_year_id)
                    ->where('semester_id', $request->semester_id)
                    ->where('year_of_study', $request->year_of_study)
                    ->whereIn('status', ['draft', 'published'])
                    ->first();

                if (!$timetable) {
                    // Create new timetable
                    $program = Program::find($request->program_id);
                    $academicYear = AcademicYear::find($request->academic_year_id);
                    $semester = Semester::find($request->semester_id);

                    $timetable = Timetable::create([
                        'program_id' => $request->program_id,
                        'academic_year_id' => $request->academic_year_id,
                        'semester_id' => $request->semester_id,
                        'year_of_study' => $request->year_of_study,
                        'name' => "{$program->code} Year {$request->year_of_study} - {$semester->name} ({$academicYear->name})",
                        'effective_from' => now(),
                        'status' => 'draft',
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);
                }
            }

            // Normalize time format
            $startTime = $request->start_time;
            $endTime = $request->end_time;
            if (strlen($startTime) === 5) $startTime .= ':00';
            if (strlen($endTime) === 5) $endTime .= ':00';

            // Create the slot
            $slot = TimetableSlot::create([
                'timetable_id' => $timetable->id,
                'course_id' => $request->course_id,
                'venue_id' => $request->venue_id,
                'instructor_id' => $request->employee_id,
                'day_of_week' => $request->day_of_week,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'slot_type' => $request->session_type,
                'notes' => $request->remarks,
                'is_active' => true,
            ]);

            DB::commit();

            // Check if "Add Another" was clicked
            if ($request->has('add_another')) {
                return redirect()->route('college.timetables.create')
                    ->with('success', 'Timetable entry added successfully! Add another entry.');
            }

            return redirect()->route('college.timetables.show', $timetable->id)
                ->with('success', 'Timetable entry created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create timetable entry: ' . $e->getMessage());
        }
    }
}
