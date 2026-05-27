<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HospitalReportController extends Controller
{
    public function index()
    {
        return view('hospital.reports.index');
    }

    public function patientRegistration(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($validated['end_date'] ?? now()->toDateString())->endOfDay();

        $patients = Patient::query()
            ->with(['insuranceType', 'branch', 'creator'])
            ->byCompany($companyId)
            ->byBranch($branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total' => $patients->count(),
            'active' => $patients->where('is_active', true)->count(),
            'male' => $patients->where('gender', 'male')->count(),
            'female' => $patients->where('gender', 'female')->count(),
        ];

        $byInsurance = $patients
            ->groupBy(fn ($p) => $p->insurance_type_name)
            ->map->count()
            ->sortDesc();

        return view('hospital.reports.patient-registration', compact(
            'patients',
            'summary',
            'byInsurance',
            'startDate',
            'endDate'
        ));
    }
}
