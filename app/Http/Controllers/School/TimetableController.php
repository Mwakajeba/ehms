<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\Timetable;
use App\Models\School\TimetablePeriod;
use App\Models\School\TimetableEntry;
use App\Models\School\TimetableSetting;
use App\Models\School\TimetableRoom;
use App\Models\School\Classe;
use App\Models\School\Stream;
use App\Models\School\Subject;
use App\Models\School\AcademicYear;
use App\Models\Hr\Employee;
use App\Models\Company;
use App\Models\School\SubjectTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class TimetableController extends Controller
{
    /**
     * Display a listing of timetables.
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

        // Get current academic year
        $currentAcademicYear = AcademicYear::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_current', true)
            ->first();

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('school.timetables.index', compact('academicYears', 'classes', 'currentAcademicYear'));
    }

    /**
     * Get timetables data for DataTables.
     */
    public function data(Request $request)
    {
        try {
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            $query = Timetable::with(['academicYear', 'classe', 'stream', 'creator'])
                ->where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                });

            // Apply filters
            $academicYearId = $request->filled('academic_year_id') ? $request->academic_year_id : null;
            $classId = $request->filled('class_id') ? $request->class_id : null;
            $timetableType = $request->filled('timetable_type') ? $request->timetable_type : null;
            $status = $request->filled('status') ? $request->status : null;

            if ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            }

            if ($classId) {
                $query->where('class_id', $classId);
            }

            if ($timetableType) {
                $query->where('timetable_type', $timetableType);
            }

            if ($status) {
                $query->where('status', $status);
            }

            // Use DataTables for all cases - simpler and more reliable
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('academic_year_name', function ($timetable) {
                    return $timetable->academicYear ? $timetable->academicYear->year_name : 'N/A';
                })
                ->addColumn('class_stream', function ($timetable) {
                    $class = $timetable->classe ? $timetable->classe->name : '';
                    $stream = $timetable->stream ? ' - ' . $timetable->stream->name : '';
                    return $class . $stream ?: 'N/A';
                })
                ->addColumn('type_badge', function ($timetable) {
                    $badges = [
                        'class' => 'primary',
                        'teacher' => 'info',
                        'teacher_on_duty' => 'danger',
                        'room' => 'success',
                        'master' => 'warning'
                    ];
                    $badge = $badges[$timetable->timetable_type] ?? 'secondary';
                    $typeName = str_replace('_', ' ', $timetable->timetable_type);
                    $typeName = ucwords($typeName);
                    return '<span class="badge bg-' . $badge . '">' . $typeName . '</span>';
                })
                ->addColumn('status_badge', function ($timetable) {
                    $badges = [
                        'draft' => 'secondary',
                        'reviewed' => 'info',
                        'approved' => 'success',
                        'published' => 'primary'
                    ];
                    $badge = $badges[$timetable->status] ?? 'secondary';
                    return '<span class="badge bg-' . $badge . '">' . ucfirst($timetable->status) . '</span>';
                })
                ->addColumn('creator_name', function ($timetable) {
                    return $timetable->creator ? $timetable->creator->name : 'N/A';
                })
                ->addColumn('actions', function ($timetable) {
                    $actions = '<div class="btn-group" role="group">';
                    $actions .= '<a href="' . route('school.timetables.show', $timetable->hashid) . '" class="btn btn-sm btn-info" title="View"><i class="bx bx-show"></i></a> ';
                    $actions .= '<a href="' . route('school.timetables.edit', $timetable->hashid) . '" class="btn btn-sm btn-warning" title="Edit"><i class="bx bx-edit"></i></a> ';
                    if ($timetable->status !== 'published') {
                        $actions .= '<button type="button" class="btn btn-sm btn-success publish-timetable" data-id="' . $timetable->hashid . '" title="Publish"><i class="bx bx-check-circle"></i></button> ';
                    }
                    $actions .= '<a href="' . route('school.timetables.duplicate', $timetable->hashid) . '" class="btn btn-sm btn-secondary" title="Duplicate"><i class="bx bx-copy"></i></a> ';
                    $actions .= '<button type="button" class="btn btn-sm btn-danger delete-timetable" data-id="' . $timetable->hashid . '" title="Delete"><i class="bx bx-trash"></i></button>';
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['type_badge', 'status_badge', 'actions'])
                ->make(true);
        } catch (\Exception $e) {
            \Log::error('TimetableController@data error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'draw' => intval($request->get('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error loading timetables: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new timetable.
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

        // Get current academic year
        $currentAcademicYear = AcademicYear::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_current', true)
            ->first();

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

        $teachers = Employee::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $rooms = TimetableRoom::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('room_name')
            ->get();

        return view('school.timetables.create', compact('academicYears', 'classes', 'subjects', 'teachers', 'rooms', 'currentAcademicYear'));
    }

    /**
     * Store a newly created timetable.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'nullable|exists:classes,id',
            'stream_id' => 'nullable|exists:streams,id',
            'timetable_type' => 'required|in:master,teacher_on_duty',
            'settings' => 'nullable|array',
            'periods' => 'nullable|array',
            'entries' => 'nullable|array',
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

            // Check for duplicate timetable (same type, class, stream, and academic year)
            $duplicateQuery = Timetable::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('academic_year_id', $request->academic_year_id)
                ->where('timetable_type', $request->timetable_type)
                ->where('class_id', $request->class_id);

            // Handle stream_id (can be null)
            if ($request->filled('stream_id')) {
                $duplicateQuery->where('stream_id', $request->stream_id);
            } else {
                $duplicateQuery->whereNull('stream_id');
            }

            $duplicateTimetable = $duplicateQuery->first();

            if ($duplicateTimetable) {
                DB::rollBack();
                $errorMessage = 'A timetable with the same type, class';
                if ($request->filled('stream_id')) {
                    $errorMessage .= ', and stream';
                }
                $errorMessage .= ' already exists for this academic year.';
                
                return redirect()->back()
                    ->withErrors(['timetable_type' => $errorMessage])
                    ->withInput();
            }

            $timetable = Timetable::create([
                'name' => $request->name,
                'description' => $request->description,
                'academic_year_id' => $request->academic_year_id,
                'class_id' => $request->class_id,
                'stream_id' => $request->stream_id,
                'timetable_type' => $request->timetable_type,
                'status' => 'draft',
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'created_by' => Auth::id(),
                'is_active' => true,
            ]);

            // Create settings
            if ($request->has('settings')) {
                $this->createSettings($timetable, $request->settings, $companyId, $branchId);
            }

            // Create periods
            if ($request->has('periods')) {
                $this->createPeriods($timetable, $request->periods);
            }

            // Create entries
            if ($request->has('entries')) {
                $this->createEntries($timetable, $request->entries, $companyId, $branchId);
            }

            DB::commit();

            return redirect()->route('school.timetables.index')
                ->with('success', 'Timetable created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create timetable: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display all teacher timetables.
     */
    public function showAllTeachers(Request $request, $academicYearId = 'all')
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Timetable::with(['academicYear', 'periods', 'entries.subject', 'entries.teacher', 'entries.room', 'entries.classe', 'entries.stream', 'settings', 'creator'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('timetable_type', 'teacher');

        if ($academicYearId !== 'all' && $academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $teacherTimetables = $query->orderBy('name')->get();

        // Get academic years for filter
        $academicYears = AcademicYear::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('year_name', 'desc')
            ->get();

        $selectedAcademicYear = $academicYearId !== 'all' ? AcademicYear::find($academicYearId) : null;

        return view('school.timetables.show-all-teachers', compact('teacherTimetables', 'academicYears', 'selectedAcademicYear', 'academicYearId'));
    }

    /**
     * Display the specified timetable.
     */
    public function show($hashId)
    {
        $timetable = Timetable::findByHashid($hashId, ['academicYear', 'classe', 'stream', 'periods', 'entries.subject', 'entries.teacher', 'entries.room', 'entries.classe', 'entries.stream', 'settings']);

        if (!$timetable) {
            abort(404, 'Timetable not found');
        }

        return view('school.timetables.show', compact('timetable'));
    }

    /**
     * Print/Export timetable as PDF.
     */
    public function print($hashId)
    {
        $timetable = Timetable::findByHashid($hashId, ['academicYear', 'classe', 'stream', 'periods', 'entries.subject', 'entries.teacher', 'entries.room', 'entries.classe', 'entries.stream', 'settings', 'company', 'branch']);

        if (!$timetable) {
            abort(404, 'Timetable not found');
        }

        // Get company information
        $company = Company::find(session('company_id')) ?? $timetable->company;
        $generatedAt = now();

        // Prepare timetable data for PDF
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $maxPeriods = $timetable->periods->max('period_number') ?? 8;
        
        // Group entries by day and period
        $entriesByDayPeriod = $timetable->entries->groupBy(function($entry) {
            return $entry->day_of_week . '-' . $entry->period_number;
        });

        // Calculate period times
        $periodDuration = $timetable->settings ? ($timetable->settings->period_duration_minutes ?? 40) : 40;
        $startTime = ($timetable->settings && $timetable->settings->school_start_time) 
            ? \Carbon\Carbon::parse($timetable->settings->school_start_time) 
            : \Carbon\Carbon::parse('08:00:00');

        // Prepare periods data
        $periodsData = [];
        for ($period = 1; $period <= $maxPeriods; $period++) {
            $periodStart = $startTime->copy()->addMinutes(($period - 1) * $periodDuration);
            $periodEnd = $periodStart->copy()->addMinutes($periodDuration);
            
            $periodsData[$period] = [
                'number' => $period,
                'start_time' => $periodStart->format('g:i A'),
                'end_time' => $periodEnd->format('g:i A'),
                'time_range' => $periodStart->format('g:i') . ' - ' . $periodEnd->format('g:i'),
            ];
        }

        $data = [
            'timetable' => $timetable,
            'company' => $company,
            'generatedAt' => $generatedAt,
            'days' => $days,
            'maxPeriods' => $maxPeriods,
            'entriesByDayPeriod' => $entriesByDayPeriod,
            'periodsData' => $periodsData,
        ];

        $pdf = Pdf::loadView('school.timetables.print', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'margin-top' => 10,
                'margin-right' => 10,
                'margin-bottom' => 10,
                'margin-left' => 10,
                'dpi' => 150,
                'defaultFont' => 'Arial'
            ]);

        $filename = 'timetable_' . str_replace(' ', '_', $timetable->name) . '_' . date('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Show the form for editing the specified timetable.
     */
    public function edit($hashId)
    {
        $timetable = Timetable::findByHashid($hashId, ['periods', 'entries.subject', 'entries.teacher', 'entries.room', 'entries.period', 'settings']);

        if (!$timetable) {
            abort(404, 'Timetable not found');
        }

        // Prepare periods data for JavaScript
        $periodsByDay = $timetable->periods->groupBy('day_of_week')->map(function($periods) {
            return $periods->map(function($p) {
                // Format times to HH:MM format for HTML time inputs
                // Handle both string and Carbon datetime formats
                $startTime = $p->start_time;
                $endTime = $p->end_time;
                
                // If it's a Carbon instance, format it
                if ($startTime instanceof \Carbon\Carbon) {
                    $startTime = $startTime->format('H:i');
                } elseif (is_string($startTime)) {
                    // If time includes seconds (HH:MM:SS), remove them
                    if (strlen($startTime) > 5) {
                        $startTime = substr($startTime, 0, 5);
                    }
                } else {
                    // Get raw value from database
                    $startTime = $p->getRawOriginal('start_time');
                    if (strlen($startTime) > 5) {
                        $startTime = substr($startTime, 0, 5);
                    }
                }
                
                if ($endTime instanceof \Carbon\Carbon) {
                    $endTime = $endTime->format('H:i');
                } elseif (is_string($endTime)) {
                    if (strlen($endTime) > 5) {
                        $endTime = substr($endTime, 0, 5);
                    }
                } else {
                    $endTime = $p->getRawOriginal('end_time');
                    if (strlen($endTime) > 5) {
                        $endTime = substr($endTime, 0, 5);
                    }
                }
                
                return [
                    'id' => $p->id,
                    'period_number' => $p->period_number,
                    'start_time' => $startTime ?: '08:00',
                    'end_time' => $endTime ?: '08:40',
                    'duration_minutes' => $p->duration_minutes,
                    'period_type' => $p->period_type,
                    'period_name' => $p->period_name,
                    'is_break' => (bool) $p->is_break,
                ];
            })->values();
        });

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

        $teachers = Employee::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $rooms = TimetableRoom::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('room_name')
            ->get();

        return view('school.timetables.edit', compact('timetable', 'academicYears', 'classes', 'subjects', 'teachers', 'rooms', 'periodsByDay'));
    }

    /**
     * Update the specified timetable.
     */
    public function update(Request $request, $hashId)
    {
        $timetable = Timetable::findByHashid($hashId);

        if (!$timetable) {
            abort(404, 'Timetable not found');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'nullable|exists:classes,id',
            'stream_id' => 'nullable|exists:streams,id',
            'timetable_type' => 'required|in:master,teacher_on_duty',
            'settings' => 'nullable|array',
            'periods' => 'nullable|array',
            'entries' => 'nullable|array',
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

            // Check for duplicate timetable (same type, class, stream, and academic year)
            // Exclude the current timetable being updated
            $duplicateQuery = Timetable::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('academic_year_id', $request->academic_year_id)
                ->where('timetable_type', $request->timetable_type)
                ->where('class_id', $request->class_id)
                ->where('id', '!=', $timetable->id);

            // Handle stream_id (can be null)
            if ($request->filled('stream_id')) {
                $duplicateQuery->where('stream_id', $request->stream_id);
            } else {
                $duplicateQuery->whereNull('stream_id');
            }

            $duplicateTimetable = $duplicateQuery->first();

            if ($duplicateTimetable) {
                DB::rollBack();
                $errorMessage = 'A timetable with the same type, class';
                if ($request->filled('stream_id')) {
                    $errorMessage .= ', and stream';
                }
                $errorMessage .= ' already exists for this academic year.';
                
                return redirect()->back()
                    ->withErrors(['timetable_type' => $errorMessage])
                    ->withInput();
            }

            $timetable->update([
                'name' => $request->name,
                'description' => $request->description,
                'academic_year_id' => $request->academic_year_id,
                'class_id' => $request->class_id,
                'stream_id' => $request->stream_id,
                'timetable_type' => $request->timetable_type,
            ]);

            // Update settings
            if ($request->has('settings')) {
                $this->updateSettings($timetable, $request->settings);
            }

            // Update periods
            if ($request->has('periods')) {
                $this->updatePeriods($timetable, $request->periods);
            }

            // Update entries
            if ($request->has('entries')) {
                $this->updateEntries($timetable, $request->entries);
            }

            DB::commit();

            return redirect()->route('school.timetables.index')
                ->with('success', 'Timetable updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update timetable: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified timetable.
     */
    public function destroy($hashId)
    {
        $timetable = Timetable::findByHashid($hashId);

        if (!$timetable) {
            return response()->json(['error' => 'Timetable not found'], 404);
        }

        try {
            $timetable->delete();
            return response()->json(['success' => true, 'message' => 'Timetable deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete timetable: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper method to create settings.
     */
    private function createSettings($timetable, $settings, $companyId, $branchId)
    {
        TimetableSetting::create(array_merge([
            'timetable_id' => $timetable->id,
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ], $settings));
    }

    /**
     * Helper method to update settings.
     */
    private function updateSettings($timetable, $settings)
    {
        $timetable->settings()->updateOrCreate(
            ['timetable_id' => $timetable->id],
            $settings
        );
    }

    /**
     * Helper method to create periods.
     */
    private function createPeriods($timetable, $periods)
    {
        foreach ($periods as $period) {
            TimetablePeriod::create(array_merge([
                'timetable_id' => $timetable->id,
            ], $period));
        }
    }

    /**
     * Helper method to update periods.
     */
    private function updatePeriods($timetable, $periods)
    {
        $timetable->periods()->delete();
        $this->createPeriods($timetable, $periods);
    }

    /**
     * Helper method to create entries.
     */
    private function createEntries($timetable, $entries, $companyId, $branchId)
    {
        foreach ($entries as $entry) {
            TimetableEntry::create(array_merge([
                'timetable_id' => $timetable->id,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ], $entry));
        }
    }

    /**
     * Helper method to update entries.
     */
    private function updateEntries($timetable, $entries)
    {
        $timetable->entries()->delete();
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $this->createEntries($timetable, $entries, $companyId, $branchId);
    }

    /**
     * Get streams for a class (AJAX).
     */
    public function getStreams(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid class ID'], 400);
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $classId = $request->class_id;

        $streams = Stream::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->whereHas('classes', function ($query) use ($classId) {
                $query->where('classes.id', $classId);
            })
            ->where('is_active', true)
            ->get(['id', 'name']);

        return response()->json($streams);
    }

    /**
     * Get subjects for a class (AJAX).
     */
    public function getSubjects(Request $request)
    {
        $classId = $request->class_id;
        // This would need to be implemented based on your subject-class relationship
        $subjects = Subject::where('is_active', true)->get();
        return response()->json($subjects);
    }

    /**
     * Check for conflicts in timetable (AJAX).
     */
    public function checkConflicts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timetable_id' => 'required|exists:timetables,id',
            'teacher_id' => 'nullable|exists:hr_employees,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'period_number' => 'required|integer|min:1|max:20',
            'class_id' => 'nullable|exists:classes,id',
            'stream_id' => 'nullable|exists:streams,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'conflicts' => [],
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;
            $timetable = Timetable::findOrFail($request->timetable_id);
            $academicYearId = $timetable->academic_year_id;
            
            $conflicts = [];
            $suggestions = [];

            // 1. Check if class already has a subject in this period (class constraint)
            if ($request->class_id && $request->day_of_week && $request->period_number) {
                $existingSubjectEntry = TimetableEntry::where('company_id', $companyId)
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->whereHas('timetable', function($query) use ($academicYearId, $request) {
                        $query->where('academic_year_id', $academicYearId)
                              ->where('class_id', $request->class_id);
                        if ($request->stream_id) {
                            $query->where('stream_id', $request->stream_id);
                        }
                    })
                    ->where('day_of_week', $request->day_of_week)
                    ->where('period_number', $request->period_number)
                    ->where(function($query) use ($request) {
                        // Exclude current entry if updating
                        if ($request->entry_id) {
                            $query->where('id', '!=', $request->entry_id);
                        }
                    })
                    ->where(function($query) use ($request) {
                        // Exclude entries from current timetable
                        $query->where('timetable_id', '!=', $request->timetable_id);
                    })
                    ->with(['subject', 'timetable'])
                    ->first();

                if ($existingSubjectEntry) {
                    $existingSubject = $existingSubjectEntry->subject->name ?? 'N/A';
                    $conflicts[] = [
                        'type' => 'class_duplicate_subject',
                        'severity' => 'error',
                        'message' => "This class already has {$existingSubject} scheduled during {$request->day_of_week} Period {$request->period_number}.",
                        'suggestion' => "A class cannot have two subjects in one period. Please choose a different period."
                    ];
                }
            }

            // 2. Check for too many heavy/compulsory subjects in one day (class constraint)
            if ($request->class_id && $request->day_of_week && $request->subject_id) {
                // Count compulsory subjects for this class on this day
                $dailyCompulsorySubjects = TimetableEntry::where('company_id', $companyId)
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->whereHas('timetable', function($query) use ($academicYearId, $request) {
                        $query->where('academic_year_id', $academicYearId)
                              ->where('class_id', $request->class_id);
                        if ($request->stream_id) {
                            $query->where('stream_id', $request->stream_id);
                        }
                    })
                    ->where('day_of_week', $request->day_of_week)
                    ->where('subject_type', 'compulsory')
                    ->where(function($query) use ($request) {
                        if ($request->entry_id) {
                            $query->where('id', '!=', $request->entry_id);
                        }
                    })
                    ->where(function($query) use ($request) {
                        // Include entries from current timetable too
                        $query->where('timetable_id', $request->timetable_id)
                              ->orWhere(function($q) use ($request) {
                                  $q->where('timetable_id', '!=', $request->timetable_id);
                              });
                    })
                    ->distinct('subject_id')
                    ->count('subject_id');

                // Check if current subject is compulsory
                $currentSubject = Subject::find($request->subject_id);
                $isCompulsory = $currentSubject && ($currentSubject->requirement_type ?? 'compulsory') == 'compulsory';
                
                if ($isCompulsory) {
                    $dailyCompulsorySubjects++; // Add current subject if it's compulsory
                }

                // Warn if more than 5 compulsory subjects in one day (configurable threshold)
                $maxCompulsoryPerDay = 5;
                if ($dailyCompulsorySubjects > $maxCompulsoryPerDay) {
                    $conflicts[] = [
                        'type' => 'class_too_many_heavy_subjects',
                        'severity' => 'warning',
                        'message' => "This class will have {$dailyCompulsorySubjects} compulsory subjects on {$request->day_of_week} (recommended max: {$maxCompulsoryPerDay}).",
                        'suggestion' => "Consider distributing compulsory subjects more evenly across the week or scheduling some as optional subjects."
                    ];
                }
            }

            // Only check teacher conflicts if teacher is selected
            if ($request->teacher_id) {
                $teacherId = $request->teacher_id;
                $dayOfWeek = $request->day_of_week;
                $periodNumber = $request->period_number;

                // 1. Check if teacher is teaching another class at the same time
                $timeConflict = TimetableEntry::where('company_id', $companyId)
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->whereHas('timetable', function($query) use ($academicYearId) {
                        $query->where('academic_year_id', $academicYearId);
                    })
                    ->where('teacher_id', $teacherId)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('period_number', $periodNumber)
                    ->where(function($query) use ($request) {
                        // Exclude current entry if updating
                        if ($request->entry_id) {
                            $query->where('id', '!=', $request->entry_id);
                        }
                    })
                    ->with(['timetable.classe', 'timetable.stream', 'subject'])
                    ->first();

                if ($timeConflict) {
                    $conflictClass = $timeConflict->timetable->classe->name ?? 'N/A';
                    $conflictStream = $timeConflict->timetable->stream ? $timeConflict->timetable->stream->name : '';
                    $conflictSubject = $timeConflict->subject->name ?? 'N/A';
                    
                    $conflicts[] = [
                        'type' => 'teacher_time_conflict',
                        'severity' => 'error',
                        'message' => "This teacher is already teaching {$conflictSubject} in {$conflictClass}" . ($conflictStream ? " ({$conflictStream})" : "") . " during {$dayOfWeek} Period {$periodNumber}.",
                        'suggestion' => "Consider assigning a different teacher or rescheduling this period."
                    ];
                }

                // 2. Check teacher's daily load (count periods per day)
                $dailyPeriods = TimetableEntry::where('company_id', $companyId)
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->whereHas('timetable', function($query) use ($academicYearId) {
                        $query->where('academic_year_id', $academicYearId);
                    })
                    ->where('teacher_id', $teacherId)
                    ->where('day_of_week', $dayOfWeek)
                    ->where(function($query) use ($request) {
                        if ($request->entry_id) {
                            $query->where('id', '!=', $request->entry_id);
                        }
                    })
                    ->count();

                // Add 1 for current entry if not updating
                if (!$request->entry_id) {
                    $dailyPeriods++;
                }

                // Warn if teacher has more than 6 periods per day (configurable threshold)
                $maxDailyLoad = 6; // Can be made configurable
                if ($dailyPeriods > $maxDailyLoad) {
                    $conflicts[] = [
                        'type' => 'teacher_daily_load',
                        'severity' => 'warning',
                        'message' => "This teacher will have {$dailyPeriods} periods on {$dayOfWeek} (recommended max: {$maxDailyLoad}).",
                        'suggestion' => "Consider distributing periods more evenly across the week."
                    ];
                }

                // 3. Check teacher's weekly load
                $weeklyPeriods = TimetableEntry::where('company_id', $companyId)
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->whereHas('timetable', function($query) use ($academicYearId) {
                        $query->where('academic_year_id', $academicYearId);
                    })
                    ->where('teacher_id', $teacherId)
                    ->where(function($query) use ($request) {
                        if ($request->entry_id) {
                            $query->where('id', '!=', $request->entry_id);
                        }
                    })
                    ->count();

                if (!$request->entry_id) {
                    $weeklyPeriods++;
                }

                // Warn if teacher has more than 30 periods per week (configurable threshold)
                $maxWeeklyLoad = 30;
                if ($weeklyPeriods > $maxWeeklyLoad) {
                    $conflicts[] = [
                        'type' => 'teacher_weekly_load',
                        'severity' => 'warning',
                        'message' => "This teacher will have {$weeklyPeriods} periods per week (recommended max: {$maxWeeklyLoad}).",
                        'suggestion' => "Consider reducing the teacher's workload or assigning additional teachers."
                    ];
                }

                // 4. Check for free periods per day (optional - warning only)
                // Check if teacher has at least one free period (skip if already has free periods)
                $periodsForDay = TimetableEntry::where('company_id', $companyId)
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->whereHas('timetable', function($query) use ($academicYearId) {
                        $query->where('academic_year_id', $academicYearId);
                    })
                    ->where('teacher_id', $teacherId)
                    ->where('day_of_week', $dayOfWeek)
                    ->where(function($query) use ($request) {
                        if ($request->entry_id) {
                            $query->where('id', '!=', $request->entry_id);
                        }
                    })
                    ->pluck('period_number')
                    ->toArray();

                if (!$request->entry_id) {
                    $periodsForDay[] = $periodNumber;
                }

                // Get max periods per day from timetable settings (default 8)
                $maxPeriodsPerDay = 8;
                if ($timetable->settings) {
                    $maxPeriodsPerDay = $timetable->settings->periods_per_day ?? 8;
                }

                // Check if teacher has consecutive periods without breaks
                $hasFreePeriod = false;
                for ($p = 1; $p <= $maxPeriodsPerDay; $p++) {
                    if (!in_array($p, $periodsForDay)) {
                        $hasFreePeriod = true;
                        break;
                    }
                }

                if (!$hasFreePeriod && count($periodsForDay) >= 4) {
                    $conflicts[] = [
                        'type' => 'teacher_no_free_period',
                        'severity' => 'info',
                        'message' => "This teacher has no free periods on {$dayOfWeek}.",
                        'suggestion' => "Consider leaving at least one period free for teacher preparation."
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'conflicts' => $conflicts,
                'has_conflicts' => count($conflicts) > 0
            ]);
        } catch (\Exception $e) {
            \Log::error('TimetableController@checkConflicts error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'conflicts' => [],
                'error' => 'Failed to check conflicts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new timetable entry.
     */
    public function storeEntry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timetable_id' => 'required|exists:timetables,id',
            'day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'period_number' => 'required|integer|min:1|max:12',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:hr_employees,id',
            'room_id' => 'nullable|exists:timetable_rooms,id',
            'subject_type' => 'nullable|in:compulsory,optional',
            'is_double_period' => 'nullable|boolean',
            'is_practical' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $timetable = Timetable::findOrFail($request->timetable_id);
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            // Get or create period
            $period = TimetablePeriod::firstOrCreate([
                'timetable_id' => $timetable->id,
                'day_of_week' => $request->day_of_week,
                'period_number' => $request->period_number,
            ], [
                'start_time' => '08:00:00',
                'end_time' => '08:40:00',
                'duration_minutes' => 40,
                'period_type' => 'regular',
                'is_break' => false,
                'sort_order' => $request->period_number,
            ]);

            // Create entry
            $entry = TimetableEntry::create([
                'timetable_id' => $timetable->id,
                'period_id' => $period->id,
                'day_of_week' => $request->day_of_week,
                'period_number' => $request->period_number,
                'subject_id' => $request->subject_id,
                'class_id' => $timetable->class_id,
                'stream_id' => $timetable->stream_id,
                'teacher_id' => $request->teacher_id,
                'room_id' => $request->room_id,
                'is_double_period' => $request->has('is_double_period') && $request->is_double_period == '1',
                'is_practical' => $request->has('is_practical') && $request->is_practical == '1',
                'subject_type' => $request->subject_type ?? 'compulsory',
                'notes' => $request->notes,
                'company_id' => $companyId,
                'branch_id' => $branchId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Timetable entry added successfully',
                'entry' => $entry
            ]);
        } catch (\Exception $e) {
            \Log::error('TimetableController@storeEntry error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a timetable entry.
     */
    public function updateEntry(Request $request, $entryId)
    {
        $validator = Validator::make($request->all(), [
            'day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'period_number' => 'required|integer|min:1|max:12',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:hr_employees,id',
            'room_id' => 'nullable|exists:timetable_rooms,id',
            'subject_type' => 'nullable|in:compulsory,optional',
            'is_double_period' => 'nullable|boolean',
            'is_practical' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $entry = TimetableEntry::findOrFail($entryId);
            
            // Verify the entry belongs to a timetable that the user can access
            $timetable = $entry->timetable;
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            if ($timetable->company_id !== $companyId || 
                ($timetable->branch_id && $timetable->branch_id !== $branchId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Get or create period if day/period changed
            if ($entry->day_of_week != $request->day_of_week || $entry->period_number != $request->period_number) {
                $period = TimetablePeriod::firstOrCreate([
                    'timetable_id' => $timetable->id,
                    'day_of_week' => $request->day_of_week,
                    'period_number' => $request->period_number,
                ], [
                    'start_time' => '08:00:00',
                    'end_time' => '08:40:00',
                    'duration_minutes' => 40,
                    'period_type' => 'regular',
                    'is_break' => false,
                    'sort_order' => $request->period_number,
                ]);
                $entry->period_id = $period->id;
            }

            $entry->day_of_week = $request->day_of_week;
            $entry->period_number = $request->period_number;
            $entry->subject_id = $request->subject_id;
            $entry->teacher_id = $request->teacher_id;
            $entry->room_id = $request->room_id;
            $entry->subject_type = $request->subject_type ?? 'compulsory';
            $entry->is_double_period = $request->has('is_double_period') && $request->is_double_period == '1';
            $entry->is_practical = $request->has('is_practical') && $request->is_practical == '1';
            $entry->notes = $request->notes;
            $entry->save();

            return response()->json([
                'success' => true,
                'message' => 'Timetable entry updated successfully',
                'entry' => $entry->load(['subject', 'teacher', 'room'])
            ]);
        } catch (\Exception $e) {
            \Log::error('TimetableController@updateEntry error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get entry data for editing.
     */
    public function getEntry($entryId)
    {
        try {
            $entry = TimetableEntry::with(['subject', 'teacher', 'room'])->findOrFail($entryId);
            
            // Verify the entry belongs to a timetable that the user can access
            $timetable = $entry->timetable;
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            if ($timetable->company_id !== $companyId || 
                ($timetable->branch_id && $timetable->branch_id !== $branchId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'entry' => $entry
            ]);
        } catch (\Exception $e) {
            \Log::error('TimetableController@getEntry error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a timetable entry.
     */
    public function destroyEntry($entryId)
    {
        try {
            $entry = TimetableEntry::findOrFail($entryId);
            
            // Verify the entry belongs to a timetable that the user can access
            $timetable = $entry->timetable;
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            if ($timetable->company_id !== $companyId || 
                ($timetable->branch_id && $timetable->branch_id !== $branchId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $entry->delete();

            return response()->json([
                'success' => true,
                'message' => 'Entry deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('TimetableController@destroyEntry error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate a timetable.
     */
    /**
     * Publish a timetable.
     */
    public function publish($hashId)
    {
        $timetable = Timetable::findByHashid($hashId);

        if (!$timetable) {
            return response()->json([
                'success' => false,
                'message' => 'Timetable not found'
            ], 404);
        }

        // Check if timetable has entries
        $entriesCount = TimetableEntry::where('timetable_id', $timetable->id)->count();
        if ($entriesCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot publish an empty timetable. Please add at least one timetable entry.'
            ], 422);
        }

        $timetable->status = 'published';
        $timetable->published_at = now();
        $timetable->save();

        return response()->json([
            'success' => true,
            'message' => 'Timetable published successfully!'
        ]);
    }

    /**
     * Duplicate a timetable.
     */
    public function duplicate($hashId)
    {
        $timetable = Timetable::findByHashid($hashId, ['periods', 'entries', 'settings']);

        if (!$timetable) {
            abort(404, 'Timetable not found');
        }

        try {
            DB::beginTransaction();

            $newTimetable = $timetable->replicate();
            $newTimetable->name = $timetable->name . ' (Copy)';
            $newTimetable->status = 'draft';
            $newTimetable->created_by = Auth::id();
            $newTimetable->reviewed_by = null;
            $newTimetable->approved_by = null;
            $newTimetable->reviewed_at = null;
            $newTimetable->approved_at = null;
            $newTimetable->published_at = null;
            $newTimetable->save();

            // Duplicate periods
            foreach ($timetable->periods as $period) {
                $newPeriod = $period->replicate();
                $newPeriod->timetable_id = $newTimetable->id;
                $newPeriod->save();
            }

            // Duplicate entries
            foreach ($timetable->entries as $entry) {
                $newEntry = $entry->replicate();
                $newEntry->timetable_id = $newTimetable->id;
                $newEntry->save();
            }

            // Duplicate settings
            if ($timetable->settings) {
                $newSettings = $timetable->settings->replicate();
                $newSettings->timetable_id = $newTimetable->id;
                $newSettings->save();
            }

            DB::commit();

            return redirect()->route('school.timetables.edit', $newTimetable->hashid)
                ->with('success', 'Timetable duplicated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to duplicate timetable: ' . $e->getMessage());
        }
    }

    /**
     * Store/Update periods for a timetable.
     */
    public function storePeriods(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timetable_id' => 'required|exists:timetables,id',
            'periods' => 'required|array',
        ]);

        // Validate nested period data
        foreach ($request->periods as $day => $dayPeriods) {
            foreach ($dayPeriods as $index => $periodData) {
                $validator->sometimes("periods.{$day}.{$index}.day_of_week", 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday', function() { return true; });
                $validator->sometimes("periods.{$day}.{$index}.period_number", 'required|integer|min:1|max:20', function() { return true; });
                $validator->sometimes("periods.{$day}.{$index}.start_time", 'required|date_format:H:i', function() { return true; });
                $validator->sometimes("periods.{$day}.{$index}.end_time", 'required|date_format:H:i', function() { return true; });
                $validator->sometimes("periods.{$day}.{$index}.duration_minutes", 'required|integer|min:1|max:120', function() { return true; });
                $validator->sometimes("periods.{$day}.{$index}.period_type", 'required|in:regular,break,assembly,games,lunch', function() { return true; });
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $timetable = Timetable::findOrFail($request->timetable_id);
            
            // Verify ownership
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;
            
            if ($timetable->company_id !== $companyId || 
                ($timetable->branch_id && $timetable->branch_id !== $branchId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            DB::beginTransaction();

            // Load existing periods to preserve relationships with entries
            $existingPeriodsById = $timetable->periods()->get()->keyBy('id');
            $existingPeriodsByKey = $timetable->periods()->get()->keyBy(function($period) {
                return $period->day_of_week . '_' . $period->period_number;
            });

            // Track which periods are being kept (by their new day_period key)
            $keptPeriodKeys = [];
            // Track which period IDs are being kept
            $keptPeriodIds = [];

            // Update or create periods
            foreach ($request->periods as $day => $dayPeriods) {
                foreach ($dayPeriods as $periodData) {
                    $dayOfWeek = $periodData['day_of_week'] ?? $day;
                    $periodNumber = $periodData['period_number'];
                    $periodKey = $dayOfWeek . '_' . $periodNumber;
                    $keptPeriodKeys[] = $periodKey;

                    // Check if period already exists with same day and period_number
                    if (isset($existingPeriodsByKey[$periodKey])) {
                        $existingPeriod = $existingPeriodsByKey[$periodKey];
                        // Update existing period (preserves ID and relationships)
                        $existingPeriod->update([
                            'start_time' => $periodData['start_time'],
                            'end_time' => $periodData['end_time'],
                            'duration_minutes' => $periodData['duration_minutes'],
                            'period_type' => $periodData['period_type'],
                            'period_name' => $periodData['period_name'] ?? null,
                            'is_break' => isset($periodData['is_break']) && $periodData['is_break'] == '1',
                            'sort_order' => $periodNumber,
                        ]);
                        $keptPeriodIds[] = $existingPeriod->id;
                    } else {
                        // Check if this is an update to an existing period (by ID if provided)
                        $periodId = isset($periodData['id']) ? $periodData['id'] : null;
                        if ($periodId && isset($existingPeriodsById[$periodId])) {
                            $existingPeriod = $existingPeriodsById[$periodId];
                            // Period ID exists but day/period_number changed
                            // Update the period and update all entries that reference it
                            $oldKey = $existingPeriod->day_of_week . '_' . $existingPeriod->period_number;
                            
                            $existingPeriod->update([
                                'day_of_week' => $dayOfWeek,
                                'period_number' => $periodNumber,
                                'start_time' => $periodData['start_time'],
                                'end_time' => $periodData['end_time'],
                                'duration_minutes' => $periodData['duration_minutes'],
                                'period_type' => $periodData['period_type'],
                                'period_name' => $periodData['period_name'] ?? null,
                                'is_break' => isset($periodData['is_break']) && $periodData['is_break'] == '1',
                                'sort_order' => $periodNumber,
                            ]);
                            
                            // Update entries that reference this period
                            TimetableEntry::where('period_id', $periodId)
                                ->update([
                                    'day_of_week' => $dayOfWeek,
                                    'period_number' => $periodNumber,
                                ]);
                            
                            $keptPeriodIds[] = $periodId;
                        } else {
                            // Create new period
                            $newPeriod = TimetablePeriod::create([
                                'timetable_id' => $timetable->id,
                                'day_of_week' => $dayOfWeek,
                                'period_number' => $periodNumber,
                                'start_time' => $periodData['start_time'],
                                'end_time' => $periodData['end_time'],
                                'duration_minutes' => $periodData['duration_minutes'],
                                'period_type' => $periodData['period_type'],
                                'period_name' => $periodData['period_name'] ?? null,
                                'is_break' => isset($periodData['is_break']) && $periodData['is_break'] == '1',
                                'sort_order' => $periodNumber,
                            ]);
                            $keptPeriodIds[] = $newPeriod->id;
                        }
                    }
                }
            }

            // Delete only periods that are no longer in the submitted data
            // BUT only if they have no entries (to prevent cascade deletion of entries)
            foreach ($existingPeriodsById as $period) {
                if (!in_array($period->id, $keptPeriodIds)) {
                    // Check if period has any entries
                    $entryCount = TimetableEntry::where('period_id', $period->id)->count();
                    
                    if ($entryCount == 0) {
                        // Safe to delete - no entries reference it
                        $period->delete();
                    } else {
                        // Period has entries - don't delete, just log a warning
                        \Log::warning("Cannot delete period {$period->id} (Day: {$period->day_of_week}, Period: {$period->period_number}) because it has {$entryCount} entries");
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Periods saved successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('TimetableController@storePeriods error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save periods: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Copy Monday periods to all other days.
     */
    public function copyMondayPeriods(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timetable_id' => 'required|exists:timetables,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $timetable = Timetable::findOrFail($request->timetable_id);
            
            // Verify ownership
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;
            
            if ($timetable->company_id !== $companyId || 
                ($timetable->branch_id && $timetable->branch_id !== $branchId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            DB::beginTransaction();

            // Get Monday periods
            $mondayPeriods = $timetable->periods()->where('day_of_week', 'Monday')->orderBy('period_number')->get();
            
            if ($mondayPeriods->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Monday periods found. Please create periods for Monday first.'
                ], 400);
            }

            $otherDays = ['Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $copied = 0;

            foreach ($otherDays as $day) {
                // Check if periods already exist for this day
                $existingPeriods = $timetable->periods()->where('day_of_week', $day)->get();
                
                if ($existingPeriods->isEmpty()) {
                    // Copy Monday periods to this day
                    foreach ($mondayPeriods as $mondayPeriod) {
                        TimetablePeriod::create([
                            'timetable_id' => $timetable->id,
                            'day_of_week' => $day,
                            'period_number' => $mondayPeriod->period_number,
                            'start_time' => $mondayPeriod->start_time,
                            'end_time' => $mondayPeriod->end_time,
                            'duration_minutes' => $mondayPeriod->duration_minutes,
                            'period_type' => $mondayPeriod->period_type,
                            'period_name' => $mondayPeriod->period_name,
                            'is_break' => $mondayPeriod->is_break,
                            'sort_order' => $mondayPeriod->period_number,
                        ]);
                        $copied++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully copied {$copied} periods to other days. Days that already had periods were skipped."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('TimetableController@copyMondayPeriods error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to copy periods: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show bulk entry form for timetable.
     */
    public function bulkEntries($hashId)
    {
        $timetable = Timetable::findByHashid($hashId, ['periods', 'entries.subject', 'entries.teacher', 'entries.room']);

        if (!$timetable) {
            abort(404, 'Timetable not found');
        }

        // Check if periods are configured
        if ($timetable->periods->isEmpty()) {
            return redirect()->route('school.timetables.edit', $timetable->hashid)
                ->with('error', 'Please configure periods first before adding entries.');
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $subjects = Subject::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $teachers = Employee::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $rooms = TimetableRoom::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('room_name')
            ->get();

        // Group periods by day
        $periodsByDay = $timetable->periods->groupBy('day_of_week')->map(function($periods) {
            return $periods->sortBy('period_number');
        });

        // Get existing entries grouped by day and period
        $existingEntries = $timetable->entries->groupBy(function($entry) {
            return $entry->day_of_week . '-' . $entry->period_number;
        })->mapWithKeys(function($entries, $key) {
            return [$key => $entries->first()];
        });

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return view('school.timetables.bulk-entries', compact(
            'timetable', 
            'subjects', 
            'teachers', 
            'rooms', 
            'periodsByDay', 
            'existingEntries',
            'daysOfWeek'
        ));
    }

    /**
     * Store bulk timetable entries.
     */
    public function storeBulkEntries(Request $request, $hashId)
    {
        $timetable = Timetable::findByHashid($hashId);

        if (!$timetable) {
            return response()->json([
                'success' => false,
                'message' => 'Timetable not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'entries' => 'required|array',
            'entries.*.day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'entries.*.period_number' => 'required|integer|min:1',
            'entries.*.subject_id' => 'required|exists:subjects,id',
            'entries.*.teacher_id' => 'nullable|exists:hr_employees,id',
            'entries.*.room_id' => 'nullable|exists:timetable_rooms,id',
            'entries.*.subject_type' => 'nullable|in:compulsory,optional',
            'entries.*.is_double_period' => 'nullable|boolean',
            'entries.*.is_practical' => 'nullable|boolean',
            'entries.*.notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($request->entries as $entryData) {
                // Skip if subject is not selected
                if (empty($entryData['subject_id'])) {
                    $skipped++;
                    continue;
                }

                // Find the corresponding period
                $period = TimetablePeriod::where('timetable_id', $timetable->id)
                    ->where('day_of_week', $entryData['day_of_week'])
                    ->where('period_number', $entryData['period_number'])
                    ->first();

                if (!$period) {
                    \Log::warning("Period not found for timetable {$timetable->id}, day {$entryData['day_of_week']}, period {$entryData['period_number']}");
                    $skipped++;
                    continue;
                }

                // Check if entry already exists
                $existingEntry = TimetableEntry::where('timetable_id', $timetable->id)
                    ->where('day_of_week', $entryData['day_of_week'])
                    ->where('period_number', $entryData['period_number'])
                    ->first();

                $entryData['timetable_id'] = $timetable->id;
                $entryData['period_id'] = $period->id;
                $entryData['class_id'] = $timetable->class_id;
                $entryData['stream_id'] = $timetable->stream_id;
                $entryData['company_id'] = $companyId;
                $entryData['branch_id'] = $branchId;
                $entryData['subject_type'] = $entryData['subject_type'] ?? 'compulsory';
                $entryData['is_double_period'] = isset($entryData['is_double_period']) && $entryData['is_double_period'] == '1';
                $entryData['is_practical'] = isset($entryData['is_practical']) && $entryData['is_practical'] == '1';
                $entryData['sort_order'] = $entryData['period_number'] ?? 0;

                if ($existingEntry) {
                    // Update existing entry
                    $existingEntry->update($entryData);
                    $updated++;
                } else {
                    // Create new entry
                    TimetableEntry::create($entryData);
                    $created++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully saved timetable entries. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}",
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('TimetableController@storeBulkEntries error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save entries: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate individual teacher timetables for selected academic year.
     */
    public function generateTeacherTimetables(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;
            $academicYearId = $request->academic_year_id;

            DB::beginTransaction();

            // Get all class timetables for the selected academic year
            $classTimetables = Timetable::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('academic_year_id', $academicYearId)
                ->where('timetable_type', 'class')
                ->with(['entries' => function($query) {
                    $query->whereNotNull('teacher_id');
                }])
                ->get();

            if ($classTimetables->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No class timetables found for the selected academic year. Please create class timetables first.'
                ], 400);
            }

            // Get all unique teachers from timetable entries
            $teacherIds = [];
            foreach ($classTimetables as $timetable) {
                foreach ($timetable->entries as $entry) {
                    if ($entry->teacher_id && !in_array($entry->teacher_id, $teacherIds)) {
                        $teacherIds[] = $entry->teacher_id;
                    }
                }
            }

            if (empty($teacherIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No teachers found in timetable entries. Please assign teachers to timetable entries first.'
                ], 400);
            }

            // Get teacher details
            $teachers = Employee::whereIn('id', $teacherIds)
                ->where('company_id', $companyId)
                ->get();

            $createdCount = 0;
            $skippedCount = 0;

            // Create teacher timetable for each teacher
            foreach ($teachers as $teacher) {
                // Check if teacher timetable already exists for this academic year
                $existingTimetable = Timetable::where('company_id', $companyId)
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->where('academic_year_id', $academicYearId)
                    ->where('timetable_type', 'teacher')
                    ->whereHas('entries', function($query) use ($teacher) {
                        $query->where('teacher_id', $teacher->id);
                    })
                    ->first();

                if ($existingTimetable) {
                    $skippedCount++;
                    continue;
                }

                // Get all entries for this teacher from all class timetables
                $teacherEntries = [];
                foreach ($classTimetables as $timetable) {
                    foreach ($timetable->entries as $entry) {
                        if ($entry->teacher_id == $teacher->id) {
                            $teacherEntries[] = $entry;
                        }
                    }
                }

                if (empty($teacherEntries)) {
                    continue;
                }

                // Create teacher timetable
                $teacherTimetable = Timetable::create([
                    'name' => $teacher->first_name . ' ' . $teacher->last_name . ' - Teacher Timetable',
                    'description' => 'Individual teacher timetable for ' . $teacher->first_name . ' ' . $teacher->last_name,
                    'academic_year_id' => $academicYearId,
                    'timetable_type' => 'teacher',
                    'status' => 'draft',
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'created_by' => Auth::id(),
                    'is_active' => true,
                ]);

                // Copy periods from first class timetable (assuming all have same periods)
                $firstClassTimetable = $classTimetables->first();
                if ($firstClassTimetable && $firstClassTimetable->periods) {
                    foreach ($firstClassTimetable->periods as $period) {
                        TimetablePeriod::create([
                            'timetable_id' => $teacherTimetable->id,
                            'day_of_week' => $period->day_of_week,
                            'period_number' => $period->period_number,
                            'start_time' => $period->start_time,
                            'end_time' => $period->end_time,
                            'duration_minutes' => $period->duration_minutes,
                            'period_type' => $period->period_type,
                            'period_name' => $period->period_name,
                            'is_break' => $period->is_break,
                            'sort_order' => $period->sort_order,
                        ]);
                    }
                }

                // Copy settings from first class timetable
                if ($firstClassTimetable && $firstClassTimetable->settings) {
                    $settings = $firstClassTimetable->settings;
                    TimetableSetting::create([
                        'timetable_id' => $teacherTimetable->id,
                        'school_start_time' => $settings->school_start_time,
                        'school_end_time' => $settings->school_end_time,
                        'period_duration_minutes' => $settings->period_duration_minutes,
                        'periods_per_day' => $settings->periods_per_day,
                        'morning_break_start' => $settings->morning_break_start,
                        'morning_break_duration' => $settings->morning_break_duration,
                        'lunch_break_start' => $settings->lunch_break_start,
                        'lunch_break_duration' => $settings->lunch_break_duration,
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                    ]);
                }

                // Copy entries for this teacher
                foreach ($teacherEntries as $entry) {
                    // Find the period_id for this entry
                    $period = TimetablePeriod::where('timetable_id', $teacherTimetable->id)
                        ->where('day_of_week', $entry->day_of_week)
                        ->where('period_number', $entry->period_number)
                        ->first();

                    TimetableEntry::create([
                        'timetable_id' => $teacherTimetable->id,
                        'period_id' => $period ? $period->id : null,
                        'day_of_week' => $entry->day_of_week,
                        'period_number' => $entry->period_number,
                        'subject_id' => $entry->subject_id,
                        'class_id' => $entry->class_id,
                        'stream_id' => $entry->stream_id,
                        'teacher_id' => $entry->teacher_id,
                        'room_id' => $entry->room_id,
                        'is_double_period' => $entry->is_double_period,
                        'is_practical' => $entry->is_practical,
                        'subject_type' => $entry->subject_type,
                        'notes' => $entry->notes,
                        'sort_order' => $entry->sort_order,
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                    ]);
                }

                $createdCount++;
            }

            DB::commit();

            $message = "Successfully created {$createdCount} teacher timetable(s).";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} teacher timetable(s) already exist and were skipped.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'created' => $createdCount,
                'skipped' => $skippedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('TimetableController@generateTeacherTimetables error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate teacher timetables: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher for a subject based on class, stream, and academic year.
     */
    public function getTeacherForSubject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'timetable_id' => 'required|exists:timetables,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $timetable = Timetable::findOrFail($request->timetable_id);
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            // Verify timetable ownership
            if ($timetable->company_id !== $companyId || 
                ($timetable->branch_id && $timetable->branch_id !== $branchId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Get subject teacher assignment
            $subjectTeacher = \App\Models\School\SubjectTeacher::where('subject_id', $request->subject_id)
                ->where('class_id', $timetable->class_id)
                ->where('academic_year_id', $timetable->academic_year_id)
                ->where('is_active', true)
                ->where(function($query) use ($timetable, $branchId) {
                    // Match stream if timetable has one, otherwise match null stream assignments
                    if ($timetable->stream_id) {
                        $query->where('stream_id', $timetable->stream_id);
                    } else {
                        $query->whereNull('stream_id');
                    }
                    
                    // Filter by branch
                    if ($branchId) {
                        $query->where(function($q) use ($branchId) {
                            $q->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                        });
                    }
                })
                ->with('employee')
                ->first();

            if ($subjectTeacher && $subjectTeacher->employee) {
                return response()->json([
                    'success' => true,
                    'teacher' => [
                        'id' => $subjectTeacher->employee->id,
                        'name' => ($subjectTeacher->employee->first_name ?? '') . ' ' . ($subjectTeacher->employee->last_name ?? ''),
                        'first_name' => $subjectTeacher->employee->first_name ?? '',
                        'last_name' => $subjectTeacher->employee->last_name ?? '',
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'teacher' => null,
                'message' => 'No teacher assigned to this subject for the selected class/stream'
            ]);
        } catch (\Exception $e) {
            \Log::error('TimetableController@getTeacherForSubject error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get teacher: ' . $e->getMessage()
            ], 500);
        }
    }
}
