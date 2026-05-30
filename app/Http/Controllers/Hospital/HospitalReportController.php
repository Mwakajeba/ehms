<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Exports\AudiologyReportExport;
use App\Exports\PatientRegistrationExport;
use App\Models\Hospital\Patient;
use App\Services\Hospital\AudiologyReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class HospitalReportController extends Controller
{
    public function __construct(
        private AudiologyReportService $audiologyReportService
    ) {}

    public function index()
    {
        return view('hospital.reports.index');
    }

    public function patientRegistration(Request $request)
    {
        $data = $this->patientRegistrationData($request);

        return view('hospital.reports.patient-registration', $data);
    }

    public function exportPatientRegistrationExcel(Request $request)
    {
        $data = $this->patientRegistrationData($request);

        $filename = 'patient-registration-report-' . $data['startDate']->format('Y-m-d') . '_to_' . $data['endDate']->format('Y-m-d') . '-' . hash('sha256', uniqid()) . '.xlsx';

        return Excel::download(
            new PatientRegistrationExport($data['patients']),
            $filename
        );
    }

    public function exportPatientRegistrationPdf(Request $request)
    {
        $data = $this->patientRegistrationData($request);

        $pdf = Pdf::loadView('hospital.reports.exports.patient-registration-pdf', $data);

        $filename = 'patient-registration-report-' . $data['startDate']->format('Y-m-d') . '_to_' . $data['endDate']->format('Y-m-d') . '-' . hash('sha256', uniqid()) . '.pdf';

        return $pdf->download($filename);
    }

    /** @return array<string, mixed> */
    private function patientRegistrationData(Request $request): array
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

        return compact('patients', 'summary', 'byInsurance', 'startDate', 'endDate');
    }

    public function audiology(Request $request)
    {
        $data = $this->audiologyReportData($request);

        return view('hospital.reports.audiology', $data);
    }

    public function exportAudiologyExcel(Request $request)
    {
        $data = $this->audiologyReportData($request);

        $filename = 'audiology-report-' . $data['startDate']->format('Y-m-d') . '_to_' . $data['endDate']->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new AudiologyReportExport(
                $data['rows'],
                $data['audiometryItems'],
                $data['deviceItems'],
                $data['startDate'],
                $data['endDate'],
                $data['periodLabel']
            ),
            $filename
        );
    }

    public function exportAudiologyPdf(Request $request)
    {
        $data = $this->audiologyReportData($request);

        $pdf = Pdf::loadView('hospital.reports.exports.audiology-pdf', $data)
            ->setPaper('a4', 'landscape');

        $filename = 'audiology-report-' . $data['startDate']->format('Y-m-d') . '_to_' . $data['endDate']->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /** @return array<string, mixed> */
    private function audiologyReportData(Request $request): array
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

        return $this->audiologyReportService->build($companyId, $branchId, $startDate, $endDate);
    }
}
