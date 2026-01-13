<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReceptionReportController extends Controller
{
    /**
     * Display reception reports
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Date range filters
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Patient count by category (visit type)
        $patientsByCategory = Visit::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->select('visit_type', DB::raw('count(*) as count'))
            ->groupBy('visit_type')
            ->get()
            ->pluck('count', 'visit_type');

        // Time spent per department
        $timePerDepartment = VisitDepartment::whereHas('visit', function ($q) use ($companyId, $branchId, $startDate, $endDate) {
                $q->where('company_id', $companyId)
                  ->where('branch_id', $branchId)
                  ->whereBetween('visit_date', [$startDate, $endDate]);
            })
            ->where('status', 'completed')
            ->join('hospital_departments', 'visit_departments.department_id', '=', 'hospital_departments.id')
            ->select(
                'hospital_departments.name',
                'hospital_departments.type',
                DB::raw('COUNT(*) as visit_count'),
                DB::raw('AVG(waiting_time_seconds) as avg_waiting_seconds'),
                DB::raw('AVG(service_time_seconds) as avg_service_seconds'),
                DB::raw('SUM(waiting_time_seconds) as total_waiting_seconds'),
                DB::raw('SUM(service_time_seconds) as total_service_seconds')
            )
            ->groupBy('hospital_departments.id', 'hospital_departments.name', 'hospital_departments.type')
            ->get();

        // Total time per visit (from start to completion)
        $totalTimePerVisit = Visit::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->with(['visitDepartments'])
            ->get()
            ->map(function ($visit) {
                $totalSeconds = 0;
                foreach ($visit->visitDepartments as $vd) {
                    if ($vd->waiting_time_seconds) {
                        $totalSeconds += $vd->waiting_time_seconds;
                    }
                    if ($vd->service_time_seconds) {
                        $totalSeconds += $vd->service_time_seconds;
                    }
                }
                
                $hours = floor($totalSeconds / 3600);
                $minutes = floor(($totalSeconds % 3600) / 60);
                $seconds = $totalSeconds % 60;
                
                return [
                    'visit_number' => $visit->visit_number,
                    'patient_name' => $visit->patient->full_name,
                    'visit_type' => $visit->visit_type,
                    'total_seconds' => $totalSeconds,
                    'total_time' => sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds),
                    'departments_count' => $visit->visitDepartments->count(),
                ];
            })
            ->sortByDesc('total_seconds')
            ->take(50);

        // Summary statistics
        $summary = [
            'total_visits' => Visit::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->whereBetween('visit_date', [$startDate, $endDate])
                ->count(),
            'completed_visits' => Visit::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->whereBetween('visit_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->count(),
            'avg_total_time' => $totalTimePerVisit->avg('total_seconds') ?? 0,
        ];

        return view('hospital.reception.reports.index', compact(
            'patientsByCategory',
            'timePerDepartment',
            'totalTimePerVisit',
            'summary',
            'startDate',
            'endDate'
        ));
    }
}
