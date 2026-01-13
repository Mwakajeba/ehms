<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class SchoolReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('school.reports.index');
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

    public function studentReport()
    {
        return view('school.reports.student-report');
    }

    public function feeReport(Request $request)
    {
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Get filters
        $academicYearId = $request->academic_year_id;
        $classId = $request->class_id;
        $streamId = $request->stream_id;
        $quarter = $request->quarter;
        $status = $request->status;

        // Handle AJAX DataTables request
        if ($request->ajax()) {
            return $this->getFeeReportData($request);
        }

        // Handle exports
        if ($request->has('export')) {
            return $this->exportFeeReport($request);
        }

        // Get classes for filter - all classes created in the system
        $classes = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->withCount('students') // Include student count
            ->orderBy('name')
            ->get();

        // Get academic years for filter
        $academicYears = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->orderBy('year_name', 'desc')
            ->get();

        // Get streams for filter (filtered by selected class if any)
        $streamsQuery = \App\Models\School\Stream::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        if ($classId) {
            $streamsQuery->whereHas('students', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $streams = $streamsQuery->orderBy('name')->get();

        // Set default academic year to current active one if not specified
        if (!$academicYearId) {
            $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
                ->where('is_current', true)
                ->first();
            $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;
        }

        // Get fee payment data
        $feeData = $this->getFeePaymentData($branchId, $academicYearId, $classId, $streamId, $quarter, $status);

        return view('school.reports.fee-report', compact(
            'feeData',
            'classes',
            'academicYears',
            'streams',
            'academicYearId',
            'classId',
            'streamId',
            'quarter',
            'status'
        ));
    }

    public function detailedFeeCollection(Request $request)
    {
        // Handle PDF export for student details
        if ($request->isMethod('post') && $request->has('export') && $request->export === 'pdf') {
            return $this->exportStudentDetailsPDF($request);
        }

        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Get filters
        $academicYearId = $request->academic_year_id ? (is_numeric($request->academic_year_id) ? $request->academic_year_id : (Hashids::decode($request->academic_year_id)[0] ?? null)) : null;
        $classId = $request->class_id ? (is_numeric($request->class_id) ? $request->class_id : (Hashids::decode($request->class_id)[0] ?? null)) : null;
        $period = $request->period; // Q1, Q2, Q3, Q4, Term 1, Term 2, Annual, or null for all

        // Handle exports
        if ($request->has('export')) {
            return $this->exportDetailedFeeCollection($request);
        }

        // Get classes for filter - all classes created in the system
        $classes = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->withCount('students') // Include student count
            ->orderBy('name')
            ->get();

        // Get academic years for filter
        $academicYears = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->orderBy('year_name', 'desc')
            ->get();

        // Set default academic year to current active one if not specified
        if (!$academicYearId) {
            $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
                ->where('is_current', true)
                ->first();
            $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;
        }

        // Get fee collection data
        $feeCollectionData = $this->getDetailedFeeCollectionData($branchId, $academicYearId, $classId, $period);

        return view('school.reports.detailed-fee-collection', compact(
            'feeCollectionData',
            'classes',
            'academicYears',
            'academicYearId',
            'classId',
            'period'
        ));
    }

    public function otherIncomeCollection(Request $request)
    {
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Get filters
        $dateFrom = $request->date_from ?: now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?: now()->format('Y-m-d');
        $accountId = $request->account_id;
        $classId = $request->class_id;
        $streamId = $request->stream_id;

        // Get classes for filter
        $classes = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->withCount('students')
            ->orderBy('name')
            ->get();

        // Get streams for filter
        $streams = \App\Models\School\Stream::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        // Get income accounts for filter
        $incomeAccounts = \App\Models\ChartAccount::whereHas('accountClassGroup', function($query) {
            $query->where('company_id', auth()->user()->company_id);
        })->whereBetween('account_code', [4000, 4999])
            ->orderBy('account_code')
            ->orderBy('account_name')
            ->get();

        // Build query for other income collection
        $query = \App\Models\OtherIncome::where('company_id', auth()->user()->company_id)
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->with(['student.class', 'student.stream', 'incomeAccount']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($accountId) {
            $query->where('income_account_id', $accountId);
        }

        if ($classId) {
            $query->whereHas('student', function($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        if ($streamId) {
            $query->whereHas('student', function($q) use ($streamId) {
                $q->where('stream_id', $streamId);
            });
        }

        $otherIncomeData = $query->orderBy('transaction_date', 'desc')
            ->get();

        // Calculate total
        $totalAmount = $otherIncomeData->sum('amount');

        // Handle exports
        if ($request->has('export')) {
            return $this->exportOtherIncomeCollection($request, $otherIncomeData, $dateFrom, $dateTo, $accountId, $classId, $streamId, $totalAmount);
        }

        return view('school.reports.other-income-collection', compact(
            'otherIncomeData',
            'incomeAccounts',
            'classes',
            'streams',
            'dateFrom',
            'dateTo',
            'accountId',
            'classId',
            'streamId',
            'totalAmount'
        ));
    }

    private function exportOtherIncomeCollection(Request $request, $otherIncomeData, $dateFrom, $dateTo, $accountId, $classId, $streamId, $totalAmount)
    {
        $exportType = $request->export;

        if ($exportType === 'pdf') {
            $pdf = PDF::loadView('school.reports.exports.other-income-collection-pdf', compact(
                'otherIncomeData', 'dateFrom', 'dateTo', 'accountId', 'classId', 'streamId', 'totalAmount'
            ));
            return $pdf->download('other-income-collection-report-' . hash('sha256', uniqid()) . '.pdf');
        } elseif ($exportType === 'excel') {
            return Excel::download(new \App\Exports\OtherIncomeCollectionExport($otherIncomeData, $dateFrom, $dateTo, $accountId, $classId, $streamId), 'other-income-collection-report-' . hash('sha256', uniqid()) . '.xlsx');
        }

        return redirect()->back()->with('error', 'Invalid export type');
    }

    private function getFeeReportData(Request $request)
    {
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Get filters
        $academicYearId = $request->academic_year_id;
        $classId = $request->class_id;
        $streamId = $request->stream_id;
        $quarter = $request->quarter;
        $status = $request->status;

        // Build query
        $query = \App\Models\FeeInvoice::where('company_id', auth()->user()->company_id)
            ->with(['student.stream', 'items', 'classe', 'academicYear']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($streamId) {
            $query->whereHas('student', function ($q) use ($streamId) {
                $q->where('stream_id', $streamId);
            });
        }

        if ($quarter) {
            $query->where('period', $quarter);
        }

        if ($status) {
            switch ($status) {
                case 'issued':
                    $query->where('paid_amount', '=', 0)
                          ->where('due_date', '>=', now());
                    break;
                case 'partial_paid':
                    $query->where('paid_amount', '>', 0)
                          ->where('paid_amount', '<', \DB::raw('total_amount'));
                    break;
                case 'paid':
                    $query->whereColumn('paid_amount', '>=', 'total_amount');
                    break;
                case 'overdue':
                    $query->whereColumn('paid_amount', '<', 'total_amount')
                          ->where('due_date', '<', now());
                    break;
            }
        }

        // Handle DataTables server-side processing
        $totalRecords = $query->count();

        // Apply ordering
        $orderColumn = $request->input('order.0.column', 0);
        $orderDirection = $request->input('order.0.dir', 'asc');
        $columns = ['invoice_number', 'student_name', 'class_name', 'stream_name', 'quarter', 'academic_year', 'total_amount', 'paid_amount', 'outstanding_amount', 'due_date'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDirection);
        }

        // Apply search
        $searchValue = $request->input('search.value');
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('invoice_number', 'like', '%' . $searchValue . '%')
                  ->orWhereHas('student', function ($studentQuery) use ($searchValue) {
                      $studentQuery->where('first_name', 'like', '%' . $searchValue . '%')
                                   ->orWhere('last_name', 'like', '%' . $searchValue . '%');
                  })
                  ->orWhereHas('classe', function ($classQuery) use ($searchValue) {
                      $classQuery->where('name', 'like', '%' . $searchValue . '%');
                  })
                  ->orWhereHas('academicYear', function ($yearQuery) use ($searchValue) {
                      $yearQuery->where('year_name', 'like', '%' . $searchValue . '%');
                  })
                  ->orWhereHas('student.stream', function ($streamQuery) use ($searchValue) {
                      $streamQuery->where('name', 'like', '%' . $searchValue . '%');
                  })
                  ->orWhereRaw("CONCAT('Q', period) LIKE ?", ['%' . $searchValue . '%']);
            });
        }

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 25);
        $filteredRecords = $query->count();
        $invoices = $query->skip($start)->take($length)->get();

        // Format data for DataTables
        $data = [];
        foreach ($invoices as $invoice) {
            $statusBadge = '';
            if ($invoice->paid_amount >= $invoice->total_amount) {
                $statusBadge = '<span class="badge status-paid">Paid</span>';
            } elseif ($invoice->paid_amount > 0) {
                $statusBadge = '<span class="badge status-partial-paid">Partial Paid</span>';
            } elseif ($invoice->due_date && $invoice->due_date < now()) {
                $statusBadge = '<span class="badge status-overdue">Overdue</span>';
            } else {
                $statusBadge = '<span class="badge status-issued">Issued</span>';
            }

            $data[] = [
                'invoice_number' => $invoice->invoice_number,
                'student_name' => $invoice->student ? $invoice->student->first_name . ' ' . $invoice->student->last_name : 'N/A',
                'class_name' => $invoice->classe ? $invoice->classe->name : 'N/A',
                'stream_name' => $invoice->student && $invoice->student->stream ? $invoice->student->stream->name : 'N/A',
                'quarter' => $invoice->period ? 'Q' . $invoice->period : 'N/A',
                'academic_year' => $invoice->academicYear ? $invoice->academicYear->year_name : 'N/A',
                'total_amount' => number_format($invoice->total_amount, 2),
                'paid_amount' => number_format($invoice->paid_amount, 2),
                'outstanding_amount' => number_format($invoice->total_amount - $invoice->paid_amount, 2),
                'due_date' => $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'N/A',
                'status' => $statusBadge
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function getStreamsByClass(Request $request)
    {
        $classId = $request->class_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        $streams = \App\Models\School\Stream::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        if ($classId) {
            $streams->whereHas('students', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $streams = $streams->orderBy('name')->get();

        return response()->json([
            'streams' => $streams->map(function ($stream) {
                return [
                    'id' => $stream->id,
                    'name' => $stream->name
                ];
            })
        ]);
    }

    public function academicReport()
    {
        return view('school.reports.academic-report');
    }

    public function genderDistribution(Request $request)
    {
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Get classes, streams, and academic years for filters
        $classes = \App\Models\School\Classe::where('branch_id', $branchId)->orderBy('name')->get();
        $streams = \App\Models\School\Stream::where('branch_id', $branchId)->orderBy('name')->get();
        $academicYears = \App\Models\School\AcademicYear::orderBy('year_name')->get();

        // Get gender distribution data
        $genderData = $this->getGenderDistributionData($request, $branchId);

        // Handle PDF export
        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportGenderDistributionPdf($request, $genderData, $classes, $streams, $academicYears);
        }

        // Handle Excel export
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportGenderDistributionExcel($request, $genderData);
        }

        return view('school.reports.gender-distribution', compact(
            'classes',
            'streams',
            'academicYears',
            'genderData'
        ));
    }

    private function getGenderDistributionData(Request $request, $branchId)
    {
        $query = \App\Models\School\Student::with(['class', 'stream', 'academicYear'])
            ->where('branch_id', $branchId);

        // Apply filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('stream_id')) {
            $query->where('stream_id', $request->stream_id);
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $students = $query->get();

        // Group by class and stream
        $groupedData = [];
        $classTotals = [];
        $grandTotal = ['male' => 0, 'female' => 0, 'total' => 0];

        foreach ($students as $student) {
            $className = $student->class->name ?? 'Unknown Class';
            $streamName = $student->stream->name ?? 'Unknown Stream';
            $gender = strtolower($student->gender) === 'male' ? 'male' : 'female';

            if (!isset($groupedData[$className])) {
                $groupedData[$className] = [];
                $classTotals[$className] = ['male' => 0, 'female' => 0, 'total' => 0];
            }

            if (!isset($groupedData[$className][$streamName])) {
                $groupedData[$className][$streamName] = ['male' => 0, 'female' => 0, 'total' => 0];
            }

            $groupedData[$className][$streamName][$gender]++;
            $groupedData[$className][$streamName]['total']++;

            $classTotals[$className][$gender]++;
            $classTotals[$className]['total']++;

            $grandTotal[$gender]++;
            $grandTotal['total']++;
        }

        return [
            'groupedData' => $groupedData,
            'classTotals' => $classTotals,
            'grandTotal' => $grandTotal
        ];
    }

    private function exportGenderDistributionPdf(Request $request, $genderData, $classes, $streams, $academicYears)
    {
        $company = \App\Models\Company::find(session('company_id'));
        $generatedAt = now();

        // Get filter descriptions
        $filters = [];
        if ($request->filled('class_id')) {
            $class = $classes->find($request->class_id);
            $filters['class'] = $class ? $class->name : 'Unknown';
        }
        if ($request->filled('stream_id')) {
            $stream = $streams->find($request->stream_id);
            $filters['stream'] = $stream ? $stream->name : 'Unknown';
        }
        if ($request->filled('academic_year_id')) {
            $year = $academicYears->find($request->academic_year_id);
            $filters['academic_year'] = $year ? $year->year_name : 'Unknown';
        }

        $data = [
            'genderData' => $genderData,
            'company' => $company,
            'generatedAt' => $generatedAt,
            'filters' => $filters,
            'logo_path' => null, // Will be set below
        ];

        // Check if company logo exists and set the correct path for DomPDF
        if ($data['company'] && $data['company']->logo) {
            $logoFullPath = public_path('storage/' . $data['company']->logo);
            if (file_exists($logoFullPath)) {
                $data['logo_path'] = $logoFullPath;
            }
        }

        $pdf = \PDF::loadView('school.reports.exports.gender-distribution-pdf', $data);

        $filename = 'gender_distribution_report_' . date('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    private function exportGenderDistributionExcel(Request $request, $genderData)
    {
        return \Excel::download(new \App\Exports\GenderDistributionExport($genderData, $request), 'gender_distribution_report_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    public function examinationResults(Request $request)
    {
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Store original hashed values for form population
        $selectedAcademicYear = $request->academic_year_id;
        $selectedExamType = $request->exam_type_id;
        $selectedClass = $request->class_id;
        $selectedStream = $request->stream_id;

        // Decode hashed IDs - handle empty decode results properly
        // Also handle numeric IDs (in case they're passed directly)
        $decodeHash = function($hash, $paramName = '') {
            if (!$hash) {
                \Log::debug("Hash decode: {$paramName} is empty");
                return null;
            }
            
            // If it's already a numeric value, return it directly
            if (is_numeric($hash)) {
                \Log::debug("Hash decode: {$paramName} is already numeric: {$hash}");
                return (int)$hash;
            }
            
            // Try to decode as hash
            $decoded = Hashids::decode($hash);
            if (empty($decoded)) {
                \Log::warning("Hash decode failed for {$paramName}: '{$hash}' - decoded to empty array");
                return null;
            }
            $decodedId = $decoded[0];
            \Log::debug("Hash decode success for {$paramName}: '{$hash}' -> {$decodedId}");
            return $decodedId;
        };

        // Log original request parameters
        \Log::info('Examination Results Request - Original Parameters', [
            'academic_year_id' => $request->academic_year_id,
            'exam_type_id' => $request->exam_type_id,
            'class_id' => $request->class_id,
            'stream_id' => $request->stream_id,
            'all_params' => $request->all(),
        ]);

        $request->merge([
            'academic_year_id' => $decodeHash($request->academic_year_id, 'academic_year_id'),
            'exam_type_id' => $decodeHash($request->exam_type_id, 'exam_type_id'),
            'class_id' => $decodeHash($request->class_id, 'class_id'),
            'stream_id' => $decodeHash($request->stream_id, 'stream_id'),
        ]);

        // Log decoded parameters
        \Log::info('Examination Results Request - Decoded Parameters', [
            'academic_year_id' => $request->academic_year_id,
            'exam_type_id' => $request->exam_type_id,
            'class_id' => $request->class_id,
            'stream_id' => $request->stream_id,
        ]);

        // Get exam types, classes, streams, and academic years for filters
        $examTypes = \App\Models\SchoolExamType::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $classes = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $streams = \App\Models\School\Stream::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $academicYears = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->orderBy('year_name')
            ->get();

        // Get current academic year
        $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->where('is_current', true)
            ->first();

        // Get examination results data
        $examData = $this->getExaminationResultsData($request, $branchId);

        // Handle PDF export
        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportExaminationResultsPdf($request, $examData, $classes, $streams, $academicYears, $examTypes);
        }

        // Handle Excel export
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportExaminationResultsExcel($request, $examData);
        }

        return view('school.reports.examination-results', compact(
            'examTypes',
            'classes',
            'streams',
            'academicYears',
            'currentAcademicYear',
            'examData',
            'selectedAcademicYear',
            'selectedExamType',
            'selectedClass',
            'selectedStream'
        ));
    }

    /**
     * Display comparative subject performance analysis report
     */
    public function comparativeSubjectPerformance(Request $request)
    {
        try {
            $branchId = session('branch_id');

            // Store original hashed values for form population
            $selectedAcademicYear1 = $request->academic_year_id_1;
            $selectedAcademicYear2 = $request->academic_year_id_2;
            $selectedExamType1 = $request->exam_type_id_1;
            $selectedExamType2 = $request->exam_type_id_2;
            $selectedClass = $request->class_id;
            $selectedStream = $request->stream_id;

            // Decode hashed IDs
            try {
                $academicYearId1 = $request->academic_year_id_1;
                $examTypeId1 = $request->exam_type_id_1;
                $academicYearId2 = $request->academic_year_id_2;
                $examTypeId2 = $request->exam_type_id_2;
                $classId = $request->class_id;
                $streamId = $request->stream_id;

                // If numeric, use as is; else decode
                $decodedAcademicYear1 = is_numeric($academicYearId1) ? [$academicYearId1] : ($academicYearId1 ? Hashids::decode($academicYearId1) : []);
                $decodedExamType1 = is_numeric($examTypeId1) ? [$examTypeId1] : ($examTypeId1 ? Hashids::decode($examTypeId1) : []);
                $decodedAcademicYear2 = is_numeric($academicYearId2) ? [$academicYearId2] : ($academicYearId2 ? Hashids::decode($academicYearId2) : []);
                $decodedExamType2 = is_numeric($examTypeId2) ? [$examTypeId2] : ($examTypeId2 ? Hashids::decode($examTypeId2) : []);
                $decodedClass = is_numeric($classId) ? [$classId] : ($classId ? Hashids::decode($classId) : []);
                $decodedStream = is_numeric($streamId) ? [$streamId] : ($streamId ? Hashids::decode($streamId) : []);

                $request->merge([
                    'academic_year_id_1' => !empty($decodedAcademicYear1) ? $decodedAcademicYear1[0] : null,
                    'exam_type_id_1' => !empty($decodedExamType1) ? $decodedExamType1[0] : null,
                    'academic_year_id_2' => !empty($decodedAcademicYear2) ? $decodedAcademicYear2[0] : null,
                    'exam_type_id_2' => !empty($decodedExamType2) ? $decodedExamType2[0] : null,
                    'class_id' => !empty($decodedClass) ? $decodedClass[0] : null,
                    'stream_id' => !empty($decodedStream) ? $decodedStream[0] : null,
                ]);
            } catch (\Exception $e) {
                $request->merge([
                    'academic_year_id_1' => null,
                    'exam_type_id_1' => null,
                    'academic_year_id_2' => null,
                    'exam_type_id_2' => null,
                    'class_id' => null,
                    'stream_id' => null,
                ]);
            }

            // Get filter options
            $classes = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            $streams = \App\Models\School\Stream::where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            $academicYears = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
                ->orderBy('year_name')
                ->get();

            // Get current academic year
            $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
                ->where('is_current', true)
                ->first();

            $examTypes = \App\Models\SchoolExamType::where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            // Get comparative data
            $comparativeData = $this->getComparativeSubjectPerformanceData($request, $branchId);

            // Handle PDF export
            if ($request->export == 'pdf') {
                return $this->exportComparativeSubjectPerformancePdf($request);
            }

            // Handle Excel export
            if ($request->export == 'excel') {
                return $this->exportComparativeSubjectPerformanceExcel($request);
            }

            // Extract grade letters from comparative data
            $gradeLetters = $comparativeData['grade_letters'] ?? ['A', 'B', 'C', 'D', 'F'];

            return view('school.reports.comparative-subject-performance', compact(
                'classes',
                'streams',
                'academicYears',
                'currentAcademicYear',
                'examTypes',
                'comparativeData',
                'selectedAcademicYear1',
                'selectedAcademicYear2',
                'selectedExamType1',
                'selectedExamType2',
                'selectedClass',
                'selectedStream',
                'gradeLetters'
            ));
        } catch (\Exception $e) {
            \Log::error('Comparative Subject Performance error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);

            return redirect()->back()->with('error', 'An error occurred while generating the report. Please try again.');
        }
    }

    /**
     * Display overall analysis report
     */
    public function overallAnalysis(Request $request)
    {
        $branchId = session('branch_id');

        // Store original hashed values for form population
        $selectedAcademicYear = $request->academic_year_id;
        $selectedExamType = $request->exam_type_id;
        $selectedClass = $request->class_id;
        $selectedStream = $request->stream_id;

        // Decode hashed IDs - handle empty decode results properly
        $decodeHash = function($hash) {
            if (!$hash) return null;
            if (is_numeric($hash)) return (int)$hash;
            $decoded = Hashids::decode($hash);
            return !empty($decoded) ? $decoded[0] : null;
        };

        $request->merge([
            'academic_year_id' => $decodeHash($request->academic_year_id),
            'exam_type_id' => $decodeHash($request->exam_type_id),
            'class_id' => $decodeHash($request->class_id),
            'stream_id' => $decodeHash($request->stream_id),
        ]);

        // Get filter options
        $classes = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $streams = \App\Models\School\Stream::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $academicYears = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->orderBy('year_name')
            ->get();

        // Get current academic year
        $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->where('is_current', true)
            ->first();

        $examTypes = \App\Models\SchoolExamType::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        // Get overall analysis data
        $analysisData = $this->getOverallAnalysisData($request, $branchId);

        // Handle PDF export
        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportOverallAnalysisPdf($request, $analysisData, $classes, $streams, $academicYears, $examTypes);
        }

        // Handle Excel export
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportOverallAnalysisExcel($request, $analysisData);
        }

        return view('school.reports.overall-analysis', compact(
            'classes',
            'streams',
            'academicYears',
            'currentAcademicYear',
            'examTypes',
            'analysisData',
            'selectedAcademicYear',
            'selectedExamType',
            'selectedClass',
            'selectedStream'
        ));
    }

    /**
     * Display performance by class report
     */
    public function performanceByClass(Request $request)
    {
        $branchId = session('branch_id');

        // Store original hashed values for form population
        $selectedAcademicYear = $request->academic_year_id;
        $selectedExamType = $request->exam_type_id;
        $selectedClass = $request->class_id;
        $selectedStream = $request->stream_id;

        // Decode hashed IDs - handle empty decode results properly
        $decodeHash = function($hash) {
            if (!$hash) return null;
            if (is_numeric($hash)) return (int)$hash;
            $decoded = Hashids::decode($hash);
            return !empty($decoded) ? $decoded[0] : null;
        };

        $request->merge([
            'academic_year_id' => $decodeHash($request->academic_year_id),
            'exam_type_id' => $decodeHash($request->exam_type_id),
            'class_id' => $decodeHash($request->class_id),
            'stream_id' => $decodeHash($request->stream_id),
        ]);

        // Get filter options
        $classes = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $streams = \App\Models\School\Stream::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $academicYears = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->orderBy('year_name')
            ->get();

        // Get current academic year
        $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->where('is_current', true)
            ->first();

        $examTypes = \App\Models\SchoolExamType::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        // Get performance by class data
        $performanceData = $this->getPerformanceByClassData($request, $branchId);

        // Handle PDF export
        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportPerformanceByClassPdf($request, $performanceData, $classes, $streams, $academicYears, $examTypes);
        }

        // Handle Excel export
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportPerformanceByClassExcel($request, $performanceData);
        }

        return view('school.reports.performance-by-class', compact(
            'classes',
            'streams',
            'academicYears',
            'currentAcademicYear',
            'examTypes',
            'performanceData',
            'selectedAcademicYear',
            'selectedExamType',
            'selectedClass',
            'selectedStream'
        ));
    }

    /**
     * Display subject-wise analysis report
     */
    public function subjectWiseAnalysis(Request $request)
    {
        try {
        \Log::info('Subject-Wise Analysis method called', [
            'user_id' => auth()->id(),
            'request_all' => $request->all(),
            'session_branch' => session('branch_id')
        ]);
        \Log::info('Subject-Wise Analysis accessed', [
            'user_id' => auth()->id(),
            'request_params' => $request->all(),
            'branch_id' => session('branch_id')
        ]);
        $branchId = session('branch_id');

        // Store original hashed values for form population
        $selectedAcademicYear = $request->academic_year_id;
        $selectedExamType = $request->exam_type_id;
        $selectedClass = $request->class_id;
        $selectedStream = $request->stream_id;

        // Decode hashed IDs or use plain numeric IDs
        try {
            $academicYearId = $request->academic_year_id;
            $examTypeId = $request->exam_type_id;
            $classId = $request->class_id;
            $streamId = $request->stream_id;

            // If numeric, use as is; else decode
            $decodedAcademicYear = is_numeric($academicYearId) ? [$academicYearId] : ($academicYearId ? Hashids::decode($academicYearId) : []);
            $decodedExamType = is_numeric($examTypeId) ? [$examTypeId] : ($examTypeId ? Hashids::decode($examTypeId) : []);
            $decodedClass = is_numeric($classId) ? [$classId] : ($classId ? Hashids::decode($classId) : []);
            $decodedStream = is_numeric($streamId) ? [$streamId] : ($streamId ? Hashids::decode($streamId) : []);

            $request->merge([
                'academic_year_id' => !empty($decodedAcademicYear) ? $decodedAcademicYear[0] : null,
                'exam_type_id' => !empty($decodedExamType) ? $decodedExamType[0] : null,
                'class_id' => !empty($decodedClass) ? $decodedClass[0] : null,
                'stream_id' => !empty($decodedStream) ? $decodedStream[0] : null,
            ]);

            \Log::info('Decoded request parameters', [
                'original_academic_year' => $request->academic_year_id,
                'original_exam_type' => $request->exam_type_id,
                'original_class' => $request->class_id,
                'decoded_academic_year' => !empty($decodedAcademicYear) ? $decodedAcademicYear[0] : null,
                'decoded_exam_type' => !empty($decodedExamType) ? $decodedExamType[0] : null,
                'decoded_class' => !empty($decodedClass) ? $decodedClass[0] : null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Hashids decoding error in subjectWiseAnalysis', [
                'error' => $e->getMessage(),
                'request_params' => $request->all()
            ]);
            // Reset to null if decoding fails
            $request->merge([
                'academic_year_id' => null,
                'exam_type_id' => null,
                'class_id' => null,
                'stream_id' => null,
            ]);
        }

        // Get filter options
        $classes = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $streams = \App\Models\School\Stream::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $academicYears = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->orderBy('year_name')
            ->get();

        // Get current academic year
        $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->where('is_current', true)
            ->first();

        $examTypes = \App\Models\SchoolExamType::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        // Get subject-wise analysis data
        $subjectWiseData = $this->getSubjectWiseAnalysisData($request, $branchId);

        // Handle Excel export
        if ($request->export == 'excel') {
            return Excel::download(new class($subjectWiseData) implements FromArray {
                protected $data;

                public function __construct($data) {
                    $this->data = $data;
                }

                public function array(): array {
                    $rows = [];

                    // Header row 1
                    $header1 = ['Subject'];
                    $header2 = [''];

                    if (!empty($this->data['subjects']) && !empty($this->data['subjects'][0]['grade_breakdown'])) {
                        foreach (array_keys($this->data['subjects'][0]['grade_breakdown']) as $grade) {
                            $header1[] = $grade;
                            $header1[] = '';
                            $header1[] = '';
                            $header2[] = 'F';
                            $header2[] = 'M';
                            $header2[] = 'Total';
                        }
                    }

                    $header1[] = 'Totals';
                    $header1[] = '';
                    $header1[] = '';
                    $header2[] = 'F';
                    $header2[] = 'M';
                    $header2[] = 'Total';

                    $rows[] = $header1;
                    $rows[] = $header2;

                    // Data rows
                    foreach ($this->data['subjects'] as $subject) {
                        $row = [$subject['subject_name']];

                        if (!empty($subject['grade_breakdown'])) {
                            foreach ($subject['grade_breakdown'] as $counts) {
                                $row[] = $counts['female'];
                                $row[] = $counts['male'];
                                $row[] = $counts['total'];
                            }
                        }

                        $row[] = $subject['totals']['female'];
                        $row[] = $subject['totals']['male'];
                        $row[] = $subject['totals']['total'];

                        $rows[] = $row;
                    }

                    // Absent students
                    if (!empty($this->data['absentStudents'])) {
                        $rows[] = [];
                        $rows[] = ['STUDENTS ABSENT FROM EXAMINATIONS'];
                        $rows[] = ['Student Name', 'Class', 'Stream', 'Absent Subjects'];

                        foreach ($this->data['absentStudents'] as $absent) {
                            $rows[] = [
                                $absent['student']->first_name . ' ' . $absent['student']->last_name,
                                $absent['student']->class->name ?? '-',
                                $absent['student']->stream->name ?? '-',
                                !empty($absent['absent_subjects']) ? implode(', ', $absent['absent_subjects']) : 'ABSENT'
                            ];
                        }
                    }

                    return $rows;
                }
            }, 'subject-wise-analysis.xlsx');
        }

        // Extract grade letters from subject wise data
        $gradeLetters = $subjectWiseData['grade_letters'] ?? ['A', 'B', 'C', 'D', 'F'];

        return view('school.reports.subject-wise-analysis', compact(
            'classes',
            'streams',
            'academicYears',
            'currentAcademicYear',
            'examTypes',
            'subjectWiseData',
            'selectedAcademicYear',
            'selectedExamType',
            'selectedClass',
            'selectedStream',
            'gradeLetters'
        ));
        } catch (\Exception $e) {
            \Log::error('Subject-Wise Analysis error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);

            return redirect()->back()->with('error', 'An error occurred while generating the report. Please try again.');
        }
    }

    /**
     * Export subject-wise analysis report
     */
    public function exportSubjectWiseAnalysis(Request $request)
    {
        try {
            $branchId = session('branch_id');

            // Decode hashed IDs or use plain numeric IDs
            try {
                $academicYearId = $request->academic_year_id;
                $examTypeId = $request->exam_type_id;
                $classId = $request->class_id;
                $streamId = $request->stream_id;

                // If numeric, use as is; else decode
                $decodedAcademicYear = is_numeric($academicYearId) ? [$academicYearId] : ($academicYearId ? Hashids::decode($academicYearId) : []);
                $decodedExamType = is_numeric($examTypeId) ? [$examTypeId] : ($examTypeId ? Hashids::decode($examTypeId) : []);
                $decodedClass = is_numeric($classId) ? [$classId] : ($classId ? Hashids::decode($classId) : []);
                $decodedStream = is_numeric($streamId) ? [$streamId] : ($streamId ? Hashids::decode($streamId) : []);

                $request->merge([
                    'academic_year_id' => !empty($decodedAcademicYear) ? $decodedAcademicYear[0] : null,
                    'exam_type_id' => !empty($decodedExamType) ? $decodedExamType[0] : null,
                    'class_id' => !empty($decodedClass) ? $decodedClass[0] : null,
                    'stream_id' => !empty($decodedStream) ? $decodedStream[0] : null,
                ]);
            } catch (\Exception $e) {
                $request->merge([
                    'academic_year_id' => null,
                    'exam_type_id' => null,
                    'class_id' => null,
                    'stream_id' => null,
                ]);
            }

            // Get subject-wise analysis data
            $subjectWiseData = $this->getSubjectWiseAnalysisData($request, $branchId);

            // Get company info
            $company = auth()->user()->company;

            // Get filter info for display
            $academicYear = $request->academic_year_id ? \App\Models\School\AcademicYear::find($request->academic_year_id) : null;
            $examType = $request->exam_type_id ? \App\Models\SchoolExamType::find($request->exam_type_id) : null;
            $selectedClass = $request->class_id ? \App\Models\School\Classe::find($request->class_id) : null;

            $generatedAt = now();

            // Extract grade letters from subject wise data
            $gradeLetters = $subjectWiseData['grade_letters'] ?? ['A', 'B', 'C', 'D', 'F'];

            $pdf = \PDF::loadView('school.reports.exports.subject-wise-analysis', compact(
                'subjectWiseData',
                'company',
                'academicYear',
                'examType',
                'selectedClass',
                'generatedAt',
                'gradeLetters'
            ));

            $pdf->setPaper('a4', 'landscape');
            $filename = 'subject-wise-analysis-' . date('Y-m-d-H-i-s') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Subject-Wise Analysis Export error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An error occurred while exporting the report. Please try again.');
        }
    }

    private function getExaminationResultsData(Request $request, $branchId)
    {
        // Get exam type and academic year filter values FIRST (before student query)
        $examTypeId = $request->exam_type_id;
        $academicYearId = $request->academic_year_id;

        // Log for debugging (remove in production if needed)
        \Log::info('Examination Results Data Retrieval', [
            'exam_type_id' => $examTypeId,
            'academic_year_id' => $academicYearId,
            'class_id' => $request->class_id,
            'stream_id' => $request->stream_id,
            'branch_id' => $branchId,
        ]);

        // Get grade scale for the academic year (can be null if no grade scale exists)
        $gradeScale = null;
        if ($academicYearId) {
            $gradeScale = \App\Models\SchoolGradeScale::active()
                ->where('academic_year_id', $academicYearId)
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->with('grades')
                ->first();
        }
        
        // Get grade letters from grade scale or use default
        $gradeLetters = $this->getGradeLetters($gradeScale);

        // Early return if required parameters are missing
        if (!$examTypeId || !$academicYearId) {
            \Log::warning('Examination Results: Missing required parameters', [
                'exam_type_id' => $examTypeId,
                'academic_year_id' => $academicYearId,
            ]);
            return [
                'results' => [],
                'absentStudents' => [],
                'subjectTotals' => [],
                'subjectAverages' => [],
                'subjectGrades' => [],
                'subjectPositions' => [],
                'subjectPerformance' => [],
                'classTotal' => 0,
                'classAverage' => 0,
                'classGrade' => '',
                'subjects' => collect(),
                'gradeLetters' => $gradeLetters,
                'gradeScale' => $gradeScale
            ];
        }

        // Get students based on filters
        $query = \App\Models\School\Student::with(['class', 'stream', 'academicYear'])
            ->where('branch_id', $branchId)
            ->where('status', 'active');

        // Apply filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('stream_id')) {
            $query->where('stream_id', $request->stream_id);
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $students = $query->get();
        
        \Log::info('Examination Results: Students found', [
            'count' => $students->count(),
        ]);

        // Get subjects that have marks for this exam type and academic year
        $examClassAssignmentsQuery = \App\Models\ExamClassAssignment::where('exam_type_id', $examTypeId)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        // Filter by class if selected
        if ($request->filled('class_id')) {
            $examClassAssignmentsQuery->where('class_id', $request->class_id);
        }

        $examClassAssignments = $examClassAssignmentsQuery->with('subject')->get();

        // Get subjects ordered by subject group sort order if class is selected
        if ($request->filled('class_id')) {
            $subjectGroups = \App\Models\School\SubjectGroup::where('class_id', $request->class_id)
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                })
                ->where('is_active', true)
                ->with(['subjects' => function ($query) {
                    $query->orderBy('subject_subject_group.sort_order')
                          ->orderBy('subjects.name');
                }])
                ->get();

            if ($subjectGroups->isNotEmpty()) {
                $orderedSubjects = collect();
                foreach ($subjectGroups as $subjectGroup) {
                    foreach ($subjectGroup->subjects as $subject) {
                        if ($examClassAssignments->contains('subject_id', $subject->id)) {
                            $orderedSubjects->push($subject);
                        }
                    }
                }
                // Add any remaining subjects not in subject groups
                $subjectsFromAssignments = $examClassAssignments->pluck('subject')->filter()->unique('id');
                foreach ($subjectsFromAssignments as $subject) {
                    if (!$orderedSubjects->contains('id', $subject->id)) {
                        $orderedSubjects->push($subject);
                    }
                }
                $subjects = $orderedSubjects->unique('id');
            } else {
                // Fallback to original logic
                $subjects = $examClassAssignments->pluck('subject')->filter()->unique('id')->sortBy('name');
            }
        } else {
            $subjects = $examClassAssignments->pluck('subject')->filter()->unique('id')->sortBy('name');
        }

        // Get all marks for these assignments and students
        $assignmentIds = $examClassAssignments->pluck('id');
        $studentIds = $students->pluck('id');

        // Get exam registrations to check attendance status
        $examRegistrations = \App\Models\SchoolExamRegistration::whereIn('exam_class_assignment_id', $assignmentIds)
            ->whereIn('student_id', $studentIds)
            ->where('exam_type_id', $examTypeId)
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->keyBy(function($registration) {
                return $registration->student_id . '-' . $registration->exam_class_assignment_id;
            });

        $marks = \App\Models\SchoolExamMark::whereIn('exam_class_assignment_id', $assignmentIds)
            ->whereIn('student_id', $studentIds)
            ->with(['examClassAssignment.subject'])
            ->get()
            ->keyBy(function($mark) {
                return $mark->student_id . '-' . $mark->exam_class_assignment_id;
            });

        $results = [];
        $absentStudents = [];
        $subjectTotals = [];
        $subjectCounts = [];
        $classTotal = 0;
        $classCount = 0;
        $classAverageSum = 0;

        foreach ($students as $student) {
            $studentMarks = [];
            $total = 0;
            $subjectCount = 0;
            $hasAllSubjects = true;
            $absentSubjects = [];

            foreach ($subjects as $subject) {
                $assignment = $examClassAssignments->first(function($assignment) use ($subject) {
                    return $assignment->subject_id === $subject->id;
                });

                if ($assignment) {
                    $markKey = $student->id . '-' . $assignment->id;
                    $registrationKey = $student->id . '-' . $assignment->id;
                    
                    $mark = $marks->get($markKey);
                    $registration = $examRegistrations->get($registrationKey);
                    
                    // Check registration status
                    if ($registration) {
                        if ($registration->status === 'absent') {
                            $markValue = 'ABS';
                            $studentMarks[$subject->id] = $markValue;
                            $absentSubjects[] = $subject->name;
                            $hasAllSubjects = false;

                            // Count absent students as 0 for subject averages
                            if (!isset($subjectTotals[$subject->id])) {
                                $subjectTotals[$subject->id] = 0;
                                $subjectCounts[$subject->id] = 0;
                            }
                            $subjectTotals[$subject->id] += 0;
                            $subjectCounts[$subject->id]++;
                        } elseif ($registration->status === 'exempted') {
                            $markValue = 'EXEMPT';
                            $studentMarks[$subject->id] = $markValue;
                            // Exempted students still count as having participated but not in averages
                        } elseif (in_array($registration->status, ['registered', 'attended'])) {
                            $markValue = $mark ? $mark->marks_obtained : null;
                            $studentMarks[$subject->id] = $markValue;

                            // Always count registered students for subject averages
                            if (!isset($subjectTotals[$subject->id])) {
                                $subjectTotals[$subject->id] = 0;
                                $subjectCounts[$subject->id] = 0;
                            }

                            if ($markValue !== null) {
                                $total += $markValue;
                                $subjectCount++;
                                $subjectTotals[$subject->id] += $markValue;
                            } else {
                                // No mark but registered - count as 0 for average calculation
                                $subjectTotals[$subject->id] += 0;
                            }
                            $subjectCounts[$subject->id]++;
                        } else {
                            // Not registered - treat as absent
                            $studentMarks[$subject->id] = 'ABS';
                            $absentSubjects[] = $subject->name;
                            $hasAllSubjects = false;
                        }
                    } else {
                        // No registration record - check if there's a mark (legacy data)
                        $markValue = $mark ? $mark->marks_obtained : null;
                        $studentMarks[$subject->id] = $markValue;
                        
                        if ($markValue !== null) {
                            $total += $markValue;
                            $subjectCount++;

                            if (!isset($subjectTotals[$subject->id])) {
                                $subjectTotals[$subject->id] = 0;
                                $subjectCounts[$subject->id] = 0;
                            }
                            $subjectTotals[$subject->id] += $markValue;
                            $subjectCounts[$subject->id]++;
                        } else {
                            // No mark and no registration - absent
                            $studentMarks[$subject->id] = 'ABS';
                            $absentSubjects[] = $subject->name;
                            $hasAllSubjects = false;
                        }
                    }
                }
            }

            // Only include students who participated in ALL subjects
            if ($hasAllSubjects && $subjectCount > 0) {
                $average = $subjectCount > 0 ? round($total / $subjectCount, 2) : 0;
                $grade = $gradeScale ? $this->calculateGradeFromScale($average, $gradeScale) : $this->calculateGrade($average);
                $remark = $gradeScale ? $this->getRemarkFromScale($grade, $gradeScale) : $this->getRemark($grade);

                $results[] = [
                    'student' => $student,
                    'marks' => $studentMarks,
                    'total' => $total,
                    'average' => $average,
                    'grade' => $grade,
                    'remark' => $remark,
                    'position' => 0 // Will be set after sorting
                ];

                $classTotal += $total;
                $classCount++;
                $classAverageSum += $average;
            } else {
                // Student was absent from some exams
                $absentStudents[] = [
                    'student' => $student,
                    'absent_subjects' => $absentSubjects,
                    'marks' => $studentMarks
                ];
            }
        }

        // Sort by total descending and assign positions
        usort($results, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        $position = 1;
        foreach ($results as &$result) {
            $result['position'] = $position++;
        }

        // Sort by position ascending for display (position 1 first)
        usort($results, function($a, $b) {
            return $a['position'] <=> $b['position'];
        });

        // Calculate subject averages
        $subjectAverages = [];
        $subjectGrades = [];
        $subjectPositions = [];
        foreach ($subjects as $subject) {
            $subjectId = $subject->id;
            $avg = isset($subjectCounts[$subjectId]) && $subjectCounts[$subjectId] > 0
                ? round($subjectTotals[$subjectId] / $subjectCounts[$subjectId], 2)
                : 0;
            $subjectAverages[$subjectId] = $avg;
            $subjectGrades[$subjectId] = $gradeScale ? $this->calculateGradeFromScale($avg, $gradeScale) : $this->calculateGrade($avg);
        }

        // Calculate positions for subjects (higher average = better position)
        // Sort subjects by average descending (highest first)
        $sortedSubjects = $subjects->sortByDesc(function($subject) use ($subjectAverages) {
            return $subjectAverages[$subject->id] ?? 0;
        });
        
        $pos = 1;
        foreach ($sortedSubjects as $subject) {
            $subjectPositions[$subject->id] = $pos++;
        }

        // Calculate subject performance analysis
        $streamId = $request->stream_id;
        $subjectPerformance = [];

        if ($streamId) {
            // Get subject teachers for the selected stream
            $subjectTeachers = \App\Models\School\SubjectTeacher::where('stream_id', $streamId)
                ->where('academic_year_id', $academicYearId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                })
                ->where('is_active', true)
                ->with(['employee', 'subject'])
                ->get()
                ->keyBy('subject_id');

            // Group analysis by stream (only the selected stream)
            $streamAnalysis = [];
            foreach ($subjects as $subject) {
                $subjectId = $subject->id;
                $subjectMarks = []; // All marks including ABS/EXEMPT for proper counting
                
                foreach ($results as $result) {
                    // Only include students from the selected stream
                    if ($result['student']->stream_id == $streamId) {
                        $mark = $result['marks'][$subjectId] ?? null;
                        // Include all marks (including ABS/EXEMPT) for proper GPA calculation
                        $subjectMarks[] = $mark;
                    }
                }
                
                $gradeCounts = [];
                if ($gradeScale) {
                    foreach ($gradeScale->grades as $grade) {
                        $gradeCounts[$grade->grade_letter] = 0;
                    }
                } else {
                    $gradeCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
                }
                
                $totalMarks = 0;
                $markCount = 0;
                
                // Count grades and calculate average (only for students who sat)
                foreach ($subjectMarks as $mark) {
                    // Skip absent and exempted for grade counts and averages
                    if ($mark === 'ABS' || $mark === 'EXEMPT' || $mark === null) {
                        continue;
                    }
                    
                    $grade = $gradeScale ? $this->calculateGradeFromScale($mark, $gradeScale) : $this->calculateGrade($mark);
                    if (isset($gradeCounts[$grade])) {
                        $gradeCounts[$grade]++;
                    }
                    $totalMarks += $mark;
                    $markCount++;
                }
                
                $subjectAverage = $markCount > 0 ? round($totalMarks / $markCount, 2) : 0;
                
                // Calculate Subject GPA using NECTA method: Sum of grade points / Number of students who sat
                // This method automatically excludes ABS/EXEMPT students
                $subjectGPA = $this->calculateSubjectGPA($subjectMarks, $gradeScale);
                
                // Assign subject grade based on GPA using grade scale
                $subjectGrade = $this->getSubjectGradeFromGPA($subjectGPA, $gradeScale);
                $competencyLevel = $this->getCompetencyLevel($subjectGPA, $gradeScale);
                
                // Get teacher name
                $teacherName = '-';
                if (isset($subjectTeachers[$subjectId])) {
                    $teacher = $subjectTeachers[$subjectId]->employee;
                    $teacherName = $teacher ? $teacher->first_name . ' ' . $teacher->last_name : '-';
                }
                
                $streamAnalysis[$subjectId] = [
                    'subject' => $subject,
                    'teacher' => $teacherName,
                    'gradeCounts' => $gradeCounts,
                    'total' => $markCount,
                    'average' => $subjectAverage,
                    'gpa' => $subjectGPA,
                    'subjectGrade' => $subjectGrade,
                    'competencyLevel' => $competencyLevel
                ];
            }
            
            $subjectPerformance = [
                'by_stream' => true,
                'streams' => [
                    $streamId => [
                        'stream' => \App\Models\School\Stream::find($streamId),
                        'subjects' => $streamAnalysis
                    ]
                ]
            ];
        } else {
            // Get all subject teachers for the class (all streams)
            $classId = $request->class_id;
            if (!$classId) {
                // If no class is selected, skip subject performance analysis
                $subjectPerformance = [];
            } else {
                $subjectTeachers = \App\Models\School\SubjectTeacher::where('class_id', $classId)
                    ->where('academic_year_id', $academicYearId)
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                    })
                    ->where('is_active', true)
                    ->with(['employee', 'subject', 'stream'])
                    ->get()
                    ->groupBy('stream_id');

                // Group analysis by all streams in the class
                $allStreamsAnalysis = [];
                $streamsInClass = \App\Models\School\Classe::find($classId)->streams()
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                    })
                    ->where('is_active', true)
                    ->get();

            foreach ($streamsInClass as $stream) {
                $streamId = $stream->id;
                $streamAnalysis = [];

                // Get teachers for this stream
                $streamTeachers = isset($subjectTeachers[$streamId]) ? $subjectTeachers[$streamId]->keyBy('subject_id') : collect();

                foreach ($subjects as $subject) {
                    $subjectId = $subject->id;
                    $subjectMarks = []; // All marks including ABS/EXEMPT for proper counting

                    foreach ($results as $result) {
                        // Only include students from this stream
                        if ($result['student']->stream_id == $streamId) {
                            $mark = $result['marks'][$subjectId] ?? null;
                            // Include all marks (including ABS/EXEMPT) for proper GPA calculation
                            $subjectMarks[] = $mark;
                        }
                    }

                    $gradeCounts = [];
                    if ($gradeScale) {
                        foreach ($gradeScale->grades as $grade) {
                            $gradeCounts[$grade->grade_letter] = 0;
                        }
                    } else {
                        $gradeCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
                    }

                    $totalMarks = 0;
                    $markCount = 0;

                    // Count grades and calculate average (only for students who sat)
                    foreach ($subjectMarks as $mark) {
                        // Skip absent and exempted for grade counts and averages
                        if ($mark === 'ABS' || $mark === 'EXEMPT' || $mark === null) {
                            continue;
                        }
                        
                        $grade = $gradeScale ? $this->calculateGradeFromScale($mark, $gradeScale) : $this->calculateGrade($mark);
                        if (isset($gradeCounts[$grade])) {
                            $gradeCounts[$grade]++;
                        }
                        $totalMarks += $mark;
                        $markCount++;
                    }

                    $subjectAverage = $markCount > 0 ? round($totalMarks / $markCount, 2) : 0;
                    
                    // Calculate Subject GPA using NECTA method: Sum of grade points / Number of students who sat
                    // This method automatically excludes ABS/EXEMPT students
                    $subjectGPA = $this->calculateSubjectGPA($subjectMarks, $gradeScale);
                    
                    // Assign subject grade based on GPA using grade scale
                    $subjectGrade = $this->getSubjectGradeFromGPA($subjectGPA, $gradeScale);
                    $competencyLevel = $this->getCompetencyLevel($subjectGPA, $gradeScale);

                    // Get teacher name and stream
                    $teacherName = '-';
                    $teacherStream = '';
                    if (isset($streamTeachers[$subjectId])) {
                        $teacherAssignment = $streamTeachers[$subjectId];
                        $teacher = $teacherAssignment->employee;
                        $teacherName = $teacher ? $teacher->first_name . ' ' . $teacher->last_name : '-';
                        $teacherStream = $teacherAssignment->stream ? $teacherAssignment->stream->name : '';
                    }

                    $streamAnalysis[$subjectId] = [
                        'subject' => $subject,
                        'teacher' => $teacherName,
                        'teacher_stream' => $teacherStream,
                        'gradeCounts' => $gradeCounts,
                        'total' => $markCount,
                        'average' => $subjectAverage,
                        'gpa' => $subjectGPA,
                        'subjectGrade' => $subjectGrade,
                        'competencyLevel' => $competencyLevel
                    ];
                }

                if (!empty($streamAnalysis)) {
                    $allStreamsAnalysis[$streamId] = [
                        'stream' => $stream,
                        'subjects' => $streamAnalysis
                    ];
                }
            }

            $subjectPerformance = [
                'by_stream' => true,
                'streams' => $allStreamsAnalysis
            ];
            }
        }

        $classAverage = $classCount > 0 ? round($classAverageSum / $classCount, 2) : 0;
        $classGrade = $gradeScale ? $this->calculateGradeFromScale($classAverage, $gradeScale) : $this->calculateGrade($classAverage);

        // Create subject mapping for Excel export
        $subjectMapping = [];
        foreach ($subjects as $subject) {
            $code = $subject->short_name ?? substr($subject->name, 0, 6);
            $subjectMapping[$code] = [
                'name' => $subject->name,
                'id' => $subject->id
            ];
        }

        return [
            'results' => $results,
            'absentStudents' => $absentStudents,
            'subjectTotals' => $subjectTotals,
            'subjectAverages' => $subjectAverages,
            'subjectGrades' => $subjectGrades,
            'subjectPositions' => $subjectPositions,
            'subjectPerformance' => $subjectPerformance,
            'gradeScale' => $gradeScale,
            'gradeLetters' => $gradeLetters,
            'classTotal' => $classTotal,
            'classAverage' => $classAverage,
            'classGrade' => $classGrade,
            'subjects' => $subjects,
            'subjectMapping' => $subjectMapping
        ];
    }

    private function calculateGrade($average)
    {
        if ($average >= 90) return 'A';
        if ($average >= 80) return 'B';
        if ($average >= 70) return 'C';
        if ($average >= 60) return 'D';
        return 'E';
    }

    private function getRemark($grade)
    {
        $remarks = [
            'A' => 'EXCELLENT',
            'B' => 'VERY GOOD',
            'C' => 'AVERAGE',
            'D' => 'BELOW AVERAGE',
            'F' => 'UNSATISFACTORY'
        ];

        return $remarks[$grade] ?? 'UNKNOWN';
    }

    private function calculateGPA($average)
    {
        if ($average >= 90) return 4.0;
        if ($average >= 85) return 3.7;
        if ($average >= 80) return 3.3;
        if ($average >= 75) return 3.0;
        if ($average >= 70) return 2.7;
        if ($average >= 65) return 2.3;
        if ($average >= 60) return 2.0;
        if ($average >= 55) return 1.7;
        if ($average >= 50) return 1.3;
        return 0.0;
    }

    /**
     * Get competency level (remark) from grade scale based on GPA
     * Uses the remark field from the grade whose grade_point matches the GPA
     */
    private function getCompetencyLevel($gpa, $gradeScale = null)
    {
        if ($gradeScale && $gradeScale->grades) {
            // Get all grades sorted by grade_point
            $grades = $gradeScale->grades->sortBy('grade_point');
            
            // Find the grade whose grade_point is closest to the GPA
            $closestGrade = null;
            $minDifference = PHP_FLOAT_MAX;
            
            foreach ($grades as $grade) {
                if ($grade->grade_point !== null) {
                    $difference = abs($gpa - $grade->grade_point);
                    if ($difference < $minDifference) {
                        $minDifference = $difference;
                        $closestGrade = $grade;
                    }
                }
            }
            
            if ($closestGrade && $closestGrade->remarks) {
                return $closestGrade->remarks;
            }
        }

        // Fallback: use hardcoded competency levels if no grade scale
        if ($gpa >= 3.7) return 'EXCELLENT';
        if ($gpa >= 3.3) return 'VERY GOOD';
        if ($gpa >= 2.7) return 'GOOD';
        if ($gpa >= 2.0) return 'SATISFACTORY';
        if ($gpa >= 1.0) return 'NEEDS IMPROVEMENT';
        return 'POOR';
    }

    private function calculateGradeFromScale($mark, $gradeScale)
    {
        if (!$gradeScale) {
            return $this->calculateGrade($mark);
        }

        $grade = $gradeScale->getGradeForMark($mark);
        if ($grade) {
            return $grade->grade_letter;
        }

        // If no grade found in scale, fall back to default grading
        return $this->calculateGrade($mark);
    }

    private function getRemarkFromScale($gradeLetter, $gradeScale)
    {
        if (!$gradeScale) {
            return $this->getRemark($gradeLetter);
        }

        $grade = $gradeScale->grades()->where('grade_letter', $gradeLetter)->first();
        return $grade ? $grade->remarks : 'UNKNOWN';
    }

    private function calculateGPAFromScale($mark, $gradeScale)
    {
        if (!$gradeScale) {
            return $this->calculateGPA($mark);
        }

        $grade = $gradeScale->getGradeForMark($mark);
        return $grade ? $grade->grade_point : 0.0;
    }

    /**
     * Get grade letters array from grade scale or return default
     */
    private function getGradeLetters($gradeScale)
    {
        if ($gradeScale && $gradeScale->grades) {
            return $gradeScale->grades->pluck('grade_letter')->toArray();
        }
        
        // Default grade letters if no grade scale
        return ['A', 'B', 'C', 'D', 'F'];
    }

    /**
     * Calculate Subject GPA using NECTA method
     * Formula: Sum of all grade points / Number of students who sat the subject
     * Only counts students who sat (excludes absent/exempted)
     */
    private function calculateSubjectGPA($marks, $gradeScale)
    {
        if (empty($marks)) {
            return 0.0;
        }

        $totalGradePoints = 0;
        $studentsWhoSat = 0;

        foreach ($marks as $mark) {
            // Skip absent and exempted students
            if ($mark === 'ABS' || $mark === 'EXEMPT' || $mark === null) {
                continue;
            }

            // Get grade for this mark
            $grade = null;
            if ($gradeScale) {
                $grade = $gradeScale->getGradeForMark($mark);
            } else {
                // Fallback: use default grading
                $gradeLetter = $this->calculateGrade($mark);
                // Get grade point from scale if available, otherwise use default
                $gradePoints = $this->getGradePointFromScale($gradeLetter, $gradeScale);
                $totalGradePoints += $gradePoints;
                $studentsWhoSat++;
                continue;
            }

            if ($grade && $grade->grade_point !== null) {
                $totalGradePoints += $grade->grade_point;
                $studentsWhoSat++;
            }
        }

        if ($studentsWhoSat == 0) {
            return 0.0;
        }

        return round($totalGradePoints / $studentsWhoSat, 4);
    }

    /**
     * Get grade point from grade scale for a given grade letter
     */
    private function getGradePointFromScale($gradeLetter, $gradeScale)
    {
        if ($gradeScale && $gradeScale->grades) {
            $grade = $gradeScale->grades->firstWhere('grade_letter', $gradeLetter);
            if ($grade && $grade->grade_point !== null) {
                return $grade->grade_point;
            }
        }

        // Fallback: default points if no grade scale
        $defaultPoints = [
            'A' => 1.0,
            'B' => 2.0,
            'C' => 3.0,
            'D' => 4.0,
            'F' => 5.0,
            'E' => 5.0
        ];

        return $defaultPoints[$gradeLetter] ?? 5.0;
    }

    /**
     * Assign subject grade based on Subject GPA using fixed ranges
     * Grade A: 1.00 to 1.599
     * Grade B: 1.6 to 2.599
     * Grade C: 2.6 to 3.599
     * Grade D: 3.6 to 4.599
     * Grade F: 4.6 to 9.00
     */
    private function getSubjectGradeFromGPA($gpa, $gradeScale = null)
    {
        // Use fixed GPA ranges regardless of grade scale
        if ($gpa >= 1.00 && $gpa <= 1.599) {
            return 'A';
        } elseif ($gpa >= 1.6 && $gpa <= 2.599) {
            return 'B';
        } elseif ($gpa >= 2.6 && $gpa <= 3.599) {
            return 'C';
        } elseif ($gpa >= 3.6 && $gpa <= 4.599) {
            return 'D';
        } elseif ($gpa >= 4.6 && $gpa <= 9.00) {
            return 'F';
        }

        // Fallback for edge cases
        if ($gpa < 1.00) return 'A';
        return 'F';
    }

    private function exportExaminationResultsPdf(Request $request, $examData, $classes, $streams, $academicYears, $examTypes)
    {
        $company = \App\Models\Company::find(session('company_id'));
        $generatedAt = now();

        // Get filter descriptions
        $filters = [];
        if ($request->filled('branch_id')) {
            $branch = \App\Models\Branch::find($request->branch_id);
            $filters['branch'] = $branch ? $branch->name : 'Unknown';
        }
        if ($request->filled('class_id')) {
            $class = $classes->find($request->class_id);
            $filters['class'] = $class ? $class->name : 'Unknown';
        }
        if ($request->filled('stream_id')) {
            $stream = $streams->find($request->stream_id);
            $filters['stream'] = $stream ? $stream->name : 'Unknown';
        }
        if ($request->filled('academic_year_id')) {
            $year = $academicYears->find($request->academic_year_id);
            $filters['academic_year'] = $year ? $year->year_name : 'Unknown';
        }
        if ($request->filled('exam_type_id')) {
            $examType = $examTypes->find($request->exam_type_id);
            $filters['exam_type'] = $examType ? $examType->name : 'Unknown';
        }

        $data = [
            'examData' => $examData,
            'company' => $company,
            'generatedAt' => $generatedAt,
            'filters' => $filters,
        ];

        // No need for logo_path as we handle it directly in the view like sales reports

        $pdf = \PDF::loadView('school.reports.exports.examination-results-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'margin-top' => 10,
                'margin-right' => 10,
                'margin-bottom' => 10,
                'margin-left' => 10,
                'dpi' => 150,
                'defaultFont' => 'Arial'
            ]);

        $filename = 'examination_results_report_' . date('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    private function exportExaminationResultsExcel(Request $request, $examData)
    {
        return \Excel::download(new \App\Exports\ExaminationResultsExport($examData, $request), 'examination_results_report_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    private function exportOverallAnalysisPdf(Request $request, $analysisData, $classes, $streams, $academicYears, $examTypes)
    {
        $company = \App\Models\Company::find(session('company_id'));
        $generatedAt = now();

        // Get filter descriptions
        $filters = [];
        if ($request->filled('class_id')) {
            $class = $classes->find($request->class_id);
            $filters['class'] = $class ? $class->name : 'Unknown';
        }
        if ($request->filled('stream_id')) {
            $stream = $streams->find($request->stream_id);
            $filters['stream'] = $stream ? $stream->name : 'Unknown';
        }
        if ($request->filled('academic_year_id')) {
            $year = $academicYears->find($request->academic_year_id);
            $filters['academic_year'] = $year ? $year->year_name : 'Unknown';
        }
        if ($request->filled('exam_type_id')) {
            $examType = $examTypes->find($request->exam_type_id);
            $filters['exam_type'] = $examType ? $examType->name : 'Unknown';
        }

        $data = [
            'analysis' => $analysisData['analysis'] ?? [],
            'subtotals' => $analysisData['subtotals'] ?? [],
            'grandTotal' => $analysisData['grandTotal'] ?? [],
            'classSubtotals' => $analysisData['classSubtotals'] ?? [],
            'gradeLetters' => $analysisData['gradeLetters'] ?? ['A', 'B', 'C', 'D', 'E'],
            'company' => $company,
            'generatedAt' => $generatedAt,
            'filters' => $filters,
        ];

        $pdf = \PDF::loadView('school.reports.exports.overall-analysis-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'margin-top' => 10,
                'margin-right' => 10,
                'margin-bottom' => 10,
                'margin-left' => 10,
                'dpi' => 150,
                'defaultFont' => 'Arial'
            ]);

        $filename = 'overall_analysis_report_' . date('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    private function exportOverallAnalysisExcel(Request $request, $analysisData)
    {
        return \Excel::download(new \App\Exports\OverallAnalysisExport($analysisData, $request), 'overall_analysis_report_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    private function exportPerformanceByClassPdf(Request $request, $performanceData, $classes, $streams, $academicYears, $examTypes)
    {
        $company = \App\Models\Company::find(session('company_id'));
        $generatedAt = now();
        $branchId = session('branch_id');

        // Get filter descriptions
        $filters = [];
        if ($branchId) {
            $branch = \App\Models\Branch::find($branchId);
            $filters['branch'] = $branch ? $branch->name : 'Unknown';
        }
        if ($request->filled('class_id')) {
            $class = $classes->find($request->class_id);
            $filters['class'] = $class ? $class->name : 'Unknown';
        }
        if ($request->filled('stream_id')) {
            $stream = $streams->find($request->stream_id);
            $filters['stream'] = $stream ? $stream->name : 'Unknown';
        }
        if ($request->filled('academic_year_id')) {
            $year = $academicYears->find($request->academic_year_id);
            $filters['academic_year'] = $year ? $year->year_name : 'Unknown';
        }
        if ($request->filled('exam_type_id')) {
            $examType = $examTypes->find($request->exam_type_id);
            $filters['exam_type'] = $examType ? $examType->name : 'Unknown';
        }

        $data = [
            'performanceData' => $performanceData,
            'company' => $company,
            'generatedAt' => $generatedAt,
            'filters' => $filters,
        ];

        $pdf = \PDF::loadView('school.reports.exports.performance-by-class-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'margin-top' => 10,
                'margin-right' => 10,
                'margin-bottom' => 10,
                'margin-left' => 10,
                'dpi' => 150,
                'defaultFont' => 'Arial'
            ]);

        $filename = 'performance_by_class_report_' . date('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    private function exportPerformanceByClassExcel(Request $request, $performanceData)
    {
        return \Excel::download(new \App\Exports\PerformanceByClassExport($performanceData, $request), 'performance_by_class_report_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    /**
     * Get overall analysis data aggregated by class and stream
     */
    private function getOverallAnalysisData(Request $request, $branchId)
    {
        // Get filter values
        $examTypeId = $request->exam_type_id;
        $academicYearId = $request->academic_year_id;

        if (!$examTypeId || !$academicYearId) {
            return [
                'analysis' => [],
                'subtotals' => [],
                'grandTotal' => []
            ];
        }

        // Get grade scale for the academic year
        $gradeScale = \App\Models\SchoolGradeScale::active()
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->with('grades')
            ->first();
        
        // Get grade letters from grade scale or use default
        $gradeLetters = $this->getGradeLetters($gradeScale);

        // Get all classes and streams
        $classesQuery = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        // Apply class filter if provided
        if ($request->filled('class_id')) {
            $classesQuery->where('id', $request->class_id);
        }

        $classes = $classesQuery->with(['streams' => function ($query) use ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                })
                ->where('is_active', true)
                ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $analysis = [];

        foreach ($classes as $class) {
            foreach ($class->streams as $stream) {
                // Get students in this stream and class
                $students = \App\Models\School\Student::where('class_id', $class->id)
                    ->where('stream_id', $stream->id)
                    ->where('academic_year_id', $academicYearId)
                    ->where('company_id', auth()->user()->company_id)
                    ->where('status', 'active')
                    ->get();

                if ($students->isEmpty()) {
                    continue;
                }

                // Get exam assignments for this class
                $examClassAssignments = \App\Models\ExamClassAssignment::where('exam_type_id', $examTypeId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('class_id', $class->id)
                    ->where('company_id', auth()->user()->company_id)
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->with('subject')
                    ->get();

                if ($examClassAssignments->isEmpty()) {
                    continue;
                }

                // Get marks for these students and assignments
                $assignmentIds = $examClassAssignments->pluck('id');
                $studentIds = $students->pluck('id');

                $marks = \App\Models\SchoolExamMark::whereIn('exam_class_assignment_id', $assignmentIds)
                    ->whereIn('student_id', $studentIds)
                    ->get()
                    ->keyBy(function($mark) {
                        return $mark->student_id . '-' . $mark->exam_class_assignment_id;
                    });

                // Get registrations
                $examRegistrations = \App\Models\SchoolExamRegistration::whereIn('exam_class_assignment_id', $assignmentIds)
                    ->whereIn('student_id', $studentIds)
                    ->where('exam_type_id', $examTypeId)
                    ->where('academic_year_id', $academicYearId)
                    ->get()
                    ->keyBy(function($registration) {
                        return $registration->student_id . '-' . $registration->exam_class_assignment_id;
                    });

                // Calculate results for each student
                $streamResults = [];
                $gradeCounts = [];
                foreach ($gradeLetters as $letter) {
                    $gradeCounts[$letter] = 0;
                }
                $totalMarks = 0;
                $totalStudents = 0;

                foreach ($students as $student) {
                    $studentMarks = [];
                    $total = 0;
                    $subjectCount = 0;
                    $hasAllSubjects = true;
                    $absentSubjects = [];

                    foreach ($examClassAssignments as $assignment) {
                        $markKey = $student->id . '-' . $assignment->id;
                        $registrationKey = $student->id . '-' . $assignment->id;

                        $mark = $marks->get($markKey);
                        $registration = $examRegistrations->get($registrationKey);

                        if ($registration) {
                            if ($registration->status === 'absent') {
                                $markValue = 'ABS';
                                $studentMarks[] = $markValue;
                                $absentSubjects[] = $assignment->subject->name ?? 'Unknown';
                                $hasAllSubjects = false;
                            } elseif ($registration->status === 'exempted') {
                                $markValue = 'EXEMPT';
                                $studentMarks[] = $markValue;
                                // Exempted students still count as having participated but not in averages
                            } elseif (in_array($registration->status, ['registered', 'attended'])) {
                                $markValue = $mark ? $mark->marks_obtained : null;
                                $studentMarks[] = $markValue;

                                if ($markValue !== null) {
                                    $total += $markValue;
                                    $subjectCount++;
                                }
                            } else {
                                // Other status - treat as absent
                                $markValue = 'ABS';
                                $studentMarks[] = $markValue;
                                $absentSubjects[] = $assignment->subject->name ?? 'Unknown';
                                $hasAllSubjects = false;
                            }
                        } else {
                            // No registration record - check if there's a mark (legacy data)
                            $markValue = $mark ? $mark->marks_obtained : null;
                            $studentMarks[] = $markValue;

                            if ($markValue !== null) {
                                $total += $markValue;
                                $subjectCount++;
                            } else {
                                // No mark and no registration - absent
                                $markValue = 'ABS';
                                $studentMarks[] = $markValue;
                                $absentSubjects[] = $assignment->subject->name ?? 'Unknown';
                                $hasAllSubjects = false;
                            }
                        }
                    }

                    // Only include students who participated in ALL subjects (same as examination results)
                    if ($hasAllSubjects && $subjectCount > 0) {
                        $average = round($total / $subjectCount, 2);
                        $grade = $gradeScale ? $this->calculateGradeFromScale($average, $gradeScale) : $this->calculateGrade($average);

                        $streamResults[] = [
                            'student' => $student,
                            'total' => $total,
                            'average' => $average,
                            'grade' => $grade
                        ];

                        if (isset($gradeCounts[$grade])) {
                            $gradeCounts[$grade]++;
                        }

                        $totalMarks += $average;
                        $totalStudents++;
                    }
                }

                if ($totalStudents > 0) {
                    $streamAverage = round($totalMarks / $totalStudents, 2);
                    $streamGrade = $gradeScale ? $this->calculateGradeFromScale($streamAverage, $gradeScale) : $this->calculateGrade($streamAverage);

                    // Get class teacher for this stream
                    $classTeacher = \App\Models\School\ClassTeacher::where('class_id', $class->id)
                        ->where('stream_id', $stream->id)
                        ->where('academic_year_id', $academicYearId)
                        ->where(function ($query) use ($branchId) {
                            $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                        })
                        ->where('is_active', true)
                        ->whereHas('employee', function($query) {
                            $query->where('company_id', auth()->user()->company_id);
                        })
                        ->with('employee')
                        ->first();

                    $teacherName = $classTeacher && $classTeacher->employee
                        ? $classTeacher->employee->full_name
                        : '-';

                    $analysis[] = [
                        'class' => $class,
                        'stream' => $stream,
                        'students' => $totalStudents,
                        'grade_counts' => $gradeCounts,
                        'class_mean' => $streamAverage,
                        'grade' => $streamGrade,
                        'class_teacher' => $teacherName
                    ];
                }
            }
        }

        // Sort analysis by class name and stream name to ensure proper grouping
        usort($analysis, function($a, $b) {
            $classCompare = strcmp($a['class']->name, $b['class']->name);
            if ($classCompare !== 0) {
                return $classCompare;
            }
            return strcmp($a['stream']->name, $b['stream']->name);
        });

        // Calculate class subtotals and grand total
        $classSubtotals = [];
        $grandTotalGradeCounts = [];
        foreach ($gradeLetters as $letter) {
            $grandTotalGradeCounts[$letter] = 0;
        }
        $grandTotal = [
            'students' => 0,
            'grade_counts' => $grandTotalGradeCounts,
            'total_mean' => 0,
            'weighted_total' => 0
        ];

        // Group by class and calculate subtotals for each class
        $groupedByClass = [];
        foreach ($analysis as $item) {
            $classId = $item['class']->id;
            if (!isset($groupedByClass[$classId])) {
                $groupedByClass[$classId] = [
                    'class' => $item['class'],
                    'items' => []
                ];
            }
            $groupedByClass[$classId]['items'][] = $item;
        }

        // Calculate subtotals for each class
        foreach ($groupedByClass as $classId => $classData) {
            $classGradeCounts = [];
            foreach ($gradeLetters as $letter) {
                $classGradeCounts[$letter] = 0;
            }
            $classSubtotal = [
                'class' => $classData['class'],
                'students' => 0,
                'grade_counts' => $classGradeCounts,
                'total_mean' => 0,
                'weighted_total' => 0
            ];

            foreach ($classData['items'] as $item) {
                $classSubtotal['students'] += $item['students'];
                foreach ($gradeLetters as $grade) {
                    $classSubtotal['grade_counts'][$grade] += $item['grade_counts'][$grade] ?? 0;
                }
                $classSubtotal['weighted_total'] += ($item['class_mean'] * $item['students']);
            }

            if ($classSubtotal['students'] > 0) {
                $classSubtotal['total_mean'] = round($classSubtotal['weighted_total'] / $classSubtotal['students'], 2);
                $classSubtotal['grade'] = $gradeScale ? $this->calculateGradeFromScale($classSubtotal['total_mean'], $gradeScale) : $this->calculateGrade($classSubtotal['total_mean']);
                $classSubtotals[$classId] = $classSubtotal;
            }
        }

        // Group by class categories (you may need to adjust this logic based on your class naming)
        $categories = [
            'NURSERY' => ['BABY CLASS'],
            'LOWER PRIMARY' => ['MIDDLE CLASS', 'PRE-UNIT', 'STANDARD ONE', 'STANDARD TWO'],
            'Others' => ['STANDARD THREE', 'STANDARD FOUR', 'STANDARD FIVE', 'STANDARD SIX', 'STANDARD SEVEN']
        ];

        $subtotals = [];
        foreach ($categories as $categoryName => $classNames) {
            $categoryGradeCounts = [];
            foreach ($gradeLetters as $letter) {
                $categoryGradeCounts[$letter] = 0;
            }
            $categoryTotal = [
                'students' => 0,
                'grade_counts' => $categoryGradeCounts,
                'total_mean' => 0,
                'weighted_total' => 0
            ];

            foreach ($analysis as $item) {
                if (in_array($item['class']->name, $classNames)) {
                    $categoryTotal['students'] += $item['students'];
                    foreach ($gradeLetters as $grade) {
                        $categoryTotal['grade_counts'][$grade] += $item['grade_counts'][$grade] ?? 0;
                    }
                    $categoryTotal['weighted_total'] += ($item['class_mean'] * $item['students']);
                }
            }

            if ($categoryTotal['students'] > 0) {
                $categoryTotal['total_mean'] = round($categoryTotal['weighted_total'] / $categoryTotal['students'], 2);
                $categoryTotal['grade'] = $gradeScale ? $this->calculateGradeFromScale($categoryTotal['total_mean'], $gradeScale) : $this->calculateGrade($categoryTotal['total_mean']);
                $subtotals[$categoryName] = $categoryTotal;
            }
        }

        // Calculate grand total
        foreach ($analysis as $item) {
            $grandTotal['students'] += $item['students'];
            foreach ($gradeLetters as $grade) {
                $grandTotal['grade_counts'][$grade] += $item['grade_counts'][$grade] ?? 0;
            }
            $grandTotal['weighted_total'] += ($item['class_mean'] * $item['students']);
        }

        if ($grandTotal['students'] > 0) {
            $grandTotal['total_mean'] = round($grandTotal['weighted_total'] / $grandTotal['students'], 2);
            $grandTotal['grade'] = $gradeScale ? $this->calculateGradeFromScale($grandTotal['total_mean'], $gradeScale) : $this->calculateGrade($grandTotal['total_mean']);
        }

        return [
            'analysis' => $analysis,
            'classSubtotals' => $classSubtotals,
            'subtotals' => $subtotals,
            'grandTotal' => $grandTotal,
            'gradeLetters' => $gradeLetters
        ];
    }

    private function getPerformanceByClassData(Request $request, $branchId)
    {
        // Get filter values
        $examTypeId = $request->exam_type_id;
        $academicYearId = $request->academic_year_id;

        if (!$examTypeId || !$academicYearId) {
            return [
                'performance' => [],
                'subtotals' => [],
                'grandTotal' => [
                    'passed' => 0,
                    'failed' => 0,
                    'not_attempted' => 0,
                    'total_students' => 0,
                    'pass_rate' => 0,
                    'classes_count' => 0
                ],
                'absentStudents' => []
            ];
        }

        // Get grade scale for the academic year
        $gradeScale = \App\Models\SchoolGradeScale::active()
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->first();

        // Get all classes and streams
        $classesQuery = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        // Apply class filter if provided
        if ($request->filled('class_id')) {
            $classesQuery->where('id', $request->class_id);
        }

        $classes = $classesQuery->with(['streams' => function ($query) use ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                })
                ->where('is_active', true)
                ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $performance = [];
        $allAbsentStudents = [];

        foreach ($classes as $class) {
            $classStreams = [];

            // Get streams for this class, applying stream filter if provided
            $streamsQuery = $class->streams();
            if ($request->filled('stream_id')) {
                $streamsQuery->where('streams.id', $request->stream_id);
            }
            $streams = $streamsQuery->get();

            if ($streams->isEmpty()) {
                continue;
            }

            foreach ($streams as $stream) {
                // Get students in this stream and class
                $students = \App\Models\School\Student::where('class_id', $class->id)
                    ->where('stream_id', $stream->id)
                    ->where('academic_year_id', $academicYearId)
                    ->where('company_id', auth()->user()->company_id)
                    ->where('status', 'active')
                    ->get();

                if ($students->isEmpty()) {
                    continue;
                }

                // Get exam assignments for this class
                $examClassAssignments = \App\Models\ExamClassAssignment::where('exam_type_id', $examTypeId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('class_id', $class->id)
                    ->where('company_id', auth()->user()->company_id)
                    // Temporarily remove branch filter for debugging
                    /*
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                    })
                    */
                    ->with('subject')
                    ->get();

                if ($examClassAssignments->isEmpty()) {
                    continue;
                }

                // Get marks for these students and assignments
                $assignmentIds = $examClassAssignments->pluck('id');
                $studentIds = $students->pluck('id');

                $marks = \App\Models\SchoolExamMark::whereIn('exam_class_assignment_id', $assignmentIds)
                    ->whereIn('student_id', $studentIds)
                    ->get()
                    ->keyBy(function($mark) {
                        return $mark->student_id . '-' . $mark->exam_class_assignment_id;
                    });

                // Get registrations
                $examRegistrations = \App\Models\SchoolExamRegistration::whereIn('exam_class_assignment_id', $assignmentIds)
                    ->whereIn('student_id', $studentIds)
                    ->where('exam_type_id', $examTypeId)
                    ->where('academic_year_id', $academicYearId)
                    ->get()
                    ->keyBy(function($registration) {
                        return $registration->student_id . '-' . $registration->exam_class_assignment_id;
                    });

                // Calculate results for each student
                $passed = 0;
                $failed = 0;
                $notAttempted = 0;
                $absentStudents = [];

                foreach ($students as $student) {
                    $studentMarks = [];
                    $hasAnyMarks = false;
                    $isAbsent = false;
                    $absentSubjects = [];

                    foreach ($examClassAssignments as $assignment) {
                        $markKey = $student->id . '-' . $assignment->id;
                        $registrationKey = $student->id . '-' . $assignment->id;
                        
                        $mark = $marks->get($markKey);
                        $registration = $examRegistrations->get($registrationKey);

                        // Check if student is absent for this subject
                        if ($registration && $registration->status === 'absent') {
                            $isAbsent = true;
                            $absentSubjects[] = $assignment->subject->name ?? 'Unknown';
                        } elseif ($mark && $mark->marks_obtained !== null) {
                            $studentMarks[] = $mark->marks_obtained;
                            $hasAnyMarks = true;
                        }
                    }

                    if ($isAbsent) {
                        // Student is absent - don't count in pass/fail statistics
                        $absentStudents[] = [
                            'student' => $student,
                            'absent_subjects' => $absentSubjects
                        ];
                    } elseif (!$hasAnyMarks) {
                        $notAttempted++;
                    } else {
                        // Calculate student's average score
                        $averageScore = count($studentMarks) > 0 ? array_sum($studentMarks) / count($studentMarks) : 0;
                        
                        // Check if student passed based on grade scale's passed_average_point
                        $passingPoint = $gradeScale ? $gradeScale->passed_average_point : 50.00;
                        
                        if ($averageScore >= $passingPoint) {
                            $passed++;
                        } else {
                            $failed++;
                        }
                    }
                }

                // Add absent students to global list
                $allAbsentStudents = array_merge($allAbsentStudents, $absentStudents);

                $totalStudents = $passed + $failed + $notAttempted;

                $classStreams[] = [
                    'class' => $class,
                    'stream' => $stream,
                    'passed' => $passed,
                    'failed' => $failed,
                    'not_attempted' => $notAttempted,
                    'total_students' => $totalStudents
                ];
            }

            if (!empty($classStreams)) {
                $performance[$class->name] = $classStreams;
            }
        }

        // Calculate subtotals
        $subtotals = [];
        foreach ($performance as $className => $streams) {
            $subtotal = [
                'passed' => 0,
                'failed' => 0,
                'not_attempted' => 0,
                'total_students' => 0
            ];

            foreach ($streams as $stream) {
                $subtotal['passed'] += $stream['passed'];
                $subtotal['failed'] += $stream['failed'];
                $subtotal['not_attempted'] += $stream['not_attempted'];
                $subtotal['total_students'] += $stream['passed'] + $stream['failed'] + $stream['not_attempted'];
            }

            $subtotals[$className] = $subtotal;
        }

        // Calculate grand totals
        $grandTotal = [
            'passed' => 0,
            'failed' => 0,
            'not_attempted' => 0,
            'total_students' => 0,
            'pass_rate' => 0,
            'classes_count' => count($performance)
        ];

        foreach ($subtotals as $subtotal) {
            $grandTotal['passed'] += $subtotal['passed'];
            $grandTotal['failed'] += $subtotal['failed'];
            $grandTotal['not_attempted'] += $subtotal['not_attempted'];
            $grandTotal['total_students'] += $subtotal['passed'] + $subtotal['failed'] + $subtotal['not_attempted'];
        }

        if ($grandTotal['total_students'] > 0) {
            $grandTotal['pass_rate'] = round(($grandTotal['passed'] / $grandTotal['total_students']) * 100, 1);
        }

        $result = [
            'performance' => $performance,
            'subtotals' => $subtotals,
            'grandTotal' => $grandTotal,
            'absentStudents' => $allAbsentStudents
        ];

        return $result;
    }

    private function getSubjectWiseAnalysisData(Request $request, $branchId)
    {
        // Get filter values
        $examTypeId = $request->exam_type_id;
        $academicYearId = $request->academic_year_id;

        \Log::info('getSubjectWiseAnalysisData called', [
            'exam_type_id' => $examTypeId,
            'academic_year_id' => $academicYearId,
            'class_id' => $request->class_id,
            'stream_id' => $request->stream_id,
            'branch_id' => $branchId
        ]);

        if (!$examTypeId || !$academicYearId) {
            return [
                'subjects' => [],
                'summary' => [
                    'total_subjects' => 0,
                    'total_students' => 0,
                    'passed' => 0,
                    'failed' => 0,
                    'not_attempted' => 0,
                    'absent' => 0,
                    'overall_pass_rate' => 0,
                    'average_score' => 0
                ],
                'absentStudents' => []
            ];
        }

        // Get grade scale for the academic year
        $gradeScale = \App\Models\SchoolGradeScale::active()
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->first();

        // Get all exam class assignments for the selected filters
        $examAssignmentsQuery = \App\Models\ExamClassAssignment::where('exam_type_id', $examTypeId)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->with(['subject', 'classe', 'stream']);

        // Apply class filter if provided
        if ($request->filled('class_id')) {
            $examAssignmentsQuery->where('class_id', $request->class_id);
        }

        // Apply stream filter if provided
        if ($request->filled('stream_id')) {
            $examAssignmentsQuery->where(function ($query) use ($request) {
                $query->where('stream_id', $request->stream_id)
                      ->orWhereNull('stream_id');
            });
        }

        $examAssignments = $examAssignmentsQuery->get();

        \Log::info('Exam assignments found', [
            'count' => $examAssignments->count(),
            'class_ids' => $examAssignments->pluck('class_id')->unique()->toArray(),
            'stream_ids' => $examAssignments->pluck('stream_id')->unique()->toArray()
        ]);

        if ($examAssignments->isEmpty()) {
            return [
                'subjects' => [],
                'summary' => [
                    'total_subjects' => 0,
                    'total_students' => 0,
                    'passed' => 0,
                    'failed' => 0,
                    'not_attempted' => 0,
                    'absent' => 0,
                    'overall_pass_rate' => 0,
                    'average_score' => 0
                ],
                'absentStudents' => []
            ];
        }

        // Sort subjects by subject group order if class is selected (same as Examination Results Report)
        $sortedSubjects = $this->getSortedSubjects($examAssignments, $request, $branchId);

        // Get all students for these assignments
        $assignmentIds = $examAssignments->pluck('id');
        $classIds = $examAssignments->pluck('class_id')->unique();
        $streamIds = $examAssignments->pluck('stream_id')->unique();

        $studentsQuery = \App\Models\School\Student::whereIn('class_id', $classIds)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where('status', 'active');

        // Apply stream filter if provided
        if ($request->filled('stream_id')) {
            $studentsQuery->where('stream_id', $request->stream_id);
        }

        $students = $studentsQuery->get();

        \Log::info('Students found', [
            'count' => $students->count(),
            'class_ids' => $students->pluck('class_id')->unique()->toArray(),
            'stream_ids' => $students->pluck('stream_id')->unique()->toArray()
        ]);

        // Get exam registrations to check attendance status
        $examRegistrations = \App\Models\SchoolExamRegistration::whereIn('exam_class_assignment_id', $assignmentIds)
            ->whereIn('student_id', $students->pluck('id'))
            ->where('exam_type_id', $examTypeId)
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->keyBy(function($registration) {
                return $registration->student_id . '-' . $registration->exam_class_assignment_id;
            });

        // Get marks for these assignments and students
        $marks = \App\Models\SchoolExamMark::whereIn('exam_class_assignment_id', $assignmentIds)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy(function($mark) {
                return $mark->student_id . '-' . $mark->exam_class_assignment_id;
            });

        // Group assignments by subject using sorted subjects
        $subjectsData = [];
        $totalStudents = 0;
        $totalPassed = 0;
        $totalFailed = 0;
        $totalNotAttempted = 0;
        $totalAbsent = 0;
        $totalScores = [];
        $passingPoint = $gradeScale ? $gradeScale->passed_average_point : 50.00;
        $allAbsentStudents = [];

        foreach ($sortedSubjects as $subject) {
            $assignments = $examAssignments->where('subject_id', $subject->id);

            // Initialize grade breakdown from grade scale or use default
            $gradeBreakdown = [];
            if ($gradeScale && $gradeScale->grades->isNotEmpty()) {
                foreach ($gradeScale->grades->sortBy('sort_order') as $grade) {
                    $gradeBreakdown[$grade->grade_letter] = ['female' => 0, 'male' => 0, 'total' => 0];
                }
            } else {
                // Fallback to default grades if no grade scale
                $defaultGrades = ['A', 'B', 'C', 'D', 'F'];
                foreach ($defaultGrades as $gradeLetter) {
                    $gradeBreakdown[$gradeLetter] = ['female' => 0, 'male' => 0, 'total' => 0];
                }
            }

            $subjectStats = [
                'subject_name' => $subject->name,
                'grade_breakdown' => $gradeBreakdown,
                'totals' => ['female' => 0, 'male' => 0, 'total' => 0],
                'total_students' => 0,
                'passed' => 0,
                'failed' => 0,
                'not_attempted' => 0,
                'absent' => 0,
                'highest_score' => 0,
                'lowest_score' => null,
                'average_score' => 0,
                'pass_rate' => 0,
                'scores' => []
            ];

            // Process marks for this subject
            foreach ($assignments as $assignment) {
                foreach ($students as $student) {
                    // Only process students that belong to this assignment's class and stream (if assignment has stream)
                    if ($student->class_id !== $assignment->class_id || 
                        ($assignment->stream_id && $student->stream_id !== $assignment->stream_id)) {
                        continue;
                    }

                    $markKey = $student->id . '-' . $assignment->id;
                    $registrationKey = $student->id . '-' . $assignment->id;

                    $mark = $marks->get($markKey);
                    $registration = $examRegistrations->get($registrationKey);

                    $subjectStats['total_students']++;

                    // Check if student is absent
                    if ($registration && $registration->status === 'absent') {
                        $subjectStats['absent']++;
                        // Collect absent student data
                        $absentStudentKey = $student->id;
                        if (!isset($allAbsentStudents[$absentStudentKey])) {
                            $allAbsentStudents[$absentStudentKey] = [
                                'student' => $student->load(['class', 'stream']),
                                'absent_subjects' => []
                            ];
                        }
                        $allAbsentStudents[$absentStudentKey]['absent_subjects'][] = $subject->name;
                        continue;
                    }

                    // Check if student has marks
                    if ($mark && $mark->marks_obtained !== null) {
                        $score = $mark->marks_obtained;
                        $subjectStats['scores'][] = $score;

                        // Update highest/lowest scores
                        if ($subjectStats['lowest_score'] === null || $score < $subjectStats['lowest_score']) {
                            $subjectStats['lowest_score'] = $score;
                        }
                        if ($score > $subjectStats['highest_score']) {
                            $subjectStats['highest_score'] = $score;
                        }

                        // Calculate grade using grade scale
                        $grade = $this->calculateGradeFromScore($score, $gradeScale);

                        // Determine gender
                        $gender = strtolower($student->gender);
                        $isFemale = in_array($gender, ['f', 'female', 'woman', 'girl']);
                        $isMale = in_array($gender, ['m', 'male', 'man', 'boy']);

                        if ($isFemale) {
                            $subjectStats['grade_breakdown'][$grade]['female']++;
                            $subjectStats['totals']['female']++;
                        } elseif ($isMale) {
                            $subjectStats['grade_breakdown'][$grade]['male']++;
                            $subjectStats['totals']['male']++;
                        }

                        $subjectStats['grade_breakdown'][$grade]['total']++;
                        $subjectStats['totals']['total']++;

                        // Check pass/fail
                        if ($score >= $passingPoint) {
                            $subjectStats['passed']++;
                        } else {
                            $subjectStats['failed']++;
                        }
                    } else {
                        $subjectStats['not_attempted']++;
                    }
                }
            }

            // Calculate subject statistics
            $subjectStats['total_students'] = $subjectStats['passed'] + $subjectStats['failed'] + $subjectStats['not_attempted'];
            if ($subjectStats['total_students'] > 0) {
                $subjectStats['pass_rate'] = round(($subjectStats['passed'] / $subjectStats['total_students']) * 100, 1);
            }
            if (!empty($subjectStats['scores'])) {
                $subjectStats['average_score'] = round(array_sum($subjectStats['scores']) / count($subjectStats['scores']), 1);
                $totalScores = array_merge($totalScores, $subjectStats['scores']);
            }

            $totalStudents += $subjectStats['total_students'];
            $totalPassed += $subjectStats['passed'];
            $totalFailed += $subjectStats['failed'];
            $totalNotAttempted += $subjectStats['not_attempted'];
            $totalAbsent += $subjectStats['absent'];

            $subjectsData[] = $subjectStats;
        }

        // Calculate summary statistics
        $summary = [
            'total_subjects' => count($subjectsData),
            'total_students' => $totalStudents,
            'passed' => $totalPassed,
            'failed' => $totalFailed,
            'not_attempted' => $totalNotAttempted,
            'absent' => $totalAbsent,
            'overall_pass_rate' => $totalStudents > 0 ? round(($totalPassed / $totalStudents) * 100, 1) : 0,
            'average_score' => !empty($totalScores) ? round(array_sum($totalScores) / count($totalScores), 1) : 0
        ];

        // Extract grade letters from grade scale or use default
        $gradeLetters = ['A', 'B', 'C', 'D', 'F'];
        if ($gradeScale && $gradeScale->grades->isNotEmpty()) {
            $gradeLetters = $gradeScale->grades->sortBy('sort_order')->pluck('grade_letter')->toArray();
        }

        return [
            'subjects' => $subjectsData,
            'summary' => $summary,
            'absentStudents' => array_values($allAbsentStudents),
            'grade_letters' => $gradeLetters
        ];
    }

    /**
     * Get subjects sorted by subject group order (same as Examination Results Report)
     */
    private function getSortedSubjects($examAssignments, $request, $branchId)
    {
        // Get subjects ordered by subject group sort order if class is selected
        if ($request->filled('class_id')) {
            $subjectGroups = \App\Models\School\SubjectGroup::where('class_id', $request->class_id)
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                })
                ->where('is_active', true)
                ->with(['subjects' => function ($query) {
                    $query->orderBy('subject_subject_group.sort_order')
                          ->orderBy('subjects.name');
                }])
                ->get();

            if ($subjectGroups->isNotEmpty()) {
                $orderedSubjects = collect();
                foreach ($subjectGroups as $subjectGroup) {
                    foreach ($subjectGroup->subjects as $subject) {
                        if ($examAssignments->contains('subject_id', $subject->id)) {
                            $orderedSubjects->push($subject);
                        }
                    }
                }
                // Add any remaining subjects not in subject groups
                $subjectsFromAssignments = $examAssignments->pluck('subject')->filter()->unique('id');
                foreach ($subjectsFromAssignments as $subject) {
                    if (!$orderedSubjects->contains('id', $subject->id)) {
                        $orderedSubjects->push($subject);
                    }
                }
                return $orderedSubjects->unique('id');
            } else {
                // Fallback to original logic
                return $examAssignments->pluck('subject')->filter()->unique('id')->sortBy('name');
            }
        } else {
            return $examAssignments->pluck('subject')->filter()->unique('id')->sortBy('name');
        }
    }

    /**
     * Get comparative subject performance data
     */
    private function getComparativeSubjectPerformanceData(Request $request, $branchId)
    {
        $data = [
            'period1' => null,
            'period2' => null,
            'comparison' => [],
            'absent_students_period1' => [],
            'absent_students_period2' => [],
            'grade_letters' => ['A', 'B', 'C', 'D', 'F'] // Default fallback
        ];

        // Get data for period 1
        if ($request->academic_year_id_1 && $request->exam_type_id_1) {
            $data['period1'] = $this->getSubjectWiseAnalysisDataForPeriod(
                $request->academic_year_id_1,
                $request->exam_type_id_1,
                $request->class_id,
                $request->stream_id,
                $branchId
            );
            $data['absent_students_period1'] = $data['period1']['absent_students'] ?? [];
            // Use grade letters from period 1 if available
            if (isset($data['period1']['grade_letters']) && !empty($data['period1']['grade_letters'])) {
                $data['grade_letters'] = $data['period1']['grade_letters'];
            }
        }

        // Get data for period 2
        if ($request->academic_year_id_2 && $request->exam_type_id_2) {
            $data['period2'] = $this->getSubjectWiseAnalysisDataForPeriod(
                $request->academic_year_id_2,
                $request->exam_type_id_2,
                $request->class_id,
                $request->stream_id,
                $branchId
            );
            $data['absent_students_period2'] = $data['period2']['absent_students'] ?? [];
            // Merge grade letters from both periods (use union to get all unique grades)
            if (isset($data['period2']['grade_letters']) && !empty($data['period2']['grade_letters'])) {
                $data['grade_letters'] = array_unique(array_merge($data['grade_letters'], $data['period2']['grade_letters']));
                // Sort to maintain order
                sort($data['grade_letters']);
            }
        }

        // Calculate comparison
        if ($data['period1'] && $data['period2']) {
            $data['comparison'] = $this->calculateComparison($data['period1'], $data['period2']);
        }

        return $data;
    }

    /**
     * Get student subject performance data for analysis
     */
    private function getStudentSubjectPerformanceData(Request $request, $branchId)
    {
        $data = [];

        // Get students based on filters
        $studentsQuery = \App\Models\School\Student::where('company_id', auth()->user()->company_id)
            ->where('branch_id', $branchId)
            ->with(['class', 'stream']);

        if ($request->class_id) {
            $studentsQuery->where('class_id', $request->class_id);
        }

        if ($request->stream_id) {
            $studentsQuery->where('stream_id', $request->stream_id);
        }

        $students = $studentsQuery->orderBy('first_name')->orderBy('last_name')->get();

        // Get subjects for the class
        $subjects = [];
        if ($request->class_id) {
            $examClassAssignments = \App\Models\ExamClassAssignment::where('class_id', $request->class_id)
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->with('subject')
                ->get();

            // Get subjects ordered by subject group sort order
            $subjectGroups = \App\Models\School\SubjectGroup::where('class_id', $request->class_id)
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                })
                ->where('is_active', true)
                ->with(['subjects' => function ($query) {
                    $query->orderBy('subject_subject_group.sort_order')
                          ->orderBy('subjects.name');
                }])
                ->get();

            if ($subjectGroups->isNotEmpty()) {
                $orderedSubjects = collect();
                foreach ($subjectGroups as $subjectGroup) {
                    foreach ($subjectGroup->subjects as $subject) {
                        if ($examClassAssignments->contains('subject_id', $subject->id)) {
                            $orderedSubjects->push($subject);
                        }
                    }
                }
                // Add any remaining subjects not in subject groups
                $subjectsFromAssignments = $examClassAssignments->pluck('subject')->filter()->unique('id');
                foreach ($subjectsFromAssignments as $subject) {
                    if (!$orderedSubjects->contains('id', $subject->id)) {
                        $orderedSubjects->push($subject);
                    }
                }
                $subjects = $orderedSubjects->unique('id')->values();
            } else {
                // Fallback to sorting by name
                $subjects = $examClassAssignments->pluck('subject')->filter()->unique('id')->sortBy('name')->values();
            }
        }

        // Get grade scales for both academic years
        $currentGradeScale = null;
        $previousGradeScale = null;
        
        if ($request->academic_year_id_1) {
            $currentGradeScale = \App\Models\SchoolGradeScale::active()
                ->where('academic_year_id', $request->academic_year_id_1)
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->with('grades')
                ->first();
        }
        
        if ($request->academic_year_id_2) {
            $previousGradeScale = \App\Models\SchoolGradeScale::active()
                ->where('academic_year_id', $request->academic_year_id_2)
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->with('grades')
                ->first();
        }

        // Get exam assignments for current period
        $currentAssignments = collect();
        if ($request->academic_year_id_1 && $request->exam_type_id_1) {
            $currentAssignments = \App\Models\ExamClassAssignment::where('academic_year_id', $request->academic_year_id_1)
                ->where('exam_type_id', $request->exam_type_id_1)
                ->where('class_id', $request->class_id)
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->with('subject')
                ->get()
                ->keyBy('subject_id');
        }

        // Get exam assignments for previous period
        $previousAssignments = collect();
        if ($request->academic_year_id_2 && $request->exam_type_id_2) {
            $previousAssignments = \App\Models\ExamClassAssignment::where('academic_year_id', $request->academic_year_id_2)
                ->where('exam_type_id', $request->exam_type_id_2)
                ->where('class_id', $request->class_id)
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->with('subject')
                ->get()
                ->keyBy('subject_id');
        }

        // Get all assignment IDs
        $allAssignmentIds = $currentAssignments->merge($previousAssignments)->pluck('id')->unique();

        // Get exam registrations for attendance status
        $examRegistrations = \App\Models\SchoolExamRegistration::whereIn('exam_class_assignment_id', $allAssignmentIds)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy(function($registration) {
                return $registration->student_id . '-' . $registration->exam_class_assignment_id;
            });

        // Get marks
        $marks = \App\Models\SchoolExamMark::whereIn('exam_class_assignment_id', $allAssignmentIds)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy(function($mark) {
                return $mark->student_id . '-' . $mark->exam_class_assignment_id;
            });

        // Pre-calculate all subject ranks to avoid N+1 query problem
        $subjectRanks = $this->calculateAllSubjectRanks(
            $request->academic_year_id_1,
            $request->exam_type_id_1,
            $request->academic_year_id_2,
            $request->exam_type_id_2,
            $request->class_id,
            $branchId,
            $request->stream_id,
            $subjects,
            $currentAssignments,
            $previousAssignments,
            $students
        );

        $studentData = [];

        foreach ($students as $student) {
            $studentInfo = [
                'student' => $student,
                'subjects' => []
            ];

            // Get performance for each subject
            foreach ($subjects as $subject) {
                $subjectPerformance = [
                    'subject_name' => $subject->name,
                    'current_period' => null,
                    'previous_period' => null,
                    'improvement' => null
                ];

                // Get current period data
                if ($currentAssignments->has($subject->id)) {
                    $assignment = $currentAssignments->get($subject->id);
                    $markKey = $student->id . '-' . $assignment->id;
                    $registrationKey = $student->id . '-' . $assignment->id;

                    $mark = $marks->get($markKey);
                    $registration = $examRegistrations->get($registrationKey);

                    if ($registration && $registration->status === 'absent') {
                        $subjectPerformance['current_period'] = [
                            'grade' => 'ABS',
                            'marks_percentage' => 0,
                            'class_rank' => '-',
                            'status' => 'absent'
                        ];
                    } elseif ($registration && $registration->status === 'exempted') {
                        $subjectPerformance['current_period'] = [
                            'grade' => 'EXEMPT',
                            'marks_percentage' => 0,
                            'class_rank' => '-',
                            'status' => 'exempted'
                        ];
                    } elseif ($mark) {
                        $percentage = $mark->max_marks > 0 ? round(($mark->marks_obtained / $mark->max_marks) * 100, 1) : 0;
                        $grade = $currentGradeScale ? $this->calculateGradeFromScale($percentage, $currentGradeScale) : $this->calculateGrade($percentage);
                        // Get pre-calculated rank
                        $rankKey = $request->academic_year_id_1 . '-' . $request->exam_type_id_1 . '-' . $subject->id . '-' . $student->id;
                        $classRank = $subjectRanks[$rankKey] ?? '-';

                        $subjectPerformance['current_period'] = [
                            'grade' => $grade,
                            'marks_percentage' => $percentage,
                            'class_rank' => $classRank,
                            'status' => 'present'
                        ];
                    } else {
                        $subjectPerformance['current_period'] = [
                            'grade' => 'N/A',
                            'marks_percentage' => 0,
                            'class_rank' => '-',
                            'status' => 'no_marks'
                        ];
                    }
                }

                // Get previous period data
                if ($previousAssignments->has($subject->id)) {
                    $assignment = $previousAssignments->get($subject->id);
                    $markKey = $student->id . '-' . $assignment->id;
                    $registrationKey = $student->id . '-' . $assignment->id;

                    $mark = $marks->get($markKey);
                    $registration = $examRegistrations->get($registrationKey);

                    if ($registration && $registration->status === 'absent') {
                        $subjectPerformance['previous_period'] = [
                            'grade' => 'ABS',
                            'marks_percentage' => 0,
                            'class_rank' => '-',
                            'status' => 'absent'
                        ];
                    } elseif ($registration && $registration->status === 'exempted') {
                        $subjectPerformance['previous_period'] = [
                            'grade' => 'EXEMPT',
                            'marks_percentage' => 0,
                            'class_rank' => '-',
                            'status' => 'exempted'
                        ];
                    } elseif ($mark) {
                        $percentage = $mark->max_marks > 0 ? round(($mark->marks_obtained / $mark->max_marks) * 100, 1) : 0;
                        $grade = $previousGradeScale ? $this->calculateGradeFromScale($percentage, $previousGradeScale) : $this->calculateGrade($percentage);
                        // Get pre-calculated rank
                        $rankKey = $request->academic_year_id_2 . '-' . $request->exam_type_id_2 . '-' . $subject->id . '-' . $student->id;
                        $classRank = $subjectRanks[$rankKey] ?? '-';

                        $subjectPerformance['previous_period'] = [
                            'grade' => $grade,
                            'marks_percentage' => $percentage,
                            'class_rank' => $classRank,
                            'status' => 'present'
                        ];
                    } else {
                        $subjectPerformance['previous_period'] = [
                            'grade' => 'N/A',
                            'marks_percentage' => 0,
                            'class_rank' => '-',
                            'status' => 'no_marks'
                        ];
                    }
                }

                // Calculate improvement/decline
                if ($subjectPerformance['current_period'] && $subjectPerformance['previous_period'] &&
                    $subjectPerformance['current_period']['status'] === 'present' &&
                    $subjectPerformance['previous_period']['status'] === 'present') {
                    $currentPercent = $subjectPerformance['current_period']['marks_percentage'];
                    $previousPercent = $subjectPerformance['previous_period']['marks_percentage'];
                    $improvement = $currentPercent - $previousPercent;
                    $subjectPerformance['improvement'] = $improvement;
                }

                $studentInfo['subjects'][] = $subjectPerformance;
            }

            $studentData[] = $studentInfo;
        }

        $data['students'] = $studentData;
        $data['subjects'] = $subjects;

        return $data;
    }

    /**
     * Calculate all subject ranks in bulk to avoid N+1 query problem
     */
    private function calculateAllSubjectRanks($academicYearId1, $examTypeId1, $academicYearId2, $examTypeId2, $classId, $branchId, $streamId, $subjects, $currentAssignments, $previousAssignments, $students)
    {
        $ranks = [];
        
        if (!$classId || $subjects->isEmpty() || $students->isEmpty()) {
            return $ranks;
        }

        $studentIds = $students->pluck('id')->toArray();
        
        // Process both periods
        $periods = [];
        if ($academicYearId1 && $examTypeId1) {
            $periods[] = [
                'academic_year_id' => $academicYearId1,
                'exam_type_id' => $examTypeId1,
                'assignments' => $currentAssignments
            ];
        }
        if ($academicYearId2 && $examTypeId2) {
            $periods[] = [
                'academic_year_id' => $academicYearId2,
                'exam_type_id' => $examTypeId2,
                'assignments' => $previousAssignments
            ];
        }

        foreach ($periods as $period) {
            foreach ($subjects as $subject) {
                $assignment = $period['assignments']->get($subject->id);
                if (!$assignment) {
                    continue;
                }

                // Get all marks for this assignment in one query
                $marks = \App\Models\SchoolExamMark::where('exam_class_assignment_id', $assignment->id)
                    ->whereIn('student_id', $studentIds)
                    ->get()
                    ->keyBy('student_id');

                // Get all registrations for this assignment in one query
                $registrations = \App\Models\SchoolExamRegistration::where('exam_class_assignment_id', $assignment->id)
                    ->whereIn('student_id', $studentIds)
                    ->get()
                    ->keyBy('student_id');

                // Collect valid marks (students who have marks and are not absent)
                $validMarks = [];
                foreach ($studentIds as $studentId) {
                    $mark = $marks->get($studentId);
                    $registration = $registrations->get($studentId);

                    // Include students who have marks and are not absent
                    if ($mark && (!$registration || $registration->status !== 'absent')) {
                        $percentage = $mark->max_marks > 0 ? round(($mark->marks_obtained / $mark->max_marks) * 100, 1) : 0;
                        $validMarks[] = [
                            'student_id' => $studentId,
                            'percentage' => $percentage
                        ];
                    }
                }

                // Sort by percentage descending (highest marks first)
                usort($validMarks, function($a, $b) {
                    return $b['percentage'] <=> $a['percentage'];
                });

                // Create rank map
                $totalStudents = count($validMarks);
                foreach ($validMarks as $index => $markData) {
                    $rankKey = $period['academic_year_id'] . '-' . $period['exam_type_id'] . '-' . $subject->id . '-' . $markData['student_id'];
                    $ranks[$rankKey] = ($index + 1) . '/' . $totalStudents;
                }
            }
        }

        return $ranks;
    }

    /**
     * Calculate class rank for a student in a specific subject
     */
    private function calculateSubjectRank($academicYearId, $examTypeId, $classId, $subjectId, $studentId, $branchId = null, $streamId = null)
    {
        // Get the exam class assignment for this subject
        $assignmentQuery = \App\Models\ExamClassAssignment::where('academic_year_id', $academicYearId)
            ->where('exam_type_id', $examTypeId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('company_id', auth()->user()->company_id);

        if ($branchId) {
            $assignmentQuery->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });
        }

        $assignment = $assignmentQuery->first();

        if (!$assignment) {
            return '-';
        }

        // Get all students in the class (and stream if filtered)
        $studentsQuery = \App\Models\School\Student::where('class_id', $classId)
            ->where('company_id', auth()->user()->company_id);

        if ($branchId) {
            $studentsQuery->where('branch_id', $branchId);
        }

        if ($streamId) {
            $studentsQuery->where('stream_id', $streamId);
        }

        $students = $studentsQuery->pluck('id');

        // Get exam registrations for this assignment
        $registrations = \App\Models\SchoolExamRegistration::where('exam_class_assignment_id', $assignment->id)
            ->whereIn('student_id', $students)
            ->get()
            ->keyBy('student_id');

        // Get marks for this assignment
        $marks = \App\Models\SchoolExamMark::where('exam_class_assignment_id', $assignment->id)
            ->whereIn('student_id', $students)
            ->get()
            ->keyBy('student_id');

        // Collect valid marks (students who have marks and are not absent)
        $validMarks = [];
        foreach ($students as $studentIdCheck) {
            $registration = $registrations->get($studentIdCheck);
            $mark = $marks->get($studentIdCheck);

            // Include students who have marks and are not absent
            if ($mark && (!$registration || $registration->status !== 'absent')) {
                $percentage = $mark->max_marks > 0 ? round(($mark->marks_obtained / $mark->max_marks) * 100, 1) : 0;
                $validMarks[] = [
                    'student_id' => $studentIdCheck,
                    'percentage' => $percentage
                ];
            }
        }

        // Sort by percentage descending (highest marks first)
        usort($validMarks, function($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });

        // Find the rank of the target student (1-based ranking)
        foreach ($validMarks as $index => $markData) {
            if ($markData['student_id'] == $studentId) {
                return ($index + 1) . '/' . count($validMarks);
            }
        }

        return '-';
    }

    /**
     * Get subject wise analysis data for a specific period
     */
    private function getSubjectWiseAnalysisDataForPeriod($academicYearId, $examTypeId, $classId, $streamId, $branchId)
    {
        // Get grade scale for the academic year
        $gradeScale = \App\Models\SchoolGradeScale::active()
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->first();

        // Get all exam class assignments for the selected filters
        $examAssignmentsQuery = \App\Models\ExamClassAssignment::where('exam_type_id', $examTypeId)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->with(['subject', 'classe', 'stream']);

        // Apply class filter if provided
        if ($classId) {
            $examAssignmentsQuery->where('class_id', $classId);
        }

        // Apply stream filter if provided
        if ($streamId) {
            $examAssignmentsQuery->where(function ($query) use ($streamId) {
                $query->where('stream_id', $streamId)
                      ->orWhereNull('stream_id');
            });
        }

        $examAssignments = $examAssignmentsQuery->get();

        if ($examAssignments->isEmpty()) {
            return [
                'subjects' => [],
                'summary' => $this->calculatePeriodSummary([], 0)
            ];
        }

        // Get all students for these assignments
        $assignmentIds = $examAssignments->pluck('id');
        $classIds = $examAssignments->pluck('class_id')->unique();

        $students = \App\Models\School\Student::whereIn('class_id', $classIds)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where('status', 'active');

        // Apply stream filter if provided
        if ($streamId) {
            $students->where('stream_id', $streamId);
        }

        $students = $students->get();

        // Get exam registrations to check attendance status
        $examRegistrations = \App\Models\SchoolExamRegistration::whereIn('exam_class_assignment_id', $assignmentIds)
            ->whereIn('student_id', $students->pluck('id'))
            ->where('exam_type_id', $examTypeId)
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->keyBy(function($registration) {
                return $registration->student_id . '-' . $registration->exam_class_assignment_id;
            });

        // Get marks for these assignments and students
        $marks = \App\Models\SchoolExamMark::whereIn('exam_class_assignment_id', $assignmentIds)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy(function($mark) {
                return $mark->student_id . '-' . $mark->exam_class_assignment_id;
            });

        // Group by subject
        $subjectData = [];

        // Get subjects ordered by subject group sort order if class is selected
        if ($classId) {
            $subjectGroups = \App\Models\School\SubjectGroup::where('class_id', $classId)
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                })
                ->where('is_active', true)
                ->with(['subjects' => function ($query) {
                    $query->orderBy('subject_subject_group.sort_order')
                          ->orderBy('subjects.name');
                }])
                ->get();

            if ($subjectGroups->isNotEmpty()) {
                $orderedSubjects = collect();
                foreach ($subjectGroups as $subjectGroup) {
                    foreach ($subjectGroup->subjects as $subject) {
                        if ($examAssignments->contains('subject_id', $subject->id)) {
                            $orderedSubjects->push($subject);
                        }
                    }
                }
                // Add any remaining subjects not in subject groups
                $subjectsFromAssignments = $examAssignments->pluck('subject')->filter()->unique('id');
                foreach ($subjectsFromAssignments as $subject) {
                    if (!$orderedSubjects->contains('id', $subject->id)) {
                        $orderedSubjects->push($subject);
                    }
                }
                $subjects = $orderedSubjects->unique('id');
            } else {
                // Fallback to original logic
                $subjects = $examAssignments->pluck('subject')->filter()->unique('id')->sortBy('name');
            }
        } else {
            $subjects = $examAssignments->pluck('subject')->filter()->unique('id')->sortBy('name');
        }
        $uniqueStudents = collect(); // Track unique students with valid marks
        $absentStudents = []; // Track absent students

        // First pass: identify students who participated in ALL subjects
        $eligibleStudents = [];
        foreach ($students as $student) {
            $hasAllSubjects = true;
            $studentAbsentSubjects = [];

            foreach ($subjects as $subject) {
                $assignments = $examAssignments->where('subject_id', $subject->id);
                $hasMarkForSubject = false;

                foreach ($assignments as $assignment) {
                    $markKey = $student->id . '-' . $assignment->id;
                    $registrationKey = $student->id . '-' . $assignment->id;

                    $mark = $marks->get($markKey);
                    $registration = $examRegistrations->get($registrationKey);

                    if ($registration) {
                        if (in_array($registration->status, ['registered', 'attended']) && $mark && $mark->marks_obtained !== null) {
                            $hasMarkForSubject = true;
                            break;
                        }
                    } else {
                        // Legacy data - check for mark
                        if ($mark && $mark->marks_obtained !== null) {
                            $hasMarkForSubject = true;
                            break;
                        }
                    }
                }

                if (!$hasMarkForSubject) {
                    $hasAllSubjects = false;
                    $studentAbsentSubjects[] = $subject->name;
                }
            }

            if ($hasAllSubjects) {
                $eligibleStudents[] = $student;
            } else {
                // Track as absent
                $absentStudents[$student->id] = [
                    'student' => $student,
                    'absent_subjects' => $studentAbsentSubjects
                ];
            }
        }

        // Get grade letters from grade scale or use default
        $gradeLetters = ['A', 'B', 'C', 'D', 'F'];
        if ($gradeScale && $gradeScale->grades->isNotEmpty()) {
            $gradeLetters = $gradeScale->grades->sortBy('sort_order')->pluck('grade_letter')->toArray();
        }

        // Second pass: calculate statistics only for eligible students
        foreach ($subjects as $subject) {
            $assignments = $examAssignments->where('subject_id', $subject->id);

            // Initialize grade breakdown dynamically from grade scale
            $gradeBreakdown = [];
            foreach ($gradeLetters as $gradeLetter) {
                $gradeBreakdown[$gradeLetter] = ['female' => 0, 'male' => 0, 'total' => 0];
            }

            $totals = ['female' => 0, 'male' => 0, 'total' => 0];
            $scores = [];

            foreach ($eligibleStudents as $student) {
                $studentMarks = [];

                foreach ($assignments as $assignment) {
                    $markKey = $student->id . '-' . $assignment->id;
                    $mark = $marks->get($markKey);

                    if ($mark && $mark->marks_obtained !== null) {
                        $studentMarks[] = $mark->marks_obtained;
                    }
                }

                if (!empty($studentMarks)) {
                    $averageScore = array_sum($studentMarks) / count($studentMarks);
                    $grade = $gradeScale ? $this->calculateGradeFromScale($averageScore, $gradeScale) : $this->calculateGrade($averageScore);
                    $gender = strtolower($student->gender ?? 'male');

                    if (isset($gradeBreakdown[$grade][$gender])) {
                        $gradeBreakdown[$grade][$gender]++;
                        $gradeBreakdown[$grade]['total']++;
                        $totals[$gender]++;
                        $totals['total']++;
                        $scores[] = $averageScore;
                        $uniqueStudents->put($student->id, $student); // Track unique students
                    }
                }
            }

            if ($totals['total'] > 0) {
                $subjectData[] = [
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->name,
                    'grade_breakdown' => $gradeBreakdown,
                    'totals' => $totals,
                    'average_score' => count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0,
                    'student_count' => $totals['total']
                ];
            }
        }

        return [
            'subjects' => $subjectData,
            'summary' => $this->calculatePeriodSummary($subjectData, $uniqueStudents->count(), $gradeLetters),
            'absent_students' => array_values($absentStudents),
            'grade_letters' => $gradeLetters
        ];
    }

    /**
     * Calculate summary for a period
     */
    private function calculatePeriodSummary($subjects, $totalUniqueStudents = 0, $gradeLetters = ['A', 'B', 'C', 'D', 'F'])
    {
        $totalAverage = 0;
        // Initialize grade totals dynamically from grade letters
        $gradeTotals = [];
        foreach ($gradeLetters as $gradeLetter) {
            $gradeTotals[$gradeLetter] = ['female' => 0, 'male' => 0, 'total' => 0];
        }

        foreach ($subjects as $subject) {
            $totalAverage += $subject['average_score'];

            foreach ($subject['grade_breakdown'] as $grade => $counts) {
                $gradeTotals[$grade]['female'] += $counts['female'];
                $gradeTotals[$grade]['male'] += $counts['male'];
                $gradeTotals[$grade]['total'] += $counts['total'];
            }
        }

        return [
            'total_subjects' => count($subjects),
            'total_students' => $totalUniqueStudents,
            'average_score' => count($subjects) > 0 ? round($totalAverage / count($subjects), 2) : 0,
            'grade_totals' => $gradeTotals
        ];
    }

    /**
     * Calculate comparison between two periods
     */
    private function calculateComparison($period1, $period2)
    {
        $comparison = [];

        // Compare subject by subject
        $subjects1 = collect($period1['subjects'])->keyBy('subject_id');
        $subjects2 = collect($period2['subjects'])->keyBy('subject_id');

        $allSubjectIds = $subjects1->keys()->merge($subjects2->keys())->unique();

        foreach ($allSubjectIds as $subjectId) {
            $subj1 = $subjects1->get($subjectId);
            $subj2 = $subjects2->get($subjectId);

            $comparison[] = [
                'subject_id' => $subjectId,
                'subject_name' => $subj1 ? $subj1['subject_name'] : ($subj2 ? $subj2['subject_name'] : 'Unknown'),
                'period1' => $subj1 ? [
                    'average_score' => $subj1['average_score'],
                    'student_count' => $subj1['student_count'],
                    'grade_breakdown' => $subj1['grade_breakdown']
                ] : null,
                'period2' => $subj2 ? [
                    'average_score' => $subj2['average_score'],
                    'student_count' => $subj2['student_count'],
                    'grade_breakdown' => $subj2['grade_breakdown']
                ] : null,
                'difference' => ($subj1 && $subj2) ? [
                    'average_score' => round($subj2['average_score'] - $subj1['average_score'], 2),
                    'student_count' => $subj2['student_count'] - $subj1['student_count']
                ] : null
            ];
        }

        return $comparison;
    }

    /**
     * Calculate grade based on score (fallback when no grade scale exists)
     */
    /**
     * Calculate grade from score using grade scale
     * Falls back to default grading if no grade scale is available
     */
    private function calculateGradeFromScore($score, $gradeScale = null)
    {
        // Use grade scale if available
        if ($gradeScale) {
            $grade = $gradeScale->getGradeForMark($score);
            if ($grade) {
                return $grade->grade_letter;
            }
        }

        // Fallback: use default grading if no grade scale
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }

    /**
     * Export comparative subject performance report to PDF
     */
    public function exportComparativeSubjectPerformancePdf(Request $request)
    {
        $branchId = session('branch_id');

        // Decode hashed IDs
        try {
            $academicYearId1 = $request->academic_year_id_1;
            $examTypeId1 = $request->exam_type_id_1;
            $academicYearId2 = $request->academic_year_id_2;
            $examTypeId2 = $request->exam_type_id_2;
            $classId = $request->class_id;
            $streamId = $request->stream_id;

            // If numeric, use as is; else decode
            $decodedAcademicYear1 = is_numeric($academicYearId1) ? [$academicYearId1] : ($academicYearId1 ? Hashids::decode($academicYearId1) : []);
            $decodedExamType1 = is_numeric($examTypeId1) ? [$examTypeId1] : ($examTypeId1 ? Hashids::decode($examTypeId1) : []);
            $decodedAcademicYear2 = is_numeric($academicYearId2) ? [$academicYearId2] : ($academicYearId2 ? Hashids::decode($academicYearId2) : []);
            $decodedExamType2 = is_numeric($examTypeId2) ? [$examTypeId2] : ($examTypeId2 ? Hashids::decode($examTypeId2) : []);
            $decodedClass = is_numeric($classId) ? [$classId] : ($classId ? Hashids::decode($classId) : []);
            $decodedStream = is_numeric($streamId) ? [$streamId] : ($streamId ? Hashids::decode($streamId) : []);

            $request->merge([
                'academic_year_id_1' => !empty($decodedAcademicYear1) ? $decodedAcademicYear1[0] : null,
                'exam_type_id_1' => !empty($decodedExamType1) ? $decodedExamType1[0] : null,
                'academic_year_id_2' => !empty($decodedAcademicYear2) ? $decodedAcademicYear2[0] : null,
                'exam_type_id_2' => !empty($decodedExamType2) ? $decodedExamType2[0] : null,
                'class_id' => !empty($decodedClass) ? $decodedClass[0] : null,
                'stream_id' => !empty($decodedStream) ? $decodedStream[0] : null,
            ]);
        } catch (\Exception $e) {
            $request->merge([
                'academic_year_id_1' => null,
                'exam_type_id_1' => null,
                'academic_year_id_2' => null,
                'exam_type_id_2' => null,
                'class_id' => null,
                'stream_id' => null,
            ]);
        }

        // Get filter options
        $classes = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $streams = \App\Models\School\Stream::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $academicYears = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->orderBy('year_name')
            ->get();

        $examTypes = \App\Models\SchoolExamType::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        // Get comparative data
        $comparativeData = $this->getComparativeSubjectPerformanceData($request, $branchId);

        $company = \App\Models\Company::find(auth()->user()->company_id);
        $generatedAt = now();

        // Get filter labels
        $period1AcademicYear = $academicYears->find($request->academic_year_id_1);
        $period1ExamType = $examTypes->find($request->exam_type_id_1);
        $period2AcademicYear = $academicYears->find($request->academic_year_id_2);
        $period2ExamType = $examTypes->find($request->exam_type_id_2);
        $selectedClass = $classes->find($request->class_id);
        $selectedStream = $streams->find($request->stream_id);

        // Extract grade letters from comparative data
        $gradeLetters = $comparativeData['grade_letters'] ?? ['A', 'B', 'C', 'D', 'F'];

        $pdf = \PDF::loadView('school.reports.exports.comparative-subject-performance-pdf', compact(
            'comparativeData',
            'company',
            'generatedAt',
            'period1AcademicYear',
            'period1ExamType',
            'period2AcademicYear',
            'period2ExamType',
            'selectedClass',
            'selectedStream',
            'gradeLetters'
        ));

        $pdf->setPaper('a4', 'landscape');
        $filename = 'comparative-subject-performance-' . date('Y-m-d-H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export comparative subject performance report to Excel
     */
    public function exportComparativeSubjectPerformanceExcel(Request $request)
    {
        $branchId = session('branch_id');

        // Decode hashed IDs
        try {
            $academicYearId1 = $request->academic_year_id_1;
            $examTypeId1 = $request->exam_type_id_1;
            $academicYearId2 = $request->academic_year_id_2;
            $examTypeId2 = $request->exam_type_id_2;
            $classId = $request->class_id;
            $streamId = $request->stream_id;

            // If numeric, use as is; else decode
            $decodedAcademicYear1 = is_numeric($academicYearId1) ? [$academicYearId1] : ($academicYearId1 ? Hashids::decode($academicYearId1) : []);
            $decodedExamType1 = is_numeric($examTypeId1) ? [$examTypeId1] : ($examTypeId1 ? Hashids::decode($examTypeId1) : []);
            $decodedAcademicYear2 = is_numeric($academicYearId2) ? [$academicYearId2] : ($academicYearId2 ? Hashids::decode($academicYearId2) : []);
            $decodedExamType2 = is_numeric($examTypeId2) ? [$examTypeId2] : ($examTypeId2 ? Hashids::decode($examTypeId2) : []);
            $decodedClass = is_numeric($classId) ? [$classId] : ($classId ? Hashids::decode($classId) : []);
            $decodedStream = is_numeric($streamId) ? [$streamId] : ($streamId ? Hashids::decode($streamId) : []);

            $request->merge([
                'academic_year_id_1' => !empty($decodedAcademicYear1) ? $decodedAcademicYear1[0] : null,
                'exam_type_id_1' => !empty($decodedExamType1) ? $decodedExamType1[0] : null,
                'academic_year_id_2' => !empty($decodedAcademicYear2) ? $decodedAcademicYear2[0] : null,
                'exam_type_id_2' => !empty($decodedExamType2) ? $decodedExamType2[0] : null,
                'class_id' => !empty($decodedClass) ? $decodedClass[0] : null,
                'stream_id' => !empty($decodedStream) ? $decodedStream[0] : null,
            ]);
        } catch (\Exception $e) {
            $request->merge([
                'academic_year_id_1' => null,
                'exam_type_id_1' => null,
                'academic_year_id_2' => null,
                'exam_type_id_2' => null,
                'class_id' => null,
                'stream_id' => null,
            ]);
        }

        // Get filter options
        $classes = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $streams = \App\Models\School\Stream::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $academicYears = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->orderBy('year_name')
            ->get();

        $examTypes = \App\Models\SchoolExamType::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        // Get comparative data
        $comparativeData = $this->getComparativeSubjectPerformanceData($request, $branchId);

        return Excel::download(new class($comparativeData, $request, $classes, $streams, $academicYears, $examTypes) implements FromView, ShouldAutoSize {
            protected $data;
            protected $request;
            protected $classes;
            protected $streams;
            protected $academicYears;
            protected $examTypes;

            public function __construct($data, $request, $classes, $streams, $academicYears, $examTypes)
            {
                $this->data = $data;
                $this->request = $request;
                $this->classes = $classes;
                $this->streams = $streams;
                $this->academicYears = $academicYears;
                $this->examTypes = $examTypes;
            }

            public function view(): View
            {
                $comparativeData = $this->data;
                $company = \App\Models\Company::find(auth()->user()->company_id);
                $generatedAt = now();

                // Get filter labels
                $period1AcademicYear = $this->academicYears->find($this->request->academic_year_id_1);
                $period1ExamType = $this->examTypes->find($this->request->exam_type_id_1);
                $period2AcademicYear = $this->academicYears->find($this->request->academic_year_id_2);
                $period2ExamType = $this->examTypes->find($this->request->exam_type_id_2);
                $selectedClass = $this->classes->find($this->request->class_id);
                $selectedStream = $this->streams->find($this->request->stream_id);

                // Extract grade letters from comparative data
                $gradeLetters = $comparativeData['grade_letters'] ?? ['A', 'B', 'C', 'D', 'F'];

                return view('school.reports.exports.comparative-subject-performance-excel', compact(
                    'comparativeData',
                    'company',
                    'generatedAt',
                    'period1AcademicYear',
                    'period1ExamType',
                    'period2AcademicYear',
                    'period2ExamType',
                    'selectedClass',
                    'selectedStream',
                    'gradeLetters'
                ));
            }
        }, 'comparative-subject-performance-' . date('Y-m-d-H-i-s') . '.xlsx');
    }

    /**
     * Display student subject performance and progress analysis report
     */
    public function studentSubjectPerformance(Request $request)
    {
        try {
            $branchId = session('branch_id');

            // Store original hashed values for form population
            $selectedAcademicYear1 = $request->academic_year_id_1;
            $selectedAcademicYear2 = $request->academic_year_id_2;
            $selectedExamType1 = $request->exam_type_id_1;
            $selectedExamType2 = $request->exam_type_id_2;
            $selectedClass = $request->class_id;
            $selectedStream = $request->stream_id;

            // Decode hashed IDs
            try {
                $academicYearId1 = $request->academic_year_id_1;
                $examTypeId1 = $request->exam_type_id_1;
                $academicYearId2 = $request->academic_year_id_2;
                $examTypeId2 = $request->exam_type_id_2;
                $classId = $request->class_id;
                $streamId = $request->stream_id;

                // If numeric, use as is; else decode
                $decodedAcademicYear1 = is_numeric($academicYearId1) ? [$academicYearId1] : ($academicYearId1 ? Hashids::decode($academicYearId1) : []);
                $decodedExamType1 = is_numeric($examTypeId1) ? [$examTypeId1] : ($examTypeId1 ? Hashids::decode($examTypeId1) : []);
                $decodedAcademicYear2 = is_numeric($academicYearId2) ? [$academicYearId2] : ($academicYearId2 ? Hashids::decode($academicYearId2) : []);
                $decodedExamType2 = is_numeric($examTypeId2) ? [$examTypeId2] : ($examTypeId2 ? Hashids::decode($examTypeId2) : []);
                $decodedClass = is_numeric($classId) ? [$classId] : ($classId ? Hashids::decode($classId) : []);
                $decodedStream = is_numeric($streamId) ? [$streamId] : ($streamId ? Hashids::decode($streamId) : []);

                $request->merge([
                    'academic_year_id_1' => !empty($decodedAcademicYear1) ? $decodedAcademicYear1[0] : null,
                    'exam_type_id_1' => !empty($decodedExamType1) ? $decodedExamType1[0] : null,
                    'academic_year_id_2' => !empty($decodedAcademicYear2) ? $decodedAcademicYear2[0] : null,
                    'exam_type_id_2' => !empty($decodedExamType2) ? $decodedExamType2[0] : null,
                    'class_id' => !empty($decodedClass) ? $decodedClass[0] : null,
                    'stream_id' => !empty($decodedStream) ? $decodedStream[0] : null,
                ]);
            } catch (\Exception $e) {
                $request->merge([
                    'academic_year_id_1' => null,
                    'exam_type_id_1' => null,
                    'academic_year_id_2' => null,
                    'exam_type_id_2' => null,
                    'class_id' => null,
                    'stream_id' => null,
                ]);
            }

            // Get filter options
            $classes = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            $streams = \App\Models\School\Stream::where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            $academicYears = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
                ->orderBy('year_name')
                ->get();

            $examTypes = \App\Models\SchoolExamType::where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            // Get student subject performance data
            $studentPerformanceData = $this->getStudentSubjectPerformanceData($request, $branchId);

            // Handle PDF export
            if ($request->export == 'pdf') {
                return $this->exportStudentSubjectPerformancePdf($request);
            }

            // Handle Excel export
            if ($request->export == 'excel') {
                return $this->exportStudentSubjectPerformanceExcel($request);
            }

            return view('school.reports.student-subject-performance', compact(
                'classes',
                'streams',
                'academicYears',
                'examTypes',
                'studentPerformanceData',
                'selectedAcademicYear1',
                'selectedAcademicYear2',
                'selectedExamType1',
                'selectedExamType2',
                'selectedClass',
                'selectedStream'
            ));
        } catch (\Exception $e) {
            \Log::error('Student Subject Performance error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);

            return redirect()->back()->with('error', 'An error occurred while generating the report. Please try again.');
        }
    }

    /**
     * Export student subject performance report to PDF
     */
    public function exportStudentSubjectPerformancePdf(Request $request)
    {
        $branchId = session('branch_id');

        // Decode hashed IDs
        try {
            $academicYearId1 = $request->academic_year_id_1;
            $examTypeId1 = $request->exam_type_id_1;
            $academicYearId2 = $request->academic_year_id_2;
            $examTypeId2 = $request->exam_type_id_2;
            $classId = $request->class_id;
            $streamId = $request->stream_id;

            // If numeric, use as is; else decode
            $decodedAcademicYear1 = is_numeric($academicYearId1) ? [$academicYearId1] : ($academicYearId1 ? Hashids::decode($academicYearId1) : []);
            $decodedExamType1 = is_numeric($examTypeId1) ? [$examTypeId1] : ($examTypeId1 ? Hashids::decode($examTypeId1) : []);
            $decodedAcademicYear2 = is_numeric($academicYearId2) ? [$academicYearId2] : ($academicYearId2 ? Hashids::decode($academicYearId2) : []);
            $decodedExamType2 = is_numeric($examTypeId2) ? [$examTypeId2] : ($examTypeId2 ? Hashids::decode($examTypeId2) : []);
            $decodedClass = is_numeric($classId) ? [$classId] : ($classId ? Hashids::decode($classId) : []);
            $decodedStream = is_numeric($streamId) ? [$streamId] : ($streamId ? Hashids::decode($streamId) : []);

            $request->merge([
                'academic_year_id_1' => !empty($decodedAcademicYear1) ? $decodedAcademicYear1[0] : null,
                'exam_type_id_1' => !empty($decodedExamType1) ? $decodedExamType1[0] : null,
                'academic_year_id_2' => !empty($decodedAcademicYear2) ? $decodedAcademicYear2[0] : null,
                'exam_type_id_2' => !empty($decodedExamType2) ? $decodedExamType2[0] : null,
                'class_id' => !empty($decodedClass) ? $decodedClass[0] : null,
                'stream_id' => !empty($decodedStream) ? $decodedStream[0] : null,
            ]);
        } catch (\Exception $e) {
            $request->merge([
                'academic_year_id_1' => null,
                'exam_type_id_1' => null,
                'academic_year_id_2' => null,
                'exam_type_id_2' => null,
                'class_id' => null,
                'stream_id' => null,
            ]);
        }

        // Get filter options
        $classes = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $streams = \App\Models\School\Stream::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $academicYears = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->orderBy('year_name')
            ->get();

        $examTypes = \App\Models\SchoolExamType::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        // Get student subject performance data
        $studentPerformanceData = $this->getStudentSubjectPerformanceData($request, $branchId);

        $company = \App\Models\Company::find(auth()->user()->company_id);
        $generatedAt = now();

        // Get filter labels
        $period1AcademicYear = $academicYears->find($request->academic_year_id_1);
        $period1ExamType = $examTypes->find($request->exam_type_id_1);
        $period2AcademicYear = $academicYears->find($request->academic_year_id_2);
        $period2ExamType = $examTypes->find($request->exam_type_id_2);
        $selectedClass = $classes->find($request->class_id);
        $selectedStream = $streams->find($request->stream_id);

        $pdf = \PDF::loadView('school.reports.exports.student-subject-performance-pdf', compact(
            'studentPerformanceData',
            'company',
            'generatedAt',
            'period1AcademicYear',
            'period1ExamType',
            'period2AcademicYear',
            'period2ExamType',
            'selectedClass',
            'selectedStream'
        ));

        $pdf->setPaper('a4', 'landscape');
        $filename = 'student-subject-performance-' . date('Y-m-d-H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export student subject performance report to Excel
     */
    public function exportStudentSubjectPerformanceExcel(Request $request)
    {
        $branchId = session('branch_id');

        // Decode hashed IDs
        try {
            $academicYearId1 = $request->academic_year_id_1;
            $examTypeId1 = $request->exam_type_id_1;
            $academicYearId2 = $request->academic_year_id_2;
            $examTypeId2 = $request->exam_type_id_2;
            $classId = $request->class_id;
            $streamId = $request->stream_id;

            // If numeric, use as is; else decode
            $decodedAcademicYear1 = is_numeric($academicYearId1) ? [$academicYearId1] : ($academicYearId1 ? Hashids::decode($academicYearId1) : []);
            $decodedExamType1 = is_numeric($examTypeId1) ? [$examTypeId1] : ($examTypeId1 ? Hashids::decode($examTypeId1) : []);
            $decodedAcademicYear2 = is_numeric($academicYearId2) ? [$academicYearId2] : ($academicYearId2 ? Hashids::decode($academicYearId2) : []);
            $decodedExamType2 = is_numeric($examTypeId2) ? [$examTypeId2] : ($examTypeId2 ? Hashids::decode($examTypeId2) : []);
            $decodedClass = is_numeric($classId) ? [$classId] : ($classId ? Hashids::decode($classId) : []);
            $decodedStream = is_numeric($streamId) ? [$streamId] : ($streamId ? Hashids::decode($streamId) : []);

            $request->merge([
                'academic_year_id_1' => !empty($decodedAcademicYear1) ? $decodedAcademicYear1[0] : null,
                'exam_type_id_1' => !empty($decodedExamType1) ? $decodedExamType1[0] : null,
                'academic_year_id_2' => !empty($decodedAcademicYear2) ? $decodedAcademicYear2[0] : null,
                'exam_type_id_2' => !empty($decodedExamType2) ? $decodedExamType2[0] : null,
                'class_id' => !empty($decodedClass) ? $decodedClass[0] : null,
                'stream_id' => !empty($decodedStream) ? $decodedStream[0] : null,
            ]);
        } catch (\Exception $e) {
            $request->merge([
                'academic_year_id_1' => null,
                'exam_type_id_1' => null,
                'academic_year_id_2' => null,
                'exam_type_id_2' => null,
                'class_id' => null,
                'stream_id' => null,
            ]);
        }

        // Get filter options
        $classes = \App\Models\School\Classe::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $streams = \App\Models\School\Stream::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $academicYears = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)
            ->orderBy('year_name')
            ->get();

        $examTypes = \App\Models\SchoolExamType::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        // Get student subject performance data
        $studentPerformanceData = $this->getStudentSubjectPerformanceData($request, $branchId);

        return Excel::download(new class($studentPerformanceData, $request, $classes, $streams, $academicYears, $examTypes) implements FromView, ShouldAutoSize {
            protected $data;
            protected $request;
            protected $classes;
            protected $streams;
            protected $academicYears;
            protected $examTypes;

            public function __construct($data, $request, $classes, $streams, $academicYears, $examTypes)
            {
                $this->data = $data;
                $this->request = $request;
                $this->classes = $classes;
                $this->streams = $streams;
                $this->academicYears = $academicYears;
                $this->examTypes = $examTypes;
            }

            public function view(): View
            {
                $studentPerformanceData = $this->data;
                $company = \App\Models\Company::find(auth()->user()->company_id);
                $generatedAt = now();

                // Get filter labels
                $period1AcademicYear = $this->academicYears->find($this->request->academic_year_id_1);
                $period1ExamType = $this->examTypes->find($this->request->exam_type_id_1);
                $period2AcademicYear = $this->academicYears->find($this->request->academic_year_id_2);
                $period2ExamType = $this->examTypes->find($this->request->exam_type_id_2);
                $selectedClass = $this->classes->find($this->request->class_id);
                $selectedStream = $this->streams->find($this->request->stream_id);

                return view('school.reports.exports.student-subject-performance-excel', compact(
                    'studentPerformanceData',
                    'company',
                    'generatedAt',
                    'period1AcademicYear',
                    'period1ExamType',
                    'period2AcademicYear',
                    'period2ExamType',
                    'selectedClass',
                    'selectedStream'
                ));
            }
        }, 'student-subject-performance-' . date('Y-m-d-H-i-s') . '.xlsx');
    }

    private function getFeePaymentData($branchId, $academicYearId = null, $classId = null, $streamId = null, $quarter = null, $status = null)
    {
        // Get fee invoices with payment information
        $query = \App\Models\FeeInvoice::where('company_id', auth()->user()->company_id)
            ->with(['student.stream', 'items', 'classe', 'academicYear']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($streamId) {
            $query->whereHas('student', function ($q) use ($streamId) {
                $q->where('stream_id', $streamId);
            });
        }

        if ($quarter) {
            $query->where('period', $quarter);
        }

        if ($status) {
            switch ($status) {
                case 'issued':
                    $query->where('paid_amount', '=', 0)
                          ->where('due_date', '>=', now());
                    break;
                case 'partial_paid':
                    $query->where('paid_amount', '>', 0)
                          ->where('paid_amount', '<', \DB::raw('total_amount'));
                    break;
                case 'paid':
                    $query->whereColumn('paid_amount', '>=', 'total_amount');
                    break;
                case 'overdue':
                    $query->whereColumn('paid_amount', '<', 'total_amount')
                          ->where('due_date', '<', now());
                    break;
            }
        }

        $feeInvoices = $query->get();

        // Calculate summary statistics
        $totalInvoices = $feeInvoices->count();
        $totalAmount = $feeInvoices->sum('total_amount');
        $totalPaid = $feeInvoices->sum('paid_amount');
        $totalOutstanding = $totalAmount - $totalPaid;

        $paidInvoices = $feeInvoices->filter(function ($invoice) {
            return $invoice->paid_amount >= $invoice->total_amount;
        })->count();

        $partialPaidInvoices = $feeInvoices->filter(function ($invoice) {
            return $invoice->paid_amount > 0 && $invoice->paid_amount < $invoice->total_amount;
        })->count();

        $issuedInvoices = $feeInvoices->filter(function ($invoice) {
            return $invoice->paid_amount == 0 && $invoice->due_date >= now();
        })->count();

        $overdueInvoices = $feeInvoices->filter(function ($invoice) {
            return $invoice->paid_amount < $invoice->total_amount && $invoice->due_date < now();
        })->count();

        // Group by class
        $classSummary = $feeInvoices->groupBy(function ($invoice) {
            return $invoice->classe ? $invoice->classe->name : 'N/A';
        })->map(function ($invoices) {
            return [
                'total_invoices' => $invoices->count(),
                'total_amount' => $invoices->sum('total_amount'),
                'total_paid' => $invoices->sum('paid_amount'),
                'total_outstanding' => $invoices->sum('total_amount') - $invoices->sum('paid_amount'),
                'paid_count' => $invoices->filter(fn($i) => $i->paid_amount >= $i->total_amount)->count(),
                'partial_paid_count' => $invoices->filter(fn($i) => $i->paid_amount > 0 && $i->paid_amount < $i->total_amount)->count(),
                'issued_count' => $invoices->filter(fn($i) => $i->paid_amount == 0 && $i->due_date >= now())->count(),
                'overdue_count' => $invoices->filter(fn($i) => $i->paid_amount < $i->total_amount && $i->due_date < now())->count(),
            ];
        });

        return [
            'summary' => [
                'total_invoices' => $totalInvoices,
                'total_amount' => $totalAmount,
                'total_paid' => $totalPaid,
                'total_outstanding' => $totalOutstanding,
                'paid_invoices' => $paidInvoices,
                'partial_paid_invoices' => $partialPaidInvoices,
                'issued_invoices' => $issuedInvoices,
                'overdue_invoices' => $overdueInvoices,
            ],
            'class_summary' => $classSummary,
            'invoices' => $feeInvoices,
        ];
    }

    private function getDetailedFeeCollectionData($branchId, $academicYearId = null, $classId = null, $period = null)
    {
        // Map period string to integer
        $periodMap = [
            'Q1' => 1,
            'Q2' => 2,
            'Q3' => 3,
            'Q4' => 4,
            'Annual' => 5,
            'Term 1' => 6,
            'Term 2' => 7,
        ];
        $periodInt = $period ? ($periodMap[$period] ?? null) : null;

        // Get students with their fee payment information
        $query = \App\Models\School\Student::where('company_id', auth()->user()->company_id)
            ->with(['class', 'stream', 'feeInvoices' => function ($q) use ($academicYearId, $periodInt) {
                if ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                }
                if ($periodInt) {
                    $q->where('period', $periodInt);
                }
                $q->with('payments');
            }]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $students = $query->get();

        // Group by class and stream
        $feeCollectionData = $students->groupBy(function ($student) {
            return $student->class ? $student->class->name : 'N/A';
        })->map(function ($classStudents) {
            return $classStudents->groupBy(function ($student) {
                return $student->stream ? $student->stream->name : 'N/A';
            })->map(function ($streamStudents) {
                // Only count students who have fee invoices
                $studentsWithInvoices = $streamStudents->filter(function ($student) {
                    return $student->feeInvoices->isNotEmpty();
                });

                $totalStudents = $studentsWithInvoices->count();
                $paidFullFees = 0;
                $outstandingFees = 0;

                $paidFullStudents = collect();
                $outstandingStudents = collect();

                foreach ($studentsWithInvoices as $student) {
                    $hasOutstanding = false;
                    foreach ($student->feeInvoices as $invoice) {
                        if ($invoice->paid_amount < $invoice->total_amount) {
                            $hasOutstanding = true;
                            break;
                        }
                    }
                    if ($hasOutstanding) {
                        $outstandingFees++;
                        $outstandingStudents->push($student);
                    } else {
                        $paidFullFees++;
                        $paidFullStudents->push($student);
                    }
                }

                return [
                    'total_students' => $totalStudents,
                    'paid_full_fees' => $paidFullFees,
                    'outstanding_fees' => $outstandingFees,
                    'paid_full_students' => $paidFullStudents,
                    'outstanding_students' => $outstandingStudents,
                ];
            })->pipe(function ($streams) {
                // Calculate class totals
                $classTotalStudents = $streams->sum('total_students');
                $classPaidFull = $streams->sum('paid_full_fees');
                $classOutstanding = $streams->sum('outstanding_fees');
                $classPaidStudents = collect();
                $classOutstandingStudents = collect();

                $streams->each(function ($stream) use (&$classPaidStudents, &$classOutstandingStudents) {
                    $classPaidStudents = $classPaidStudents->merge($stream['paid_full_students']);
                    $classOutstandingStudents = $classOutstandingStudents->merge($stream['outstanding_students']);
                });

                $streams['class_totals'] = [
                    'total_students' => $classTotalStudents,
                    'paid_full_fees' => $classPaidFull,
                    'outstanding_fees' => $classOutstanding,
                    'paid_full_students' => $classPaidStudents,
                    'outstanding_students' => $classOutstandingStudents,
                ];

                return $streams;
            });
        });

        return $feeCollectionData;
    }

    private function exportFeeReport(Request $request)
    {
        $branchId = session('branch_id') ?: auth()->user()->branch_id;
        $academicYearId = $request->academic_year_id;
        $classId = $request->class_id;
        $streamId = $request->stream_id;
        $quarter = $request->quarter;
        $status = $request->status;
        $exportType = $request->export;

        $feeData = $this->getFeePaymentData($branchId, $academicYearId, $classId, $streamId, $quarter, $status);

        if ($exportType === 'pdf') {
            $pdf = PDF::loadView('school.reports.exports.fee-report-pdf', compact('feeData', 'academicYearId', 'classId', 'streamId', 'quarter', 'status'));
            return $pdf->download('fee-payment-status-report-' . date('Y-m-d-H-i-s') . '.pdf');
        } elseif ($exportType === 'excel') {
            return Excel::download(new \App\Exports\FeeReportExport($feeData, $academicYearId, $classId, $streamId, $quarter, $status), 'fee-payment-status-report-' . date('Y-m-d-H-i-s') . '.xlsx');
        }

        return redirect()->back()->with('error', 'Invalid export type');
    }

    private function exportDetailedFeeCollection(Request $request)
    {
        $branchId = session('branch_id') ?: auth()->user()->branch_id;
        $academicYearId = $request->academic_year_id ? (is_numeric($request->academic_year_id) ? $request->academic_year_id : (Hashids::decode($request->academic_year_id)[0] ?? null)) : null;
        $classId = $request->class_id ? (is_numeric($request->class_id) ? $request->class_id : (Hashids::decode($request->class_id)[0] ?? null)) : null;
        $period = $request->period; // Q1, Q2, Q3, Q4, Term 1, Term 2, Annual, or null for all
        $exportType = $request->export;

        $feeCollectionData = $this->getDetailedFeeCollectionData($branchId, $academicYearId, $classId, $period);

        if ($exportType === 'pdf') {
            $pdf = PDF::loadView('school.reports.exports.detailed-fee-collection-pdf', compact('feeCollectionData', 'academicYearId', 'classId'));
            return $pdf->download('detailed-fee-collection-report-' . hash('sha256', uniqid()) . '.pdf');
        } elseif ($exportType === 'excel') {
            return Excel::download(new \App\Exports\DetailedFeeCollectionExport($feeCollectionData, $academicYearId, $classId), 'detailed-fee-collection-report-' . hash('sha256', uniqid()) . '.xlsx');
        }

        return redirect()->back()->with('error', 'Invalid export type');
    }

    private function exportStudentDetailsPDF(Request $request)
    {
        $title = $request->title;
        $students = json_decode($request->students, true);

        $pdf = PDF::loadView('school.reports.exports.student-details-pdf', compact('title', 'students'));

        // Return PDF as response for AJAX download
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="student-details-' . hash('sha256', uniqid()) . '.pdf"'
        ]);
    }

    /**
     * Subject-Wise Attendance Report
     */
    public function subjectWiseAttendanceReport(Request $request)
    {
        try {
            $branchId = session('branch_id') ?: auth()->user()->branch_id;
            $companyId = auth()->user()->company_id;

            // Get filters
            $academicYearId = $request->academic_year_id;
            $classId = $request->class_id;
            $streamId = $request->stream_id;
            $subjectId = $request->subject_id;

            // Get classes for filter
            $classes = \App\Models\School\Classe::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            // Get streams for filter
            $streams = \App\Models\School\Stream::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            // Get academic years for filter
            $academicYears = \App\Models\School\AcademicYear::where('company_id', $companyId)
                ->orderBy('year_name', 'desc')
                ->get();

            // Get current academic year for default
            $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', $companyId)
                ->where('is_current', true)
                ->first();

            if (!$academicYearId && $currentAcademicYear) {
                $academicYearId = $currentAcademicYear->id;
            }

            // Get subjects for filter (based on selected class/stream/academic year)
            $subjectsQuery = \App\Models\School\Subject::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('is_active', true);

            // Filter subjects by class/stream if selected
            if ($classId || $streamId || $academicYearId) {
                $subjectsQuery->whereHas('subjectTeachers', function ($q) use ($classId, $streamId, $academicYearId) {
                    if ($classId) {
                        $q->where('class_id', $classId);
                    }
                    if ($streamId) {
                        $q->where('stream_id', $streamId);
                    }
                    if ($academicYearId) {
                        $q->where('academic_year_id', $academicYearId);
                    }
                });
            }

            $subjects = $subjectsQuery->orderBy('name')->get();

            // Get attendance data
            $attendanceData = $this->getSubjectWiseAttendanceData($branchId, $companyId, $academicYearId, $classId, $streamId, $subjectId);

            return view('school.reports.subject-wise-attendance', compact(
                'classes',
                'streams',
                'academicYears',
                'subjects',
                'currentAcademicYear',
                'attendanceData',
                'academicYearId',
                'classId',
                'streamId',
                'subjectId'
            ));
        } catch (\Exception $e) {
            \Log::error('Subject-Wise Attendance Report error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);

            return redirect()->back()->with('error', 'An error occurred while generating the report. Please try again.');
        }
    }

    /**
     * Get subject-wise attendance data
     */
    private function getSubjectWiseAttendanceData($branchId, $companyId, $academicYearId, $classId, $streamId, $subjectId)
    {
        // Get subject teachers based on filters
        $subjectTeachersQuery = \App\Models\School\SubjectTeacher::whereHas('subject', function ($q) use ($companyId, $branchId) {
                $q->where('company_id', $companyId);
                if ($branchId) {
                    $q->where(function ($subQ) use ($branchId) {
                        $subQ->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                }
            })
            ->where(function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                }
            })
            ->where('is_active', true)
            ->with(['subject', 'classe', 'stream', 'academicYear']);

        if ($academicYearId) {
            $subjectTeachersQuery->where('academic_year_id', $academicYearId);
        }
        if ($classId) {
            $subjectTeachersQuery->where('class_id', $classId);
        }
        if ($streamId) {
            $subjectTeachersQuery->where('stream_id', $streamId);
        }
        if ($subjectId) {
            $subjectTeachersQuery->where('subject_id', $subjectId);
        }

        $subjectTeachers = $subjectTeachersQuery->get();

        // Group by subject
        $subjectData = [];

        foreach ($subjectTeachers as $subjectTeacher) {
            $subject = $subjectTeacher->subject;
            $subjectKey = $subject->id;

            if (!isset($subjectData[$subjectKey])) {
                $subjectData[$subjectKey] = [
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->name,
                    'subject_code' => $subject->code,
                    'classes' => [],
                    'total_sessions' => 0,
                    'total_students' => 0,
                    'total_present' => 0,
                    'total_absent' => 0,
                    'total_late' => 0,
                    'total_sick' => 0,
                ];
            }

            // Get students for this class/stream
            $studentsQuery = \App\Models\School\Student::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('class_id', $subjectTeacher->class_id)
                ->where('status', 'active');

            if ($subjectTeacher->stream_id) {
                $studentsQuery->where('stream_id', $subjectTeacher->stream_id);
            }

            if ($academicYearId) {
                $studentsQuery->where('academic_year_id', $academicYearId);
            }

            $students = $studentsQuery->get();
            $studentIds = $students->pluck('id')->toArray();

            // Get attendance sessions for this class/stream
            $sessionsQuery = \App\Models\School\AttendanceSession::where('class_id', $subjectTeacher->class_id)
                ->where('status', 'active');

            if ($subjectTeacher->stream_id) {
                $sessionsQuery->where('stream_id', $subjectTeacher->stream_id);
            }

            if ($academicYearId) {
                $sessionsQuery->where('academic_year_id', $academicYearId);
            }

            $sessions = $sessionsQuery->get();
            $sessionIds = $sessions->pluck('id')->toArray();

            // Get attendance records
            $attendances = \App\Models\School\StudentAttendance::whereIn('attendance_session_id', $sessionIds)
                ->whereIn('student_id', $studentIds)
                ->get();

            // Calculate statistics
            $classKey = $subjectTeacher->class_id . '_' . ($subjectTeacher->stream_id ?? '0');
            
            if (!isset($subjectData[$subjectKey]['classes'][$classKey])) {
                $subjectData[$subjectKey]['classes'][$classKey] = [
                    'class_name' => $subjectTeacher->classe->name ?? 'N/A',
                    'stream_name' => $subjectTeacher->stream->name ?? 'N/A',
                    'sessions' => count($sessionIds),
                    'students' => count($studentIds),
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                    'sick' => 0,
                ];
            }

            // Count attendance by status
            foreach ($attendances as $attendance) {
                switch ($attendance->status) {
                    case 'present':
                        $subjectData[$subjectKey]['classes'][$classKey]['present']++;
                        $subjectData[$subjectKey]['total_present']++;
                        break;
                    case 'absent':
                        $subjectData[$subjectKey]['classes'][$classKey]['absent']++;
                        $subjectData[$subjectKey]['total_absent']++;
                        break;
                    case 'late':
                        $subjectData[$subjectKey]['classes'][$classKey]['late']++;
                        $subjectData[$subjectKey]['total_late']++;
                        break;
                    case 'sick':
                        $subjectData[$subjectKey]['classes'][$classKey]['sick']++;
                        $subjectData[$subjectKey]['total_sick']++;
                        break;
                }
            }

            $subjectData[$subjectKey]['total_sessions'] += count($sessionIds);
            $subjectData[$subjectKey]['total_students'] = max($subjectData[$subjectKey]['total_students'], count($studentIds));
        }

        // Calculate attendance rates
        foreach ($subjectData as $key => $data) {
            $totalRecords = $data['total_present'] + $data['total_absent'] + $data['total_late'] + $data['total_sick'];
            $subjectData[$key]['attendance_rate'] = $totalRecords > 0 
                ? round(($data['total_present'] / $totalRecords) * 100, 2) 
                : 0;
            $subjectData[$key]['total_records'] = $totalRecords;

            // Calculate rates for each class
            foreach ($subjectData[$key]['classes'] as $classKey => $classData) {
                $classTotal = $classData['present'] + $classData['absent'] + $classData['late'] + $classData['sick'];
                $subjectData[$key]['classes'][$classKey]['attendance_rate'] = $classTotal > 0 
                    ? round(($classData['present'] / $classTotal) * 100, 2) 
                    : 0;
                $subjectData[$key]['classes'][$classKey]['total_records'] = $classTotal;
            }
        }

        return array_values($subjectData);
    }

    /**
     * Fee Aging Report
     */
    public function feeAgingReport(Request $request)
    {
        try {
            $branchId = session('branch_id') ?: auth()->user()->branch_id;
            $companyId = auth()->user()->company_id;

            // Get filters
            $academicYearId = $request->academic_year_id;
            $classId = $request->class_id;
            $streamId = $request->stream_id;
            $feeGroupId = $request->fee_group_id;
            $asOfDate = $request->as_of_date ?: now()->format('Y-m-d');
            $period = $request->period; // Q1, Q2, Q3, Q4, Term 1, Term 2, Annual, or null for all

            // Get classes for filter
            $classes = \App\Models\School\Classe::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            // Get streams for filter
            $streams = \App\Models\School\Stream::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            // Get academic years for filter
            $academicYears = \App\Models\School\AcademicYear::where('company_id', $companyId)
                ->orderBy('year_name', 'desc')
                ->get();

            // Get current academic year for default
            $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', $companyId)
                ->where('is_current', true)
                ->first();

            if (!$academicYearId && $currentAcademicYear) {
                $academicYearId = $currentAcademicYear->id;
            }

            // Get fee groups for filter
            $feeGroups = \App\Models\FeeGroup::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            // Get fee aging data
            $agingData = $this->getFeeAgingData($branchId, $companyId, $academicYearId, $classId, $streamId, $feeGroupId, $asOfDate, $period);

            return view('school.reports.fee-aging', compact(
                'classes',
                'streams',
                'academicYears',
                'period',
                'feeGroups',
                'currentAcademicYear',
                'agingData',
                'academicYearId',
                'classId',
                'streamId',
                'feeGroupId',
                'asOfDate'
            ));
        } catch (\Exception $e) {
            \Log::error('Fee Aging Report error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);

            return redirect()->back()->with('error', 'An error occurred while generating the report. Please try again.');
        }
    }

    /**
     * Get fee aging data
     */
    private function getFeeAgingData($branchId, $companyId, $academicYearId, $classId, $streamId, $feeGroupId, $asOfDate, $period = null)
    {
        $asOfDateCarbon = \Carbon\Carbon::parse($asOfDate);

        // Map period string to integer
        $periodMap = [
            'Q1' => 1,
            'Q2' => 2,
            'Q3' => 3,
            'Q4' => 4,
            'Annual' => 5,
            'Term 1' => 6,
            'Term 2' => 7,
        ];
        $periodInt = $period ? ($periodMap[$period] ?? null) : null;

        // Get outstanding invoices
        $invoicesQuery = \App\Models\FeeInvoice::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('due_date');

        if ($academicYearId) {
            $invoicesQuery->where('academic_year_id', $academicYearId);
        }

        if ($classId) {
            $invoicesQuery->where('class_id', $classId);
        }

        if ($streamId) {
            $invoicesQuery->whereHas('student', function ($q) use ($streamId) {
                $q->where('stream_id', $streamId);
            });
        }

        if ($feeGroupId) {
            $invoicesQuery->where('fee_group_id', $feeGroupId);
        }

        if ($periodInt) {
            $invoicesQuery->where('period', $periodInt);
        }

        // Eager load relationships
        $invoicesQuery->with(['student.stream', 'student', 'classe', 'academicYear', 'feeGroup']);

        $invoices = $invoicesQuery->get();

        // Group by fee group
        $feeGroupData = [];

        foreach ($invoices as $invoice) {
            $outstandingAmount = $invoice->total_amount - ($invoice->paid_amount ?? 0);
            
            if ($outstandingAmount <= 0) {
                continue;
            }

            $dueDate = \Carbon\Carbon::parse($invoice->due_date);
            $daysOverdue = $asOfDateCarbon->diffInDays($dueDate, false);
            
            // Determine aging bucket
            $agingBucket = 'current';
            if ($daysOverdue < 0) {
                // Overdue (due date is in the past)
                $daysOverdue = abs($daysOverdue);
                if ($daysOverdue <= 30) {
                    $agingBucket = '0-30';
                } elseif ($daysOverdue <= 60) {
                    $agingBucket = '31-60';
                } elseif ($daysOverdue <= 90) {
                    $agingBucket = '61-90';
                } else {
                    $agingBucket = '91+';
                }
            } else {
                // Not yet due (current)
                $daysOverdue = 0;
            }

            $feeGroupKey = $invoice->fee_group_id ?? 'no_group';
            $feeGroupName = $invoice->feeGroup->name ?? 'No Fee Group';

            if (!isset($feeGroupData[$feeGroupKey])) {
                $feeGroupData[$feeGroupKey] = [
                    'fee_group_id' => $invoice->fee_group_id,
                    'fee_group_name' => $feeGroupName,
                    'fee_group_code' => $invoice->feeGroup->fee_code ?? 'N/A',
                    'total_outstanding' => 0,
                    'current' => 0,
                    '0-30' => 0,
                    '31-60' => 0,
                    '61-90' => 0,
                    '91+' => 0,
                    'invoices' => [],
                    'students' => [],
                ];
            }

            $feeGroupData[$feeGroupKey]['total_outstanding'] += $outstandingAmount;
            $feeGroupData[$feeGroupKey][$agingBucket] += $outstandingAmount;

            // Add invoice details
            $feeGroupData[$feeGroupKey]['invoices'][] = [
                'hash_id' => $invoice->hashid ?? Hashids::encode($invoice->id),
                'invoice_number' => $invoice->invoice_number,
                'student_name' => $invoice->student ? ($invoice->student->first_name . ' ' . $invoice->student->last_name) : 'N/A',
                'admission_number' => $invoice->student->admission_number ?? 'N/A',
                'class_name' => $invoice->classe->name ?? 'N/A',
                'stream_name' => ($invoice->student && $invoice->student->stream) ? $invoice->student->stream->name : 'N/A',
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'issue_date' => $invoice->issue_date->format('Y-m-d'),
                'total_amount' => $invoice->total_amount,
                'paid_amount' => $invoice->paid_amount ?? 0,
                'outstanding_amount' => $outstandingAmount,
                'days_overdue' => $daysOverdue,
                'aging_bucket' => $agingBucket,
                'status' => $invoice->status,
            ];

            // Track unique students
            $studentKey = $invoice->student_id;
            if (!isset($feeGroupData[$feeGroupKey]['students'][$studentKey])) {
                $feeGroupData[$feeGroupKey]['students'][$studentKey] = [
                    'student_name' => $invoice->student ? ($invoice->student->first_name . ' ' . $invoice->student->last_name) : 'N/A',
                    'admission_number' => $invoice->student->admission_number ?? 'N/A',
                    'class_name' => $invoice->classe->name ?? 'N/A',
                    'stream_name' => ($invoice->student && $invoice->student->stream) ? $invoice->student->stream->name : 'N/A',
                    'total_outstanding' => 0,
                ];
            }
            $feeGroupData[$feeGroupKey]['students'][$studentKey]['total_outstanding'] += $outstandingAmount;
        }

        // Convert students to array and calculate totals
        foreach ($feeGroupData as $key => $data) {
            $feeGroupData[$key]['students'] = array_values($data['students']);
            $feeGroupData[$key]['student_count'] = count($feeGroupData[$key]['students']);
            $feeGroupData[$key]['invoice_count'] = count($data['invoices']);
        }

        // Calculate grand totals
        $grandTotals = [
            'total_outstanding' => array_sum(array_column($feeGroupData, 'total_outstanding')),
            'current' => array_sum(array_column($feeGroupData, 'current')),
            '0-30' => array_sum(array_column($feeGroupData, '0-30')),
            '31-60' => array_sum(array_column($feeGroupData, '31-60')),
            '61-90' => array_sum(array_column($feeGroupData, '61-90')),
            '91+' => array_sum(array_column($feeGroupData, '91+')),
            'student_count' => array_sum(array_column($feeGroupData, 'student_count')),
            'invoice_count' => array_sum(array_column($feeGroupData, 'invoice_count')),
        ];

        return [
            'fee_groups' => array_values($feeGroupData),
            'grand_totals' => $grandTotals,
        ];
    }

    /**
     * Export Fee Aging Report
     */
    public function exportFeeAgingReport(Request $request)
    {
        try {
            $branchId = session('branch_id') ?: auth()->user()->branch_id;
            $companyId = auth()->user()->company_id;
            $exportType = $request->export ?? 'pdf';

            // Get filters
            $academicYearId = $request->academic_year_id;
            $classId = $request->class_id;
            $streamId = $request->stream_id;
            $feeGroupId = $request->fee_group_id;
            $asOfDate = $request->as_of_date ?: now()->format('Y-m-d');
            $period = $request->period; // Q1, Q2, Q3, Q4, Term 1, Term 2, Annual, or null for all

            // Get aging data
            $agingData = $this->getFeeAgingData($branchId, $companyId, $academicYearId, $classId, $streamId, $feeGroupId, $asOfDate, $period);

            // Get filter labels for display
            $academicYear = $academicYearId ? \App\Models\School\AcademicYear::find($academicYearId) : null;
            $class = $classId ? \App\Models\School\Classe::find($classId) : null;
            $stream = $streamId ? \App\Models\School\Stream::find($streamId) : null;
            $feeGroup = $feeGroupId ? \App\Models\FeeGroup::find($feeGroupId) : null;
            $company = auth()->user()->company;

            if ($exportType === 'pdf') {
                $pdf = PDF::loadView('school.reports.exports.fee-aging-pdf', compact(
                    'agingData',
                    'academicYear',
                    'class',
                    'stream',
                    'feeGroup',
                    'asOfDate',
                    'company'
                ));
                $pdf->setPaper('a4', 'landscape');
                $filename = 'fee-aging-report-' . date('Y-m-d-H-i-s') . '.pdf';
                return $pdf->download($filename);
            } elseif ($exportType === 'excel') {
                return Excel::download(new \App\Exports\FeeAgingExport($agingData, $academicYear, $class, $stream, $feeGroup, $asOfDate, $company), 'fee-aging-report-' . date('Y-m-d-H-i-s') . '.xlsx');
            }

            return redirect()->back()->with('error', 'Invalid export type');
        } catch (\Exception $e) {
            \Log::error('Fee Aging Report Export error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An error occurred while exporting the report. Please try again.');
        }
    }

    /**
     * Class-Wise Revenue Collection Report
     */
    public function classWiseRevenueCollection(Request $request)
    {
        try {
            $branchId = session('branch_id') ?: auth()->user()->branch_id;
            $companyId = auth()->user()->company_id;

            // Get filters
            $academicYearId = $request->academic_year_id;
            $classId = $request->class_id;
            $streamId = $request->stream_id;
            $dateFrom = $request->date_from ?: now()->startOfMonth()->format('Y-m-d');
            $dateTo = $request->date_to ?: now()->format('Y-m-d');
            $period = $request->period; // Q1, Q2, Q3, Q4, Annual, or null for all

            // Get classes for filter
            $classes = \App\Models\School\Classe::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            // Get streams for filter
            $streams = \App\Models\School\Stream::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            // Get academic years for filter
            $academicYears = \App\Models\School\AcademicYear::where('company_id', $companyId)
                ->orderBy('year_name', 'desc')
                ->get();

            // Get current academic year for default
            $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', $companyId)
                ->where('is_current', true)
                ->first();

            if (!$academicYearId && $currentAcademicYear) {
                $academicYearId = $currentAcademicYear->id;
            }

            // Handle exports
            if ($request->has('export')) {
                return $this->exportClassWiseRevenueCollection($request);
            }

            // Get revenue data
            $revenueData = $this->getClassWiseRevenueData($branchId, $companyId, $academicYearId, $classId, $streamId, $dateFrom, $dateTo, $period);

            return view('school.reports.class-wise-revenue-collection', compact(
                'classes',
                'streams',
                'academicYears',
                'currentAcademicYear',
                'revenueData',
                'academicYearId',
                'classId',
                'streamId',
                'dateFrom',
                'dateTo',
                'period'
            ));
        } catch (\Exception $e) {
            \Log::error('Class-Wise Revenue Collection Report error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);

            return redirect()->back()->with('error', 'An error occurred while generating the report. Please try again.');
        }
    }

    /**
     * Get class-wise revenue collection data
     */
    private function getClassWiseRevenueData($branchId, $companyId, $academicYearId, $classId, $streamId, $dateFrom, $dateTo, $period)
    {
        // Get fee invoices
        $invoicesQuery = \App\Models\FeeInvoice::with(['classe', 'academicYear', 'feeGroup', 'student.stream'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('status', '!=', 'cancelled')
            ->whereBetween('issue_date', [$dateFrom, $dateTo]);

        if ($academicYearId) {
            $invoicesQuery->where('academic_year_id', $academicYearId);
        }

        if ($classId) {
            $invoicesQuery->where('class_id', $classId);
        }

        if ($streamId) {
            $invoicesQuery->whereHas('student', function ($q) use ($streamId) {
                $q->where('stream_id', $streamId);
            });
        }

        if ($period) {
            $periodMap = ['Q1' => 1, 'Q2' => 2, 'Q3' => 3, 'Q4' => 4, 'Annual' => 5];
            if (isset($periodMap[$period])) {
                $invoicesQuery->where('period', $periodMap[$period]);
            }
        }

        $invoices = $invoicesQuery->get();

        // Group invoices by class
        $classData = [];

        foreach ($invoices as $invoice) {
            $class = $invoice->classe;
            $classKey = $class->id ?? 'no_class';
            $className = $class->name ?? 'No Class';

            if (!isset($classData[$classKey])) {
                $classData[$classKey] = [
                    'class_id' => $class->id ?? null,
                    'class_name' => $className,
                    'streams' => [],
                    'total_invoices' => 0,
                    'total_billed' => 0,
                    'total_collected' => 0,
                    'total_outstanding' => 0,
                    'periods' => [
                        'Q1' => ['billed' => 0, 'collected' => 0, 'outstanding' => 0],
                        'Q2' => ['billed' => 0, 'collected' => 0, 'outstanding' => 0],
                        'Q3' => ['billed' => 0, 'collected' => 0, 'outstanding' => 0],
                        'Q4' => ['billed' => 0, 'collected' => 0, 'outstanding' => 0],
                        'Annual' => ['billed' => 0, 'collected' => 0, 'outstanding' => 0],
                    ],
                ];
            }

            // Get period name
            $periodMap = [1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4', 5 => 'Annual'];
            $periodName = $periodMap[$invoice->period] ?? 'Unknown';

            // Use paid_amount from invoice (more reliable as it's already calculated)
            $collectedAmount = $invoice->paid_amount ?? 0;
            $outstandingAmount = $invoice->total_amount - $collectedAmount;

            // Update class totals
            $classData[$classKey]['total_invoices']++;
            $classData[$classKey]['total_billed'] += $invoice->total_amount;
            $classData[$classKey]['total_collected'] += $collectedAmount;
            $classData[$classKey]['total_outstanding'] += $outstandingAmount;

            // Update period totals
            if (isset($classData[$classKey]['periods'][$periodName])) {
                $classData[$classKey]['periods'][$periodName]['billed'] += $invoice->total_amount;
                $classData[$classKey]['periods'][$periodName]['collected'] += $collectedAmount;
                $classData[$classKey]['periods'][$periodName]['outstanding'] += $outstandingAmount;
            }

            // Group by stream
            $stream = $invoice->student->stream ?? null;
            $streamKey = $stream->id ?? 'no_stream';
            $streamName = $stream->name ?? 'No Stream';

            if (!isset($classData[$classKey]['streams'][$streamKey])) {
                $classData[$classKey]['streams'][$streamKey] = [
                    'stream_id' => $stream->id ?? null,
                    'stream_name' => $streamName,
                    'total_invoices' => 0,
                    'total_billed' => 0,
                    'total_collected' => 0,
                    'total_outstanding' => 0,
                    'periods' => [
                        'Q1' => ['billed' => 0, 'collected' => 0, 'outstanding' => 0],
                        'Q2' => ['billed' => 0, 'collected' => 0, 'outstanding' => 0],
                        'Q3' => ['billed' => 0, 'collected' => 0, 'outstanding' => 0],
                        'Q4' => ['billed' => 0, 'collected' => 0, 'outstanding' => 0],
                        'Annual' => ['billed' => 0, 'collected' => 0, 'outstanding' => 0],
                    ],
                ];
            }

            // Update stream totals
            $classData[$classKey]['streams'][$streamKey]['total_invoices']++;
            $classData[$classKey]['streams'][$streamKey]['total_billed'] += $invoice->total_amount;
            $classData[$classKey]['streams'][$streamKey]['total_collected'] += $collectedAmount;
            $classData[$classKey]['streams'][$streamKey]['total_outstanding'] += $outstandingAmount;

            // Update stream period totals
            if (isset($classData[$classKey]['streams'][$streamKey]['periods'][$periodName])) {
                $classData[$classKey]['streams'][$streamKey]['periods'][$periodName]['billed'] += $invoice->total_amount;
                $classData[$classKey]['streams'][$streamKey]['periods'][$periodName]['collected'] += $collectedAmount;
                $classData[$classKey]['streams'][$streamKey]['periods'][$periodName]['outstanding'] += $outstandingAmount;
            }
        }

        // Calculate collection rates
        foreach ($classData as $key => $data) {
            $classData[$key]['collection_rate'] = $data['total_billed'] > 0 
                ? round(($data['total_collected'] / $data['total_billed']) * 100, 2) 
                : 0;

            // Calculate rates for each period
            foreach ($classData[$key]['periods'] as $periodKey => $periodData) {
                $classData[$key]['periods'][$periodKey]['collection_rate'] = $periodData['billed'] > 0 
                    ? round(($periodData['collected'] / $periodData['billed']) * 100, 2) 
                    : 0;
            }

            // Calculate rates for streams
            foreach ($classData[$key]['streams'] as $streamKey => $streamData) {
                $classData[$key]['streams'][$streamKey]['collection_rate'] = $streamData['total_billed'] > 0 
                    ? round(($streamData['total_collected'] / $streamData['total_billed']) * 100, 2) 
                    : 0;

                // Calculate rates for each period in stream
                foreach ($classData[$key]['streams'][$streamKey]['periods'] as $periodKey => $periodData) {
                    $classData[$key]['streams'][$streamKey]['periods'][$periodKey]['collection_rate'] = $periodData['billed'] > 0 
                        ? round(($periodData['collected'] / $periodData['billed']) * 100, 2) 
                        : 0;
                }
            }
        }

        // Calculate grand totals
        $grandTotals = [
            'total_invoices' => array_sum(array_column($classData, 'total_invoices')),
            'total_billed' => array_sum(array_column($classData, 'total_billed')),
            'total_collected' => array_sum(array_column($classData, 'total_collected')),
            'total_outstanding' => array_sum(array_column($classData, 'total_outstanding')),
            'collection_rate' => 0,
        ];

        if ($grandTotals['total_billed'] > 0) {
            $grandTotals['collection_rate'] = round(($grandTotals['total_collected'] / $grandTotals['total_billed']) * 100, 2);
        }

        return [
            'classes' => array_values($classData),
            'grand_totals' => $grandTotals,
        ];
    }

    /**
     * Export Class-Wise Revenue Collection Report
     */
    private function exportClassWiseRevenueCollection(Request $request)
    {
        try {
            $branchId = session('branch_id') ?: auth()->user()->branch_id;
            $companyId = auth()->user()->company_id;
            $exportType = $request->export ?? 'pdf';

            // Get filters
            $academicYearId = $request->academic_year_id;
            $classId = $request->class_id;
            $streamId = $request->stream_id;
            $dateFrom = $request->date_from ?: now()->startOfMonth()->format('Y-m-d');
            $dateTo = $request->date_to ?: now()->format('Y-m-d');
            $period = $request->period;

            // Get revenue data
            $revenueData = $this->getClassWiseRevenueData($branchId, $companyId, $academicYearId, $classId, $streamId, $dateFrom, $dateTo, $period);

            // Get filter labels for display
            $academicYear = $academicYearId ? \App\Models\School\AcademicYear::find($academicYearId) : null;
            $class = $classId ? \App\Models\School\Classe::find($classId) : null;
            $stream = $streamId ? \App\Models\School\Stream::find($streamId) : null;
            $company = auth()->user()->company;

            if ($exportType === 'pdf') {
                $pdf = PDF::loadView('school.reports.exports.class-wise-revenue-collection-pdf', compact(
                    'revenueData',
                    'academicYear',
                    'class',
                    'stream',
                    'dateFrom',
                    'dateTo',
                    'period',
                    'company'
                ));
                $pdf->setPaper('a4', 'landscape');
                $filename = 'class-wise-revenue-collection-report-' . date('Y-m-d-H-i-s') . '.pdf';
                return $pdf->download($filename);
            } elseif ($exportType === 'excel') {
                return Excel::download(new \App\Exports\ClassWiseRevenueCollectionExport($revenueData, $academicYear, $class, $stream, $dateFrom, $dateTo, $period, $company), 'class-wise-revenue-collection-report-' . date('Y-m-d-H-i-s') . '.xlsx');
            }

            return redirect()->back()->with('error', 'Invalid export type');
        } catch (\Exception $e) {
            \Log::error('Class-Wise Revenue Collection Export error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An error occurred while exporting the report. Please try again.');
        }
    }

    /**
     * Fee Waivers & Discounts Report
     */
    public function feeWaiversDiscountsReport(Request $request)
    {
        try {
            $branchId = session('branch_id') ?: auth()->user()->branch_id;
            $companyId = auth()->user()->company_id;

            // Get filters - decode hash IDs
            $academicYearId = $request->academic_year_id ? (is_numeric($request->academic_year_id) ? $request->academic_year_id : (Hashids::decode($request->academic_year_id)[0] ?? null)) : null;
            $classId = $request->class_id ? (is_numeric($request->class_id) ? $request->class_id : (Hashids::decode($request->class_id)[0] ?? null)) : null;
            $streamId = $request->stream_id ? (is_numeric($request->stream_id) ? $request->stream_id : (Hashids::decode($request->stream_id)[0] ?? null)) : null;
            $dateFrom = $request->date_from ?: now()->startOfMonth()->format('Y-m-d');
            $dateTo = $request->date_to ?: now()->format('Y-m-d');
            $discountType = $request->discount_type; // fixed, percentage, or null for all
            $period = $request->period; // Q1, Q2, Q3, Q4, Annual, or null for all

            // Get classes for filter
            $classes = \App\Models\School\Classe::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            // Get streams for filter
            $streams = \App\Models\School\Stream::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->get();

            // Get academic years for filter
            $academicYears = \App\Models\School\AcademicYear::where('company_id', $companyId)
                ->orderBy('year_name', 'desc')
                ->get();

            // Get current academic year for default
            $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', $companyId)
                ->where('is_current', true)
                ->first();

            if (!$academicYearId && $currentAcademicYear) {
                $academicYearId = $currentAcademicYear->id;
            }

            // Handle exports
            if ($request->has('export')) {
                return $this->exportFeeWaiversDiscounts($request);
            }

            // Get waivers and discounts data
            $waiversDiscountsData = $this->getFeeWaiversDiscountsData($branchId, $companyId, $academicYearId, $classId, $streamId, $dateFrom, $dateTo, $discountType, $period);

            return view('school.reports.fee-waivers-discounts', compact(
                'classes',
                'streams',
                'academicYears',
                'currentAcademicYear',
                'waiversDiscountsData',
                'academicYearId',
                'classId',
                'streamId',
                'dateFrom',
                'dateTo',
                'discountType',
                'period'
            ));
        } catch (\Exception $e) {
            \Log::error('Fee Waivers & Discounts Report error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);

            return redirect()->back()->with('error', 'An error occurred while generating the report. Please try again.');
        }
    }

    /**
     * Get fee waivers and discounts data
     */
    private function getFeeWaiversDiscountsData($branchId, $companyId, $academicYearId, $classId, $streamId, $dateFrom, $dateTo, $discountType, $period = null)
    {
        // Get fee invoices with discounts
        $invoicesQuery = \App\Models\FeeInvoice::with(['classe', 'academicYear', 'feeGroup', 'student', 'student.stream'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('status', '!=', 'cancelled')
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->where(function ($query) {
                $query->where('discount_amount', '>', 0)
                      ->orWhereNotNull('discount_type');
            });

        if ($academicYearId) {
            $invoicesQuery->where('academic_year_id', $academicYearId);
        }

        if ($classId) {
            $invoicesQuery->where('class_id', $classId);
        }

        if ($streamId) {
            $invoicesQuery->whereHas('student', function ($q) use ($streamId) {
                $q->where('stream_id', $streamId);
            });
        }

        if ($discountType) {
            $invoicesQuery->where('discount_type', $discountType);
        }

        if ($period) {
            $periodMap = ['Q1' => 1, 'Q2' => 2, 'Q3' => 3, 'Q4' => 4, 'Annual' => 5];
            if (isset($periodMap[$period])) {
                $invoicesQuery->where('period', $periodMap[$period]);
            }
        }

        $invoices = $invoicesQuery->orderBy('issue_date', 'desc')->get();

        // Group by class
        $classData = [];

        foreach ($invoices as $invoice) {
            $class = $invoice->classe;
            $classKey = $class->id ?? 'no_class';
            $className = $class->name ?? 'No Class';

            if (!isset($classData[$classKey])) {
                $classData[$classKey] = [
                    'class_id' => $class->id ?? null,
                    'class_name' => $className,
                    'streams' => [],
                    'total_invoices' => 0,
                    'total_subtotal' => 0,
                    'total_discount_amount' => 0,
                    'total_after_discount' => 0,
                    'discount_types' => [
                        'fixed' => ['count' => 0, 'amount' => 0],
                        'percentage' => ['count' => 0, 'amount' => 0],
                    ],
                ];
            }

            // Get period name
            $periodMap = [1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4', 5 => 'Annual'];
            $periodName = $periodMap[$invoice->period] ?? 'Unknown';

            $discountAmount = $invoice->discount_amount ?? 0;
            $subtotal = $invoice->subtotal ?? 0;
            $afterDiscount = $subtotal - $discountAmount;

            // Update class totals
            $classData[$classKey]['total_invoices']++;
            $classData[$classKey]['total_subtotal'] += $subtotal;
            $classData[$classKey]['total_discount_amount'] += $discountAmount;
            $classData[$classKey]['total_after_discount'] += $afterDiscount;

            // Update discount type totals
            if ($invoice->discount_type) {
                if (isset($classData[$classKey]['discount_types'][$invoice->discount_type])) {
                    $classData[$classKey]['discount_types'][$invoice->discount_type]['count']++;
                    $classData[$classKey]['discount_types'][$invoice->discount_type]['amount'] += $discountAmount;
                }
            }

            // Group by stream
            $stream = $invoice->student->stream ?? null;
            $streamKey = $stream->id ?? 'no_stream';
            $streamName = $stream->name ?? 'No Stream';

            if (!isset($classData[$classKey]['streams'][$streamKey])) {
                $classData[$classKey]['streams'][$streamKey] = [
                    'stream_id' => $stream->id ?? null,
                    'stream_name' => $streamName,
                    'invoices' => [],
                    'total_invoices' => 0,
                    'total_subtotal' => 0,
                    'total_discount_amount' => 0,
                    'total_after_discount' => 0,
                ];
            }

            // Add invoice to stream
            $studentName = 'N/A';
            if ($invoice->student) {
                $studentName = $invoice->student->name ?? trim(($invoice->student->first_name ?? '') . ' ' . ($invoice->student->last_name ?? ''));
                if (empty($studentName)) {
                    $studentName = 'N/A';
                }
            }
            
            $classData[$classKey]['streams'][$streamKey]['invoices'][] = [
                'invoice_id' => $invoice->id,
                'hash_id' => $invoice->hashid,
                'invoice_number' => $invoice->invoice_number,
                'student_name' => $studentName,
                'admission_number' => $invoice->student->admission_number ?? 'N/A',
                'period' => $periodName,
                'issue_date' => $invoice->issue_date->format('Y-m-d'),
                'subtotal' => $subtotal,
                'discount_type' => $invoice->discount_type ?? 'N/A',
                'discount_value' => $invoice->discount_value ?? 0,
                'discount_amount' => $discountAmount,
                'after_discount' => $afterDiscount,
                'total_amount' => $invoice->total_amount ?? 0,
            ];

            // Update stream totals
            $classData[$classKey]['streams'][$streamKey]['total_invoices']++;
            $classData[$classKey]['streams'][$streamKey]['total_subtotal'] += $subtotal;
            $classData[$classKey]['streams'][$streamKey]['total_discount_amount'] += $discountAmount;
            $classData[$classKey]['streams'][$streamKey]['total_after_discount'] += $afterDiscount;
        }

        // Calculate discount percentages
        foreach ($classData as $key => $data) {
            $classData[$key]['discount_percentage'] = $data['total_subtotal'] > 0 
                ? round(($data['total_discount_amount'] / $data['total_subtotal']) * 100, 2) 
                : 0;

            foreach ($classData[$key]['streams'] as $streamKey => $streamData) {
                $classData[$key]['streams'][$streamKey]['discount_percentage'] = $streamData['total_subtotal'] > 0 
                    ? round(($streamData['total_discount_amount'] / $streamData['total_subtotal']) * 100, 2) 
                    : 0;
            }
        }

        // Calculate grand totals
        $grandTotals = [
            'total_invoices' => array_sum(array_column($classData, 'total_invoices')),
            'total_subtotal' => array_sum(array_column($classData, 'total_subtotal')),
            'total_discount_amount' => array_sum(array_column($classData, 'total_discount_amount')),
            'total_after_discount' => array_sum(array_column($classData, 'total_after_discount')),
            'discount_percentage' => 0,
            'discount_types' => [
                'fixed' => ['count' => 0, 'amount' => 0],
                'percentage' => ['count' => 0, 'amount' => 0],
            ],
        ];

        // Aggregate discount types
        foreach ($classData as $data) {
            $grandTotals['discount_types']['fixed']['count'] += $data['discount_types']['fixed']['count'];
            $grandTotals['discount_types']['fixed']['amount'] += $data['discount_types']['fixed']['amount'];
            $grandTotals['discount_types']['percentage']['count'] += $data['discount_types']['percentage']['count'];
            $grandTotals['discount_types']['percentage']['amount'] += $data['discount_types']['percentage']['amount'];
        }

        if ($grandTotals['total_subtotal'] > 0) {
            $grandTotals['discount_percentage'] = round(($grandTotals['total_discount_amount'] / $grandTotals['total_subtotal']) * 100, 2);
        }

        return [
            'classes' => array_values($classData),
            'grand_totals' => $grandTotals,
        ];
    }

    /**
     * Export Fee Waivers & Discounts Report
     */
    private function exportFeeWaiversDiscounts(Request $request)
    {
        try {
            $branchId = session('branch_id') ?: auth()->user()->branch_id;
            $companyId = auth()->user()->company_id;
            $exportType = $request->export ?? 'pdf';

            // Get filters - decode hash IDs
            $academicYearId = $request->academic_year_id ? (is_numeric($request->academic_year_id) ? $request->academic_year_id : (Hashids::decode($request->academic_year_id)[0] ?? null)) : null;
            $classId = $request->class_id ? (is_numeric($request->class_id) ? $request->class_id : (Hashids::decode($request->class_id)[0] ?? null)) : null;
            $streamId = $request->stream_id ? (is_numeric($request->stream_id) ? $request->stream_id : (Hashids::decode($request->stream_id)[0] ?? null)) : null;
            $dateFrom = $request->date_from ?: now()->startOfMonth()->format('Y-m-d');
            $dateTo = $request->date_to ?: now()->format('Y-m-d');
            $discountType = $request->discount_type;
            $period = $request->period;

            // Get waivers and discounts data
            $waiversDiscountsData = $this->getFeeWaiversDiscountsData($branchId, $companyId, $academicYearId, $classId, $streamId, $dateFrom, $dateTo, $discountType, $period);

            // Get filter labels for display
            $academicYear = $academicYearId ? \App\Models\School\AcademicYear::find($academicYearId) : null;
            $class = $classId ? \App\Models\School\Classe::find($classId) : null;
            $stream = $streamId ? \App\Models\School\Stream::find($streamId) : null;
            $company = auth()->user()->company;

            if ($exportType === 'pdf') {
                $pdf = PDF::loadView('school.reports.exports.fee-waivers-discounts-pdf', compact(
                    'waiversDiscountsData',
                    'academicYear',
                    'class',
                    'stream',
                    'dateFrom',
                    'dateTo',
                    'discountType',
                    'period',
                    'company'
                ));
                $pdf->setPaper('a4', 'landscape');
                $filename = 'fee-waivers-discounts-report-' . date('Y-m-d-H-i-s') . '.pdf';
                return $pdf->download($filename);
            } elseif ($exportType === 'excel') {
                return Excel::download(new \App\Exports\FeeWaiversDiscountsExport($waiversDiscountsData, $academicYear, $class, $stream, $dateFrom, $dateTo, $discountType, $period, $company), 'fee-waivers-discounts-report-' . date('Y-m-d-H-i-s') . '.xlsx');
            }

            return redirect()->back()->with('error', 'Invalid export type');
        } catch (\Exception $e) {
            \Log::error('Fee Waivers & Discounts Export error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An error occurred while exporting the report. Please try again.');
        }
    }
}