<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\AcademicYear;
use App\Models\School\Classe;
use App\Models\School\Student;
use App\Models\School\Stream;
use App\Models\School\Route;
use App\Models\School\BusStop;
use App\Models\School\Bus;
use App\Models\School\StudentTransfer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class StudentInformationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companyId = auth()->user()?->company_id;
        $branchId = session('branch_id') ?: auth()->user()?->branch_id;

        // Get data for filters
        $classes = Classe::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->orderBy('year_name', 'desc')
            ->get();

        // Get counts for streams and classes
        $streamsCount = Stream::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->count();

        $classesCount = Classe::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->count();

        // Get counts for transportation
        $routesCount = Route::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->count();

        $busStopsCount = BusStop::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->count();

        $busesCount = Bus::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->count();

        // Get counts for student operations
        $studentsCount = Student::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->count();

        $transfersCount = StudentTransfer::whereHas('student', function($query) use ($companyId, $branchId) {
                $query->where('company_id', $companyId);
                if ($branchId) {
                    $query->where(function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                    });
                }
            })
            ->count();

        // Get active students in current academic year for promotions
        $currentAcademicYear = AcademicYear::where('company_id', $companyId)
            ->where('is_current', true)
            ->first();
        
        $promotionsCount = 0;
        if ($currentAcademicYear) {
            $promotionsCount = Student::where('company_id', $companyId)
                ->where('status', 'active')
                ->where('academic_year_id', $currentAcademicYear->id)
                ->when($branchId, function($query) use ($branchId) {
                    return $query->where(function($q) use ($branchId) {
                        $q->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                    });
                })
                ->count();
        }

        return view('school.student-informations.index', compact(
            'classes', 
            'academicYears',
            'streamsCount',
            'classesCount',
            'routesCount',
            'busStopsCount',
            'busesCount',
            'studentsCount',
            'transfersCount',
            'promotionsCount'
        ));
    }

    public function getAcademicYears(): JsonResponse
    {
        $academicYears = AcademicYear::where('company_id', auth()->user()?->company_id)
            ->orderBy('year_name', 'desc')
            ->get(['id', 'year_name', 'is_current']);

        return response()->json([
            'academic_years' => $academicYears
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
