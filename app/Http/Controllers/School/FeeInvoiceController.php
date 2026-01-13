<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FeeGroup;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeeSetting;
use App\Models\FeeSettingItem;
use App\Models\School\AcademicYear;
use App\Models\School\BusStop;
use App\Models\School\Classe;
use App\Models\School\Stream;
use App\Models\School\Student;
use App\Services\ParentNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class FeeInvoiceController extends Controller
{
    /**
     * Map period number to fee_period enum value.
     */
    private function mapPeriodToFeePeriod($period)
    {
        $periodMap = [
            1 => 'Q1',
            2 => 'Q2',
            3 => 'Q3',
            4 => 'Q4',
            5 => 'Annual', // Full year period
            6 => 'Term 1',
            7 => 'Term 2',
        ];

        return $periodMap[$period] ?? 'Q1';
    }

    /**
     * Get period name for display
     */
    private function getPeriodName($period)
    {
        $periodNames = [
            1 => 'Q1',
            2 => 'Q2',
            3 => 'Q3',
            4 => 'Q4',
            5 => 'Annual',
            6 => 'Term 1',
            7 => 'Term 2',
        ];

        return $periodNames[$period] ?? 'Q1';
    }

    /**
     * Format period for display
     */
    private function formatPeriod($period)
    {
        $periodNames = [
            1 => 'Q1 Quarter',
            2 => 'Q2 Quarter',
            3 => 'Q3 Quarter',
            4 => 'Q4 Quarter',
            5 => 'Full Year',
            6 => 'Term 1',
            7 => 'Term 2',
        ];

        return $periodNames[$period] ?? 'Q1 Quarter';
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get classes - first try to get classes for the user's branch, if none found, get all classes for the company
        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        // If no classes found for the user's branch, get all classes for the company
        if ($classes->isEmpty()) {
            $classes = Classe::where('company_id', $companyId)
                ->orderBy('name')
                ->get();
        }

        \Log::info('FeeInvoiceController@index classes loaded', [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'session_branch_id' => session('branch_id'),
            'user_branch_id' => Auth::user()->branch_id,
            'classes_count' => $classes->count(),
            'classes' => $classes->pluck('name', 'id')->toArray()
        ]);

        $feeGroups = FeeGroup::active()
            ->orderBy('name')
            ->get();

        // Get streams
        $streams = Stream::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->where('is_current', true)
            ->get();

        // Get the current active academic year for default selection
        $currentAcademicYear = AcademicYear::where('company_id', $companyId)
            ->where('is_current', true)
            ->first();

        // Calculate statistics
        $baseQuery = FeeInvoice::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled');

        if ($branchId) {
            $baseQuery->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });
        }

        $totalInvoices = $baseQuery->count();

        $issuedInvoices = (clone $baseQuery)->where('status', 'issued')->count();

        $paidInvoices = (clone $baseQuery)->whereRaw('paid_amount >= total_amount')->count();

        $overdueInvoices = (clone $baseQuery)->where('due_date', '<', now())
            ->whereRaw('paid_amount < total_amount')
            ->count();

        return view('school.fee-invoices.index', compact(
            'classes',
            'streams',
            'feeGroups',
            'academicYears',
            'currentAcademicYear',
            'totalInvoices',
            'issuedInvoices',
            'paidInvoices',
            'overdueInvoices'
        ));
    }

    /**
     * Get fee invoices data for DataTables.
     */
    public function data(Request $request)
    {
        \Log::info('FeeInvoiceController@data called', [
            'request_all' => $request->all(),
            'user_id' => auth()->id(),
            'user_branch' => auth()->user()->branch_id ?? 'null',
            'session_branch' => session('branch_id') ?? 'null'
        ]);

        $query = FeeInvoice::with(['student.stream', 'classe', 'academicYear', 'feeGroup'])
            ->forCompany(Auth::user()->company_id);

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId) {
            $query->forBranch($branchId);
        }

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by class if provided
        if ($request->has('class_id') && $request->class_id) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by fee group if provided
        if ($request->has('fee_group_id') && $request->fee_group_id) {
            $query->where('fee_group_id', $request->fee_group_id);
        }

        // Filter by academic year if provided
        if ($request->has('academic_year_id') && $request->academic_year_id) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by stream if provided
        if ($request->has('stream_id') && $request->stream_id) {
            $decodedStreamId = \Vinkla\Hashids\Facades\Hashids::decode($request->stream_id);
            if (!empty($decodedStreamId)) {
                $query->whereHas('student', function($q) use ($decodedStreamId) {
                    $q->where('stream_id', $decodedStreamId[0]);
                });
            }
        }

        // Filter by period if provided
        if ($request->has('period') && $request->period) {
            $query->where('period', $request->period);
        }

        // Handle DataTables search (search by student name)
        $searchValue = null;
        if ($request->has('search') && is_array($request->input('search')) && isset($request->input('search')['value'])) {
            $searchValue = trim($request->input('search')['value']);
        } elseif ($request->has('search.value')) {
            $searchValue = trim($request->input('search.value'));
        }
        
        if (!empty($searchValue)) {
            $query->whereHas('student', function($q) use ($searchValue) {
                $q->where(function($subQuery) use ($searchValue) {
                    $subQuery->where('first_name', 'like', '%' . $searchValue . '%')
                             ->orWhere('last_name', 'like', '%' . $searchValue . '%')
                             ->orWhere('admission_number', 'like', '%' . $searchValue . '%')
                             ->orWhere(DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))"), 'like', '%' . $searchValue . '%');
                });
            });
        }

        // Determine which academic year to use for opening balance lookup
        // Priority: 1. Filter academic year, 2. Current academic year, 3. Invoice academic year
        $academicYearForOpeningBalance = null;
        if ($request->has('academic_year_id') && $request->academic_year_id) {
            $academicYearForOpeningBalance = AcademicYear::find($request->academic_year_id);
        }
        if (!$academicYearForOpeningBalance) {
            $academicYearForOpeningBalance = AcademicYear::where('company_id', Auth::user()->company_id)
                ->where('is_current', true)
                ->first();
        }

        // Get all invoices matching filters
        $invoices = $query->get();

        // Group by student_id
        $grouped = $invoices->groupBy('student_id');

        // Transform grouped data
        $data = $grouped->map(function ($studentInvoices, $studentId) use ($academicYearForOpeningBalance) {
            $student = $studentInvoices->first()->student;
            $class = $studentInvoices->first()->classe;
            $academicYear = $studentInvoices->first()->academicYear;
            $feeGroup = $studentInvoices->first()->feeGroup;

            $totalAmount = $studentInvoices->sum('total_amount');
            $paidAmount = $studentInvoices->sum('paid_amount');
            $totalDiscount = $studentInvoices->sum('discount_amount');
            $remainingAmount = $totalAmount - $paidAmount;

            $overallStatus = $this->calculateOverallStatus($studentInvoices);

            $invoiceDetails = $studentInvoices->pluck('invoice_number')->join(', ');
            
            // Get all periods from invoices and format them
            $periods = $studentInvoices->pluck('period')
                ->map(function($period) {
                    return $this->formatPeriod($period);
                })
                ->unique()
                ->sort()
                ->values()
                ->join(', ');
            
            // Get student's stream
            $streamName = $student && $student->stream ? $student->stream->name : 'N/A';
            
            // Get all control numbers from invoices (using lipisha_control_number field)
            $controlNumbers = $studentInvoices
                ->filter(function($invoice) {
                    return $invoice->lipisha_control_number;
                })
                ->pluck('lipisha_control_number')
                ->unique()
                ->values();
            
            $controlNumber = $controlNumbers->isNotEmpty() ? $controlNumbers->join(', ') : '-';
            
            // Get opening balance info from student_fee_opening_balances table
            // Use the academic year from filter/current year, not from invoices
            $openingBalance = 0;
            $openingBalanceControlNumber = '-';
            
            if ($student && $academicYearForOpeningBalance) {
                // Get all opening balances for this student in the academic year (may have multiple per fee group)
                $openingBalanceRecords = \App\Models\School\StudentFeeOpeningBalance::where('student_id', $student->id)
                    ->where('academic_year_id', $academicYearForOpeningBalance->id)
                    ->where('balance_due', '>', 0)
                    ->get();
                
                if ($openingBalanceRecords->isNotEmpty()) {
                    // Sum all opening balances
                    $openingBalance = $openingBalanceRecords->sum(function($record) {
                        return $record->balance_due ?? $record->amount ?? 0;
                    });
                    
                    // Get all control numbers (comma-separated)
                    $controlNumbers = $openingBalanceRecords
                        ->filter(function($record) {
                            return !empty($record->lipisha_control_number);
                        })
                        ->pluck('lipisha_control_number')
                        ->unique()
                        ->values();
                    
                    $openingBalanceControlNumber = $controlNumbers->isNotEmpty() ? $controlNumbers->join(', ') : '-';
                }
            }
            
            return [
                'DT_RowIndex' => $studentId,
                'student_name' => ($student && $student->first_name) ? ($student->first_name . ' ' . ($student->last_name ?? '')) : 'N/A',
                'class_name' => $class ? $class->name : 'N/A',
                'stream_name' => $streamName,
                'academic_year' => $academicYear ? $academicYear->year_name : 'N/A',
                'fee_group' => $feeGroup ? $feeGroup->name : 'N/A',
                'period' => $periods ?: 'N/A',
                'invoices' => $invoiceDetails,
                'control_number' => $controlNumber,
                'opening_balance' => 'TZS ' . number_format($openingBalance, 2),
                'opening_balance_control_number' => $openingBalanceControlNumber,
                'total_amount_invoiced' => 'TZS ' . number_format($totalAmount, 2),
                'total_amount_to_be_paid' => 'TZS ' . number_format($remainingAmount, 2),
                'status' => $this->formatOverallStatus($overallStatus),
                'actions' => $student ? '<a href="' . route('school.fee-invoices.student', $student->getRouteKey()) . '" class="btn btn-info btn-sm" title="View Fee Invoices">
                                <i class="bx bx-show"></i>
                            </a>' : '<span class="text-muted">N/A</span>'
            ];
        })->values();

        // Apply search filter to grouped data (in case search needs to match other fields like class, stream, etc.)
        if (!empty($searchValue)) {
            $data = $data->filter(function($item) use ($searchValue) {
                $searchLower = strtolower($searchValue);
                return (
                    stripos($item['student_name'] ?? '', $searchValue) !== false ||
                    stripos($item['class_name'] ?? '', $searchValue) !== false ||
                    stripos($item['stream_name'] ?? '', $searchValue) !== false ||
                    stripos($item['fee_group'] ?? '', $searchValue) !== false ||
                    stripos($item['invoices'] ?? '', $searchValue) !== false ||
                    stripos($item['control_number'] ?? '', $searchValue) !== false
                );
            })->values();
        }

        // Get pagination parameters from request
        $draw = intval($request->get('draw', 1));
        $start = intval($request->get('start', 0));
        $length = intval($request->get('length', 25));
        
        // Get total count before pagination (after search filter)
        $totalRecords = $data->count();
        
        // Apply pagination
        $paginatedData = $data->slice($start, $length)->values();

        \Log::info('FeeInvoiceController@data grouped response', [
            'total_count' => $totalRecords,
            'start' => $start,
            'length' => $length,
            'returned_count' => $paginatedData->count(),
            'first_record_keys' => $paginatedData->count() > 0 ? array_keys($paginatedData[0]) : []
        ]);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $paginatedData
        ]);
    }

    /**
     * Show the form for creating a single invoice.
     */
    public function createSingle()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->withCount(['students' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('name')
            ->get();

        $feeGroups = FeeGroup::active()
            ->where('company_id', Auth::user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        return view('school.fee-invoices.create-single', compact('classes', 'feeGroups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->withCount(['students' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('name')
            ->get();

        $feeGroups = FeeGroup::active()
            ->where('company_id', Auth::user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        // Initialize empty students collection (students are loaded dynamically via AJAX when class is selected)
        $students = collect([]);

        return view('school.fee-invoices.create', compact('classes', 'feeGroups', 'students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'generation_type' => 'required|in:single,bulk',
            'class_id' => 'required|exists:classes,id',
            'fee_group_id' => 'required|exists:fee_groups,id',
            'period' => 'required|integer|min:1|max:7',
        ];

        // Add student_id validation only for single generation
        if ($request->generation_type === 'single') {
            $rules['student_id'] = 'required|exists:students,id';
        }

        $request->validate($rules);

        try {
            // For bulk generation, don't use transactions to avoid duplicate invoice numbers
            if ($request->generation_type === 'bulk') {
                if ($request->period == 5) {
                    $this->generateBulkInvoicesWithoutTransaction($request);
                } else {
                    $this->generateBulkInvoicesWithoutTransaction($request);
                }

                $results = session('bulk_generation_results', ['created' => 0, 'skipped' => 0, 'total_students' => 0, 'periods_generated' => 1]);
                $periodsGenerated = $results['periods_generated'] ?? 1;
                $totalExpected = $results['total_students'] * $periodsGenerated;

                if ($results['created'] > 0) {
                    $message = "Successfully generated {$results['created']} invoice(s). ";
                    if ($results['skipped'] > 0) {
                        $message .= "{$results['skipped']} student(s) already had invoices for this period.";
                    }
                    if ($periodsGenerated > 1) {
                        $message .= " (Full Year: {$periodsGenerated} quarters × {$results['total_students']} students)";
                    }
                } else {
                    $message = "No new invoices were generated. All {$totalExpected} expected invoice(s) already exist.";
                }

                // Check if this is an AJAX request
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'results' => $results
                    ]);
                }

                return redirect()->route('school.fee-invoices.index')
                    ->with('success', $message);
            }

            // For single generation, use transaction
            DB::beginTransaction();

            $this->generateSingleInvoice($request);

            DB::commit();

            $message = 'Fee invoice generated successfully.';

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'results' => session('bulk_generation_results', [])
                ]);
            }

            return redirect()->route('school.fee-invoices.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Fee invoice generation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate fee invoice(s). Please try again.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to generate fee invoice(s). Please try again.');
        }
    }

    /**
     * Generate single student invoice.
     */
    private function generateSingleInvoice(Request $request)
    {
        $student = Student::findOrFail($request->student_id);

        // Use the student's academic year, not the current academic year
        // This allows creating invoices for students in different academic years
        $academicYearId = $student->academic_year_id;
        
        if (!$academicYearId) {
            // Fallback to current academic year if student doesn't have one set
            $academicYear = AcademicYear::where('company_id', Auth::user()->company_id)
                ->where('is_current', true)
                ->first();

            if (!$academicYear) {
                throw new \Exception('No current academic year found and student has no academic year set.');
            }
            
            $academicYearId = $academicYear->id;
        }

        $result = $this->createInvoiceForStudent($student, $request->class_id, $academicYearId, $request->period, $request->fee_group_id);

        if (!$result) {
            throw new \Exception('Failed to create invoice. No fee settings found for the selected period.');
        }
    }



    /**
     * Generate bulk invoices without transactions (for all bulk operations).
     */
    private function generateBulkInvoicesWithoutTransaction(Request $request)
    {
        // Get current academic year
        $academicYear = AcademicYear::where('company_id', Auth::user()->company_id)
            ->where('is_current', true)
            ->first();

        if (!$academicYear) {
            throw new \Exception('No current academic year found.');
        }

        $students = Student::where('class_id', $request->class_id)
            ->where('academic_year_id', $academicYear->id)
            ->where('status', 'active')
            ->get();

        $created = 0;
        $skipped = 0;

        // Generate for all quarters if Full Year (period 5), otherwise for the specific period
        // Note: Full Year only generates Q1-Q4, not Terms
        $periodsToGenerate = $request->period == 5 ? [1, 2, 3, 4] : [$request->period];

        foreach ($students as $student) {
            // Use the student's academic year, not the current academic year
            $studentAcademicYearId = $student->academic_year_id ?? $academicYear->id;
            
            foreach ($periodsToGenerate as $period) {
                try {
                    // Check if invoice already exists (using createInvoiceForStudent will check this too)
                    // But we check here first to avoid unnecessary processing
                    $existingInvoice = FeeInvoice::where('student_id', $student->id)
                        ->where('class_id', $request->class_id)
                        ->where('academic_year_id', $studentAcademicYearId)
                        ->where('period', $period)
                        ->where('fee_group_id', $request->fee_group_id)
                        ->where('status', '!=', 'cancelled')
                        ->first();

                    if ($existingInvoice) {
                        $skipped++;
                        continue;
                    }

                    // Create invoice without pre-generated numbers (generate on the fly)
                    $result = $this->createInvoiceForStudent($student, $request->class_id, $studentAcademicYearId, $period, $request->fee_group_id);
                    if ($result === true) {
                        $created++;
                    }
                } catch (\Exception $e) {
                    // Log the error but continue with other invoices
                    \Log::error('Failed to create invoice for student', [
                        'student_id' => $student->id,
                        'period' => $period,
                        'error' => $e->getMessage()
                    ]);
                    throw $e; // Re-throw to stop the process
                }
            }
        }

        // Store results in session for better user feedback
        session(['bulk_generation_results' => [
            'created' => $created,
            'skipped' => $skipped,
            'total_students' => $students->count(),
            'periods_generated' => count($periodsToGenerate)
        ]]);
    }

    /**
     * Create invoice for a specific student.
     */
    private function createInvoiceForStudent($student, $classId, $academicYearId, $period, $feeGroupId, $invoiceNumber = null)
    {
        // Check if invoice already exists
        // Must match: student, class, academic year, period, AND fee group
        $existingInvoice = FeeInvoice::where('student_id', $student->id)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('period', $period)
            ->where('fee_group_id', $feeGroupId)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingInvoice) {
            return false; // Invoice already exists
        }

        // Get fee settings for the class and specific period
        // Fee settings are now reusable across academic years (academic_year_id is optional)
        $feePeriod = $this->mapPeriodToFeePeriod($period);
        $feeSetting = FeeSetting::where('class_id', $classId)
            ->where('fee_period', $feePeriod)
            ->where('is_active', true)
            ->where('company_id', Auth::user()->company_id)
            ->where(function ($query) {
                $branchId = session('branch_id') ?: Auth::user()->branch_id;
                if ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                } else {
                    $query->whereNull('branch_id');
                }
            })
            ->first();

        if (!$feeSetting) {
            return false; // No fee settings found for this period
        }

        // Get class and fee group to determine branch_id
        $class = \App\Models\School\Classe::find($classId);
        $feeGroup = FeeGroup::find($feeGroupId);

        // Determine branch_id: user branch -> class branch -> fee group branch -> company default branch
        $branchId = Auth::user()->branch_id ?? $class->branch_id ?? $feeGroup->branch_id ?? null;
        
        // If still no branch_id, try to get default branch for company
        if (!$branchId && Auth::user()->company_id) {
            $defaultBranch = \App\Models\Branch::where('company_id', Auth::user()->company_id)->first();
            $branchId = $defaultBranch ? $defaultBranch->id : null;
        }

        if (!$branchId) {
            throw new \Exception('Unable to determine branch for fee invoice creation. Please ensure at least one branch exists for your company.');
        }

        // Initialize variables
        $subtotal = 0;
        $transportFare = 0;
        $invoiceItems = [];
        $discountAmount = 0;
        $discountType = null;
        $discountValue = null;

        // Find the fee setting item that matches the student's boarding type
        $studentCategory = $student->boarding_type ?? 'day'; // Default to 'day' if not set
        // Ensure category is valid for the enum
        if (!in_array($studentCategory, ['day', 'boarding'])) {
            $studentCategory = 'day';
        }
        $feeItem = $feeSetting->feeSettingItems->where('category', $studentCategory)->first();

        if ($feeItem) {
            // Use the amount directly since fee settings already specify amounts per period
            $itemAmount = $feeItem->amount;
            $subtotal += $itemAmount;

            // Add transport fare if applicable
            if ($feeItem->includes_transport && $student->bus_stop_id) {
                $busStop = BusStop::find($student->bus_stop_id);

                if ($busStop && $busStop->fare) {
                    $transportFare += $busStop->fare; // Remove the * $period multiplication
                }
            }

            $invoiceItems[] = [
                'fee_name' => ucfirst($studentCategory) . ' Fee',
                'category' => $studentCategory,
                'amount' => $itemAmount,
                'includes_transport' => (bool) $feeItem->includes_transport,
                'company_id' => Auth::user()->company_id,
                'branch_id' => $branchId,
            ];
        }

        // Calculate discount based on student's discount settings
        if ($student->discount_type && $student->discount_value) {
            $discountType = $student->discount_type;
            $discountValue = $student->discount_value;

            $baseAmount = $subtotal + $transportFare; // Amount before discount

            if ($discountType === 'percentage') {
                $discountAmount = ($baseAmount * $discountValue) / 100;
            } elseif ($discountType === 'fixed') {
                $discountAmount = min($discountValue, $baseAmount); // Don't allow discount to exceed the total amount
            }
        }

        $totalAmount = $subtotal + $transportFare - $discountAmount;
        $maxRetries = 5;
        $attempts = 0;
        $year = date('Y');
        $result = FeeInvoice::withTrashed()
                            ->selectRaw('MAX(CAST(SUBSTRING_INDEX(invoice_number, "-", -1) AS UNSIGNED)) as max_num')
                            ->where('invoice_number', 'like', "INV-{$year}-%")
                            ->first() ?? (object)['max_num' => null];
        $baseNumber = ($result->max_num ?? 0) + 1;
        $invoice = null;

        while ($attempts < $maxRetries) {
            try {
                $currentNumber = str_pad($baseNumber + $attempts, 4, '0', STR_PAD_LEFT);
                $invoiceNumberToUse = $invoiceNumber ?: "INV-{$year}-{$currentNumber}";

                $invoice = FeeInvoice::create([
                    'invoice_number' => $invoiceNumberToUse,
                    'student_id' => $student->id,
                    'class_id' => $classId,
                    'academic_year_id' => $academicYearId,
                    'fee_group_id' => $feeGroupId,
                    'period' => $period,
                    'subtotal' => $subtotal,
                    'transport_fare' => $transportFare,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0, // Will be updated if credit is applied
                    'due_date' => now()->addDays(30)->toDateString(), // Default due date
                    'issue_date' => now()->toDateString(),
                    'status' => 'issued',
                    'company_id' => Auth::user()->company_id,
                    'branch_id' => $branchId,
                    'created_by' => Auth::id(),
                ]);

                break; // Success, exit loop
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == 23000) { // Integrity constraint violation (duplicate key)
                    $attempts++;
                    if ($attempts >= $maxRetries) {
                        throw new \Exception('Failed to generate unique invoice number after ' . $maxRetries . ' attempts.');
                    }
                    // Continue to next attempt with incremented number
                } else {
                    throw $e; // Re-throw other database exceptions
                }
            }
        }

        // Create invoice items
        foreach ($invoiceItems as $itemData) {
            $itemData['fee_invoice_id'] = $invoice->id;
            FeeInvoiceItem::create($itemData);
        }

        // Generate LIPISHA control number if LIPISHA is enabled
        if (\App\Services\LipishaService::isEnabled()) {
            try {
                \Log::info('🔍 Attempting to get LIPISHA control number for invoice', [
                    'student_id' => $student->id,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $totalAmount,
                    'period' => $period,
                    'academic_year_id' => $academicYearId
                ]);

                $controlNumber = \App\Services\LipishaService::getControlNumberForInvoice(
                    $student,
                    $totalAmount,
                    $period,
                    $academicYearId,
                    $invoice->invoice_number,
                    'Fee Invoice - ' . $invoice->invoice_number
                );

                if ($controlNumber) {
                    $invoice->update(['lipisha_control_number' => $controlNumber]);
                    \Log::info('✅ LIPISHA control number generated and saved', [
                        'invoice_id' => $invoice->id,
                        'control_number' => $controlNumber
                    ]);
                } else {
                    \Log::warning('⚠️ LIPISHA control number generation returned null', [
                        'invoice_id' => $invoice->id,
                        'student_id' => $student->id
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('❌ Failed to generate LIPISHA control number for invoice', [
                    'invoice_id' => $invoice->id,
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Don't throw - invoice creation should still succeed even if control number generation fails
            }
        }

        // Create GL transactions for the invoice
        $invoice->createDoubleEntryTransactions();

        // Automatically apply available prepaid account credit to the invoice
        $this->applyPrepaidCreditToInvoice($invoice, $student);

        // Send notification to parents about the new invoice
        try {
            $notificationService = new ParentNotificationService();
            $dueDate = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') : 'N/A';
            $title = 'Ada Mpya Imetengenezwa: ' . $invoice->invoice_number;
            $message = "Ada ya shule ya {$student->first_name} {$student->last_name} (Namba ya ankara: {$invoice->invoice_number}, Kiasi: " . number_format($invoice->total_amount, 2) . " TZS) imetengenezwa. Tarehe ya mwisho: {$dueDate}.";
            $notificationService->notifyStudentParents(
                $student,
                'invoice_created',
                $title,
                $message,
                [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->total_amount,
                    'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send invoice notification', [
                'invoice_id' => $invoice->id,
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);
        }

        return true; // Invoice created successfully
    }

    /**
     * Apply available prepaid account credit to an invoice
     */
    private function applyPrepaidCreditToInvoice($invoice, $student)
    {
        try {
            // Check if auto-apply is enabled
            $autoApply = \App\Models\SystemSetting::getValue('prepaid_auto_apply_credit', true);
            if (!$autoApply) {
                return; // Auto-apply is disabled
            }

            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            // Get prepaid account for student
            $prepaidAccount = \App\Models\School\StudentPrepaidAccount::where('student_id', $student->id)
                ->where('company_id', $companyId)
                ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                }))
                ->first();

            if (!$prepaidAccount || $prepaidAccount->credit_balance <= 0) {
                return; // No prepaid account or no credit available
            }

            // Calculate outstanding amount
            $outstandingAmount = $invoice->total_amount - ($invoice->paid_amount ?? 0);
            
            if ($outstandingAmount <= 0) {
                return; // Invoice already fully paid
            }

            // Get credit application order setting
            $applyOrder = \App\Models\SystemSetting::getValue('prepaid_apply_credit_order', 'oldest_first');
            
            // Use available credit (up to outstanding amount)
            $creditToApply = min($prepaidAccount->credit_balance, $outstandingAmount);
            
            if ($creditToApply > 0) {
                // Use credit from prepaid account
                // Note: The useCredit method applies FIFO by default (oldest transactions first)
                // If newest_first is selected, we would need to modify the logic to use newest credit first
                // For now, we'll apply the credit which uses the oldest available balance
                $transaction = $prepaidAccount->useCredit($creditToApply, $invoice->id, "Auto-applied to invoice {$invoice->invoice_number}");

                // Get prepaid chart account from settings
                $prepaidAccountId = \App\Models\SystemSetting::getValue('prepaid_chart_account_id', null);
                if (!$prepaidAccountId) {
                    \Log::warning('Prepaid chart account not configured. GL transactions not created for credit application.', [
                        'invoice_id' => $invoice->id,
                        'student_id' => $student->id,
                    ]);
                } else {
                    // Get receivable account from fee group
                    $receivableAccountId = $invoice->feeGroup->receivable_account_id ??
                                         \App\Models\ChartAccount::where('account_name', 'Trade Receivables')->value('id') ??
                                         18; // Default fallback

                    $userId = Auth::id();

                    // Create GL transactions when credit is used
                    // 1. Debit Prepaid Account (reduces liability - prepaid balance decreases)
                    \App\Models\GlTransaction::create([
                        'chart_account_id' => $prepaidAccountId,
                        'customer_id' => null,
                        'supplier_id' => null,
                        'amount' => $creditToApply,
                        'nature' => 'debit',
                        'transaction_id' => $transaction->id,
                        'transaction_type' => 'student_prepaid_application',
                        'date' => $invoice->issue_date ?? now(),
                        'description' => "Prepaid credit applied to invoice {$invoice->invoice_number}",
                        'branch_id' => $branchId,
                        'user_id' => $userId,
                    ]);

                    // 2. Credit Accounts Receivable (reduces receivable - invoice is being paid)
                    \App\Models\GlTransaction::create([
                        'chart_account_id' => $receivableAccountId,
                        'customer_id' => null,
                        'supplier_id' => null,
                        'amount' => $creditToApply,
                        'nature' => 'credit',
                        'transaction_id' => $transaction->id,
                        'transaction_type' => 'student_prepaid_application',
                        'date' => $invoice->issue_date ?? now(),
                        'description' => "Prepaid credit applied to invoice {$invoice->invoice_number}",
                        'branch_id' => $branchId,
                        'user_id' => $userId,
                    ]);
                }

                // Update invoice paid amount
                $invoice->paid_amount = ($invoice->paid_amount ?? 0) + $creditToApply;
                
                // Update invoice status if fully paid
                if ($invoice->paid_amount >= $invoice->total_amount) {
                    $invoice->status = 'paid';
                }
                
                $invoice->save();

                \Log::info('Prepaid credit applied to invoice', [
                    'invoice_id' => $invoice->id,
                    'student_id' => $student->id,
                    'credit_applied' => $creditToApply,
                    'remaining_credit' => $prepaidAccount->credit_balance,
                    'apply_order' => $applyOrder
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to apply prepaid credit to invoice', [
                'invoice_id' => $invoice->id,
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw - invoice creation should still succeed even if credit application fails
        }
    }



    /**
     * Display the specified resource.
     */
    public function show($fee_invoice)
    {
        $feeInvoice = FeeInvoice::findByHashid($fee_invoice);

        if (!$feeInvoice) {
            return redirect()->route('school.fee-invoices.index')
                ->with('error', 'Fee invoice not found.');
        }

        // Check if invoice belongs to user's branch
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId && $feeInvoice->branch_id !== $branchId) {
            return redirect()->route('school.fee-invoices.index')
                ->with('error', 'You do not have permission to view this invoice.');
        }

        // Get all invoices for this student
        $studentInvoices = FeeInvoice::with(['classe', 'academicYear', 'feeGroup'])
            ->where('student_id', $feeInvoice->student_id)
            ->forCompany(Auth::user()->company_id)
            ->when($branchId, function ($query) use ($branchId) {
                return $query->forBranch($branchId);
            })
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('period', 'asc')
            ->get();

        // Calculate totals
        $totalAmount = $studentInvoices->sum('total_amount');
        $paidAmount = $studentInvoices->where('status', 'paid')->sum('total_amount');
        $outstandingAmount = $totalAmount - $paidAmount;

        // Group by quarters
        $quarterlyFees = [];
        foreach ($studentInvoices as $invoice) {
            $quarter = 'Q' . $invoice->period;
            if (!isset($quarterlyFees[$quarter])) {
                $quarterlyFees[$quarter] = [
                    'total' => 0,
                    'paid' => 0,
                    'outstanding' => 0,
                    'invoices' => []
                ];
            }
            $quarterlyFees[$quarter]['total'] += $invoice->total_amount;
            $paidAmount = $invoice->paid_amount ?? 0;
            $quarterlyFees[$quarter]['paid'] += $paidAmount;
            $quarterlyFees[$quarter]['outstanding'] += $invoice->total_amount - $paidAmount;
            $quarterlyFees[$quarter]['invoices'][] = $invoice;
        }

        return view('school.fee-invoices.show', compact(
            'feeInvoice',
            'studentInvoices',
            'totalAmount',
            'paidAmount',
            'outstandingAmount',
            'quarterlyFees'
        ));
    }

    /**
     * Get invoice details for modal display (AJAX endpoint)
     */
    public function getInvoiceDetails($fee_invoice)
    {
        try {
            $invoice = FeeInvoice::findByHashid($fee_invoice);

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            // Check if invoice belongs to user's branch
            $branchId = session('branch_id') ?: Auth::user()->branch_id;
            if ($branchId && $invoice->branch_id !== $branchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this invoice'
                ], 403);
            }

            // Load related data
            $invoice->load(['student.stream', 'classe', 'academicYear', 'feeGroup', 'payments.bankAccount']);

            // Get company information
            $company = \App\Models\Company::find(Auth::user()->company_id);

            // Get invoice items (fee breakdown)
            $invoiceItems = [];
            if ($invoice->subtotal > 0) {
                // For now, we'll show the subtotal as a single item
                // In a more detailed implementation, you might want to store individual fee items
                $invoiceItems[] = [
                    'fee_name' => $invoice->feeGroup ? $invoice->feeGroup->name . ' Fee' : 'School Fee',
                    'amount' => $invoice->subtotal
                ];
            }

            // Add transport fare if applicable
            if ($invoice->transport_fare > 0) {
                $invoiceItems[] = [
                    'fee_name' => 'Transport Fee',
                    'amount' => $invoice->transport_fare
                ];
            }

            // Format payments
            $payments = $invoice->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'hash_id' => $payment->hashid,
                    'date' => $payment->date,
                    'amount' => $payment->amount,
                    'reference' => $payment->reference,
                    'reference_number' => $payment->reference_number,
                    'description' => $payment->description,
                    'bank_account' => $payment->bankAccount ? [
                        'id' => $payment->bankAccount->id,
                        'name' => $payment->bankAccount->name,
                        'account_number' => $payment->bankAccount->account_number
                    ] : null
                ];
            });

            // Calculate totals
            $totalAmount = $invoice->total_amount;
            $paidAmount = $invoice->paid_amount ?? 0;
            $outstandingAmount = $totalAmount - $paidAmount;

            // Determine status
            $status = $invoice->status;
            if ($outstandingAmount <= 0) {
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = 'partially_paid';
            } else {
                $status = 'unpaid';
            }

            return response()->json([
                'success' => true,
                'invoice' => [
                    'id' => $invoice->id,
                    'hashid' => $invoice->hashid,
                    'invoice_number' => $invoice->invoice_number,
                    'period' => $invoice->period,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'outstanding_amount' => $outstandingAmount,
                    'status' => $status,
                    'created_at' => $invoice->created_at,
                    'due_date' => $invoice->due_date,
                    'subtotal' => $invoice->subtotal,
                    'transport_fare' => $invoice->transport_fare,
                    'discount_amount' => $invoice->discount_amount,
                    'discount_type' => $invoice->discount_type,
                    'discount_value' => $invoice->discount_value,
                    'items' => $invoiceItems,
                    'payments' => $payments
                ],
                'student' => [
                    'id' => $invoice->student->id,
                    'name' => $invoice->student->first_name . ' ' . $invoice->student->last_name,
                    'admission_number' => $invoice->student->admission_number,
                    'stream' => $invoice->student->stream ? $invoice->student->stream->name : 'N/A',
                    'class' => $invoice->classe ? $invoice->classe->name : 'N/A',
                    'academic_year' => $invoice->academicYear ? $invoice->academicYear->year_name : 'N/A'
                ],
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'logo' => $company->logo ? asset('storage/' . $company->logo) : null,
                    'address' => $company->address,
                    'phone' => $company->phone,
                    'email' => $company->email
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to get invoice details', [
                'invoice_id' => $fee_invoice,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load invoice details'
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($fee_invoice)
    {
        $feeInvoice = FeeInvoice::findByHashid($fee_invoice);

        if (!$feeInvoice) {
            return redirect()->route('school.fee-invoices.index')
                ->with('error', 'Fee invoice not found.');
        }

        // Check if invoice belongs to user's branch
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId && $feeInvoice->branch_id !== $branchId) {
            return redirect()->route('school.fee-invoices.index')
                ->with('error', 'You do not have permission to edit this invoice.');
        }

        // Load related data
        $feeInvoice->load(['student.stream', 'classe', 'academicYear']);

        return view('school.fee-invoices.edit', compact('feeInvoice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $fee_invoice)
    {
        $feeInvoice = FeeInvoice::findByHashid($fee_invoice);

        if (!$feeInvoice) {
            return redirect()->route('school.fee-invoices.index')
                ->with('error', 'Fee invoice not found.');
        }

        // Check if invoice belongs to user's branch
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId && $feeInvoice->branch_id !== $branchId) {
            return redirect()->route('school.fee-invoices.index')
                ->with('error', 'You do not have permission to update this invoice.');
        }

        $request->validate([
            'status' => 'required|in:draft,issued,paid,overdue,cancelled',
            'subtotal' => 'required|numeric|min:0',
            'transport_fare' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        // Calculate discount amount
        $discountAmount = 0;
        $baseAmount = $request->subtotal + $request->transport_fare;

        if ($request->discount_type && $request->discount_value > 0) {
            if ($request->discount_type === 'percentage') {
                $discountAmount = ($baseAmount * $request->discount_value) / 100;
            } elseif ($request->discount_type === 'fixed') {
                $discountAmount = min($request->discount_value, $baseAmount);
            }
        }

        $totalAmount = $baseAmount - $discountAmount;

        $feeInvoice->update([
            'status' => $request->status,
            'subtotal' => $request->subtotal,
            'transport_fare' => $request->transport_fare,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'due_date' => $request->due_date,
        ]);

        // Recreate GL transactions after update
        $feeInvoice->createDoubleEntryTransactions();

        return redirect()->route('school.fee-invoices.index')
            ->with('success', 'Fee invoice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($fee_invoice)
    {
        $invoice = FeeInvoice::findByHashid($fee_invoice);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Fee invoice not found.'
            ], 404);
        }

        // Check if invoice belongs to user's branch
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($branchId && $invoice->branch_id !== $branchId) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this invoice.'
            ], 403);
        }

        // Only allow deleting invoices with no payments
        if ($invoice->paid_amount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete invoice with payment amounts. Please delete payments first.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Delete associated GL transactions
            $invoice->deleteDoubleEntryTransactions();

            // Delete associated invoice items
            $invoice->items()->delete();

            // Delete the invoice
            $invoice->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fee invoice deleted successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Invoice deletion failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete invoice. Please try again.'
            ], 500);
        }
    }

    /**
     * Show all invoices for a specific student.
     */
    public function studentInvoices(Student $encodedId)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $student = $encodedId;

        // Get all invoices for this student
        $invoices = FeeInvoice::with(['classe', 'academicYear', 'feeGroup'])
            ->where('student_id', $student->id)
            ->forCompany(Auth::user()->company_id)
            ->when($branchId = session('branch_id') ?: Auth::user()->branch_id, function ($query) use ($branchId) {
                return $query->forBranch($branchId);
            })
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('period', 'asc')
            ->get();

        // Calculate totals
        $totalAmount = $invoices->sum('total_amount');
        $paidAmount = $invoices->sum('paid_amount');
        $pendingAmount = $totalAmount - $paidAmount;

        // Group by quarters
        $quarterlyFees = [];
        foreach ($invoices as $invoice) {
            $quarter = 'Q' . $invoice->period;
            if (!isset($quarterlyFees[$quarter])) {
                $quarterlyFees[$quarter] = [
                    'total' => 0,
                    'paid' => 0,
                    'outstanding' => 0,
                    'invoices' => [],
                    'payments' => []
                ];
            }
            $quarterlyFees[$quarter]['total'] += $invoice->total_amount;
            $invoicePaidAmount = $invoice->paid_amount ?? 0;
            $quarterlyFees[$quarter]['paid'] += $invoicePaidAmount;
            $quarterlyFees[$quarter]['outstanding'] += $invoice->total_amount - $invoicePaidAmount;

            // Include fee group data explicitly in the invoice data
            $invoiceData = $invoice->toArray();
            $invoiceData['fee_group_name'] = $invoice->feeGroup ? $invoice->feeGroup->name : 'N/A';
            $quarterlyFees[$quarter]['invoices'][] = $invoiceData;
        }

        // Get payments for each quarter
        foreach ($quarterlyFees as $quarter => &$data) {
            $period = str_replace('Q', '', $quarter);
            $invoiceIds = collect($data['invoices'])->pluck('id');
            
            $payments = \App\Models\Payment::with(['bankAccount'])
                ->whereIn('reference', function($query) use ($invoiceIds) {
                    $query->select('invoice_number')
                          ->from('fee_invoices')
                          ->whereIn('id', $invoiceIds);
                })
                ->where('reference_type', 'fee_invoice')
                ->orderBy('date', 'desc')
                ->get();
            
            $data['payments'] = $payments;
        }

        // Get company information for receipt
        $company = \App\Models\Company::find(Auth::user()->company_id);

        // Get bank accounts for payment forms
        $bankAccounts = \App\Models\BankAccount::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();

        // Get opening balance for the student
        $openingBalance = null;
        if ($invoices->isNotEmpty()) {
            // Get the academic year from the first invoice (most recent)
            $academicYearId = $invoices->first()->academic_year_id;
            $openingBalance = \App\Models\School\StudentFeeOpeningBalance::where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->first();
        }

        // Check if LIPISHA is enabled
        $lipishaEnabled = \App\Services\LipishaService::isEnabled();

        return view('school.fee-invoices.student', compact(
            'student',
            'invoices',
            'totalAmount',
            'paidAmount',
            'pendingAmount',
            'quarterlyFees',
            'company',
            'bankAccounts',
            'openingBalance',
            'lipishaEnabled'
        ));
    }

    /**
     * Show form for creating bulk payment for a quarter.
     */
    public function createBulkPayment(Student $encodedId, $quarter)
    {
        $student = $encodedId;

        // Get invoices for this quarter
        $quarterInvoices = FeeInvoice::with(['classe', 'academicYear', 'feeGroup'])
            ->where('student_id', $student->id)
            ->where('period', str_replace('Q', '', $quarter))
            ->forCompany(Auth::user()->company_id)
            ->get();

        return view('school.fee-invoices.bulk-payment', compact('student', 'quarter', 'quarterInvoices'));
    }

    /**
     * Show form for creating single payment for a quarter.
     */
    public function createSinglePayment(Student $encodedId, $quarter)
    {
        $student = $encodedId;

        // Get invoices for this quarter
        $quarterInvoices = FeeInvoice::with(['classe', 'academicYear', 'feeGroup'])
            ->where('student_id', $student->id)
            ->where('period', str_replace('Q', '', $quarter))
            ->forCompany(Auth::user()->company_id)
            ->get();

        return view('school.fee-invoices.single-payment', compact('student', 'quarter', 'quarterInvoices'));
    }

    /**
     * Get data for single payment modal (AJAX endpoint)
     */
    public function getSinglePaymentData(Student $encodedId)
    {
        $student = $encodedId;

        // Get all unpaid invoices for this student
        $invoices = FeeInvoice::with(['classe', 'academicYear', 'feeGroup'])
            ->where('student_id', $student->id)
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->forCompany(Auth::user()->company_id)
            ->orderBy('period')
            ->orderBy('total_amount')
            ->get();

        // Format invoices for the modal
        $formattedInvoices = $invoices->map(function ($invoice) {
            $outstandingAmount = $invoice->total_amount - ($invoice->paid_amount ?? 0);
            $periodName = $this->getPeriodName($invoice->period);

            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'period' => $periodName,
                'total_amount' => $invoice->total_amount,
                'paid_amount' => $invoice->paid_amount ?? 0,
                'outstanding_amount' => $outstandingAmount,
                'status' => $invoice->status,
                'academic_year' => $invoice->academicYear->year_name ?? 'N/A',
                'class_name' => $invoice->classe->name ?? 'N/A',
                'fee_group_name' => $invoice->feeGroup->name ?? 'N/A',
            ];
        });

        // Get available bank accounts
        $bankAccounts = \App\Models\BankAccount::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'account_name' => $account->name,
                    'account_number' => $account->account_number,
                    'bank_name' => $account->name, // Using name as bank name since no separate bank_name column
                ];
            });

        // Calculate summary
        $totalOutstanding = $formattedInvoices->sum('outstanding_amount');

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'admission_number' => $student->admission_number,
                'class_name' => $student->class->name ?? 'N/A',
                'hashid' => $student->hashid,
            ],
            'invoices' => $formattedInvoices,
            'bank_accounts' => $bankAccounts,
            'summary' => [
                'total_invoices' => $formattedInvoices->count(),
                'total_outstanding' => $totalOutstanding,
            ]
        ]);
    }

    /**
     * Store bulk payment for a quarter.
     */
    public function storeBulkPayment(Request $request, Student $encodedId, $quarter)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'notes' => 'nullable|string'
        ]);

        $student = $encodedId;

        try {
            DB::beginTransaction();

            // Get all unpaid invoices for this quarter
            $quarterInvoices = FeeInvoice::with(['feeGroup'])
                ->where('student_id', $student->id)
                ->where('period', str_replace('Q', '', $quarter))
                ->where('status', '!=', 'paid')
                ->orderBy('total_amount', 'asc')
                ->get();

            $remainingAmount = $request->amount;
            $totalApplied = 0;

            foreach ($quarterInvoices as $invoice) {
                if ($remainingAmount <= 0) break;

                $outstandingAmount = $invoice->total_amount - ($invoice->paid_amount ?? 0);

                if ($outstandingAmount > 0) {
                    $paymentAmount = min($remainingAmount, $outstandingAmount);

                    // Record payment for this invoice
                    $this->recordInvoicePayment($invoice, $paymentAmount, $request->payment_date,
                                              $request->payment_method, $request->reference_number,
                                              $request->notes, $request->bank_account_id);

                    $remainingAmount -= $paymentAmount;
                    $totalApplied += $paymentAmount;
                }
            }

            // If there's remaining amount, create advance payment
            if ($remainingAmount > 0) {
                $this->createAdvancePayment($student, $remainingAmount, $request->payment_date,
                                          $request->payment_method, $request->reference_number,
                                          $request->notes, $quarter, $request->bank_account_id);
            }

            DB::commit();

            return redirect()->route('school.fee-invoices.student', $student)
                ->with('success', 'Bulk payment of TZS ' . number_format($request->amount, 2) . ' recorded successfully for ' . $quarter . '. Applied: TZS ' . number_format($totalApplied, 2) . ($remainingAmount > 0 ? ', Advance: TZS ' . number_format($remainingAmount, 2) : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to record bulk payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Store single payment for a quarter.
     */
    public function storeSinglePayment(Request $request, Student $encodedId, $quarter)
    {
        $request->validate([
            'invoice_id' => 'required|exists:fee_invoices,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'notes' => 'nullable|string'
        ]);

        $student = $encodedId;

        try {
            DB::beginTransaction();

            $invoice = FeeInvoice::findOrFail($request->invoice_id);

            // Verify invoice belongs to student and quarter
            if ($invoice->student_id != $student->id || $invoice->period != str_replace('Q', '', $quarter)) {
                throw new \Exception('Invalid invoice selected');
            }

            $outstandingAmount = $invoice->total_amount - ($invoice->paid_amount ?? 0);
            $paymentAmount = min($request->amount, $outstandingAmount);
            $remainingAmount = $request->amount - $paymentAmount;

            // Record payment for this invoice
            $this->recordInvoicePayment($invoice, $paymentAmount, $request->payment_date,
                                      $request->payment_method, $request->reference_number,
                                      $request->notes, $request->bank_account_id);

            // If there's remaining amount, check if current invoice is fully paid before applying to next invoice
            if ($remainingAmount > 0) {
                // Refresh invoice data after payment
                $invoice->refresh();

                // Check if current invoice is now fully paid
                $isCurrentInvoiceFullyPaid = $invoice->paid_amount >= $invoice->total_amount;

                if ($isCurrentInvoiceFullyPaid) {
                    // Current invoice is fully paid, so we can apply excess to next invoice in the same quarter
                    $nextInvoice = FeeInvoice::where('student_id', $student->id)
                        ->where('period', str_replace('Q', '', $quarter))
                        ->where('status', '!=', 'paid')
                        ->where('status', '!=', 'cancelled')
                        ->where('id', '!=', $invoice->id)
                        ->orderBy('period', 'asc') // Get the next period in sequence within the quarter
                        ->first();

                    if ($nextInvoice) {
                        $nextOutstanding = $nextInvoice->total_amount - ($nextInvoice->paid_amount ?? 0);
                        $nextPaymentAmount = min($remainingAmount, $nextOutstanding);

                        $this->recordInvoicePayment($nextInvoice, $nextPaymentAmount, $request->payment_date,
                                                  $request->payment_method, $request->reference_number,
                                                  $request->notes . ' (Applied from excess payment on previous invoice)', $request->bank_account_id);

                        $remainingAmount -= $nextPaymentAmount;
                    }
                }

                // If still remaining amount (either current invoice not fully paid, or no next invoice, or excess after next invoice), create advance payment
                if ($remainingAmount > 0) {
                    $advanceReason = $isCurrentInvoiceFullyPaid ? 'Excess payment after applying to next invoice' : 'Excess payment - current invoice not fully paid';
                    $this->createAdvancePayment($student, $remainingAmount, $request->payment_date,
                                              $request->payment_method, $request->reference_number,
                                              $request->notes . ' (' . $advanceReason . ')', $quarter, $request->bank_account_id);
                }
            }

            DB::commit();

            return redirect()->route('school.fee-invoices.student', $student)
                ->with('success', 'Single payment recorded successfully for ' . $quarter);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to record single payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Store single payment for any outstanding invoice (modal version - not quarter-specific).
     */
    public function storeSinglePaymentModal(Request $request, Student $encodedId)
    {
        $request->validate([
            'invoice_id' => 'required|exists:fee_invoices,id',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'notes' => 'nullable|string'
        ]);

        $student = $encodedId;

        try {
            DB::beginTransaction();

            $invoice = FeeInvoice::findOrFail($request->invoice_id);

            // Verify invoice belongs to student
            if ($invoice->student_id != $student->id) {
                throw new \Exception('Invalid invoice selected');
            }

            // Set payment method to bank_transfer when bank account is selected
            $paymentMethod = 'bank_transfer';

            $outstandingAmount = $invoice->total_amount - ($invoice->paid_amount ?? 0);
            $paymentAmount = min($request->amount, $outstandingAmount);
            $remainingAmount = $request->amount - $paymentAmount;

            // Record payment for this invoice
            $this->recordInvoicePayment($invoice, $paymentAmount, $request->payment_date,
                                      $paymentMethod, $request->reference_number,
                                      $request->notes, $request->bank_account_id);

            // If there's remaining amount, check if current invoice is fully paid before applying to next invoice
            if ($remainingAmount > 0) {
                // Refresh invoice data after payment
                $invoice->refresh();

                // Check if current invoice is now fully paid
                $isCurrentInvoiceFullyPaid = $invoice->paid_amount >= $invoice->total_amount;

                if ($isCurrentInvoiceFullyPaid) {
                    // Current invoice is fully paid, so we can apply excess to next invoice
                    // Find the next invoice in sequence (next period) for this student
                    $nextInvoice = FeeInvoice::where('student_id', $student->id)
                        ->where('period', '>', $invoice->period) // Next period only
                        ->where('status', '!=', 'paid')
                        ->where('status', '!=', 'cancelled')
                        ->orderBy('period', 'asc') // Get the next period in sequence
                        ->first();

                    if ($nextInvoice) {
                        $nextOutstanding = $nextInvoice->total_amount - ($nextInvoice->paid_amount ?? 0);
                        $nextPaymentAmount = min($remainingAmount, $nextOutstanding);

                        $this->recordInvoicePayment($nextInvoice, $nextPaymentAmount, $request->payment_date,
                                                  $paymentMethod, $request->reference_number,
                                                  $request->notes . ' (Applied from excess payment on previous invoice)', $request->bank_account_id);

                        $remainingAmount -= $nextPaymentAmount;
                    } else {
                        // No next invoice exists - check if there are any other invoices for this student
                        $otherInvoices = FeeInvoice::where('student_id', $student->id)
                            ->where('id', '!=', $invoice->id)
                            ->where('status', '!=', 'cancelled')
                            ->exists();

                        if (!$otherInvoices) {
                            // No other invoices exist - create prepaid account credit
                            $advanceReason = 'Excess payment - no other invoices exist';
                            $this->createAdvancePayment($student, $remainingAmount, $request->payment_date,
                                                      $paymentMethod, $request->reference_number,
                                                      $request->notes . ' (' . $advanceReason . ')', 'All', $request->bank_account_id);
                            $remainingAmount = 0; // All remaining amount has been handled
                        } else {
                            // Other invoices exist but not in sequence - reject the payment
                            DB::rollBack();
                            return redirect()->back()
                                ->with('error', 'Cannot make advance payment for future periods. Please create the invoice for the next period first before making this payment.')
                                ->withInput();
                        }
                    }
                }

                // If still remaining amount after applying to next invoice (or current invoice not fully paid), create advance payment
                if ($remainingAmount > 0) {
                    $advanceReason = $isCurrentInvoiceFullyPaid ? 'Excess payment after applying to next invoice' : 'Excess payment - current invoice not fully paid';
                    $this->createAdvancePayment($student, $remainingAmount, $request->payment_date,
                                              $paymentMethod, $request->reference_number,
                                              $request->notes . ' (' . $advanceReason . ')', 'All', $request->bank_account_id);
                }
            }

            DB::commit();

            return redirect()->route('school.fee-invoices.student', $student)
                ->with('success', 'Single payment recorded successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to record single payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Record payment for a specific invoice
     */
    private function recordInvoicePayment($invoice, $amount, $paymentDate, $paymentMethod, $referenceNumber, $notes, $bankAccountId = null)
    {
        // Create payment record (similar to sales invoice payment)
        $payment = \App\Models\Payment::create([
            'customer_id' => null, // Students don't have customer IDs, but we can use student_id in a custom field or extend the model
            'amount' => $amount,
            'date' => $paymentDate,
            'reference' => $invoice->invoice_number,
            'reference_type' => 'fee_invoice',
            'description' => $notes ?? "Payment for Fee Invoice #{$invoice->invoice_number}",
            'branch_id' => $invoice->branch_id,
            'user_id' => auth()->id(),
            'bank_account_id' => $bankAccountId, // Store the selected bank account
            'cash_deposit_id' => null,
        ]);

        // Update invoice paid amount
        $invoice->increment('paid_amount', $amount);

        // Update status if fully paid
        if ($invoice->paid_amount >= $invoice->total_amount) {
            $invoice->status = 'paid';
        } elseif ($invoice->paid_amount > 0) {
            $invoice->status = 'issued'; // Partially paid invoice remains issued
        }

        $invoice->save();

        // Create GL transactions using fee group accounts
        $this->createFeePaymentGlTransactions($payment, $invoice, $amount, $paymentDate, $paymentMethod, $notes, $bankAccountId);

        return $payment;
    }

    /**
     * Create advance payment for student
     */
    private function createAdvancePayment($student, $amount, $paymentDate, $paymentMethod, $referenceNumber, $notes, $quarter, $bankAccountId = null)
    {
        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Get or create prepaid account for student
        $prepaidAccount = \App\Models\School\StudentPrepaidAccount::getOrCreateForStudent(
            $student->id,
            $companyId,
            $branchId
        );

        // Add credit to prepaid account
        $prepaidAccount->addCredit(
            $amount,
            $referenceNumber,
            null,
            $notes ?? "Advance payment for {$quarter}"
        );

        // Automatically apply credit to unpaid invoices
        $autoApplyResult = $prepaidAccount->autoApplyCreditToUnpaidInvoices();

        // Create advance payment record for tracking
        $advancePayment = \App\Models\Payment::create([
            'customer_id' => null,
            'amount' => $amount,
            'date' => $paymentDate,
            'reference' => $referenceNumber ?? "ADVANCE-{$student->id}-{$quarter}",
            'reference_type' => 'student_prepaid',
            'description' => $notes ?? "Advance payment for student {$student->first_name} {$student->last_name} - {$quarter}",
            'branch_id' => $branchId,
            'user_id' => auth()->id(),
            'bank_account_id' => $bankAccountId,
            'cash_deposit_id' => null,
        ]);

        // Create GL transactions for advance payment
        $this->createAdvancePaymentGlTransactions($advancePayment, $student, $amount, $paymentDate, $paymentMethod, $notes, $bankAccountId);

        return $advancePayment;
    }

    /**
     * Create GL transactions for fee payment
     */
    private function createFeePaymentGlTransactions($payment, $invoice, $amount, $paymentDate, $paymentMethod, $notes, $bankAccountId = null)
    {
        $user = auth()->user();
        $userId = $user ? $user->id : 1;

        $transactions = [];

        // Get fee group for receivable account
        $feeGroup = $invoice->feeGroup;

        // 1. Debit Cash/Bank Account based on payment method
        $debitAccountId = $this->getPaymentAccountId($paymentMethod, $bankAccountId);
        $transactions[] = [
            'chart_account_id' => $debitAccountId,
            'customer_id' => null, // Students don't have customer IDs
            'amount' => $amount,
            'nature' => 'debit',
            'transaction_id' => $payment->id,
            'transaction_type' => 'payment',
            'date' => $paymentDate,
            'description' => $notes ?? "Payment for Fee Invoice #{$invoice->invoice_number}",
            'branch_id' => $invoice->branch_id,
            'user_id' => $userId,
        ];

        // 2. Credit Fee Receivable Account (from fee group)
        $transactions[] = [
            'chart_account_id' => $feeGroup->receivable_account_id ??
                                 \App\Models\ChartAccount::where('account_name', 'Trade Receivables')->value('id') ??
                                 18, // Default fallback
            'customer_id' => null,
            'amount' => $amount,
            'nature' => 'credit',
            'transaction_id' => $payment->id,
            'transaction_type' => 'payment',
            'date' => $paymentDate,
            'description' => $notes ?? "Payment for Fee Invoice #{$invoice->invoice_number}",
            'branch_id' => $invoice->branch_id,
            'user_id' => $userId,
        ];

        // Create all transactions
        foreach ($transactions as $transaction) {
            \App\Models\GlTransaction::create($transaction);
        }
    }

    /**
     * Create GL transactions for advance payment
     */
    private function createAdvancePaymentGlTransactions($payment, $student, $amount, $paymentDate, $paymentMethod, $notes, $bankAccountId = null)
    {
        $user = auth()->user();
        $userId = $user ? $user->id : 1;
        $companyId = $user ? $user->company_id : null;

        $transactions = [];

        // 1. Debit Cash/Bank Account
        $debitAccountId = $this->getPaymentAccountId($paymentMethod, $bankAccountId);
        $transactions[] = [
            'chart_account_id' => $debitAccountId,
            'customer_id' => null,
            'amount' => $amount,
            'nature' => 'debit',
            'transaction_id' => $payment->id,
            'transaction_type' => 'student_prepaid',
            'date' => $paymentDate,
            'description' => $notes ?? "Advance payment for student {$student->first_name} {$student->last_name}",
            'branch_id' => session('branch_id') ?: auth()->user()->branch_id,
            'user_id' => $userId,
        ];

        // 2. Credit Student Prepaid Account (from settings)
        $prepaidAccountId = \App\Models\SystemSetting::getValue('prepaid_chart_account_id', null);
        if (!$prepaidAccountId && $companyId) {
            // Fallback to default account if not set
            $prepaidAccountId = \App\Models\ChartAccount::whereHas('accountClassGroup', function($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->whereHas('accountClass', function($q2) {
                          $q2->where('name', 'LIKE', '%liabilit%');
                      });
                })
                ->where(function($query) {
                    $query->where('account_name', 'LIKE', '%student%prepaid%')
                          ->orWhere('account_name', 'LIKE', '%prepaid%student%');
                })
                ->value('id') ?? 1;
        }
        
        if (!$prepaidAccountId) {
            $prepaidAccountId = 1; // Final fallback
        }
        
        $transactions[] = [
            'chart_account_id' => $prepaidAccountId,
            'customer_id' => null,
            'amount' => $amount,
            'nature' => 'credit',
            'transaction_id' => $payment->id,
            'transaction_type' => 'student_prepaid',
            'date' => $paymentDate,
            'description' => $notes ?? "Advance payment for student {$student->first_name} {$student->last_name}",
            'branch_id' => session('branch_id') ?: auth()->user()->branch_id,
            'user_id' => $userId,
        ];

        // Create all transactions
        foreach ($transactions as $transaction) {
            \App\Models\GlTransaction::create($transaction);
        }
    }

    /**
     * Update an existing payment
     */
    public function updatePayment(Request $request, $paymentId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'reference_number' => 'nullable|string',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $payment = \App\Models\Payment::findOrFail($paymentId);

            // Verify the payment belongs to a fee invoice
            if ($payment->reference_type !== 'fee_invoice') {
                throw new \Exception('Invalid payment type for fee invoice update');
            }

            // Find the associated invoice
            $invoice = FeeInvoice::where('invoice_number', $payment->reference)->first();
            if (!$invoice) {
                throw new \Exception('Associated invoice not found');
            }

            // Calculate the difference in amount
            $oldAmount = $payment->amount;
            $newAmount = $request->amount;
            $amountDifference = $newAmount - $oldAmount;

            // Update payment record
            $payment->update([
                'amount' => $newAmount,
                'date' => $request->date,
                'bank_account_id' => $request->bank_account_id,
                'reference_number' => $request->reference_number,
                'description' => $request->notes,
            ]);

            // Recalculate invoice paid amount from all payments
            $totalPaidAmount = $invoice->payments()->sum('amount');
            $invoice->paid_amount = $totalPaidAmount;

            // Calculate overpayment (if any)
            $overpayment = max(0, $totalPaidAmount - $invoice->total_amount);

            // Update invoice status based on new paid amount
            if ($invoice->paid_amount >= $invoice->total_amount) {
                $invoice->status = 'paid';
            } elseif ($invoice->paid_amount > 0) {
                $invoice->status = 'issued'; // Partially paid invoice remains issued
            } else {
                $invoice->status = 'issued';
            }
            $invoice->save();

            // Reverse old GL transactions and create new ones
            $this->reversePaymentGlTransactions($payment);
            
            // Calculate the amount that should be applied to this invoice (not exceeding invoice total)
            $invoiceOutstandingBefore = $invoice->total_amount - ($totalPaidAmount - $newAmount);
            $paymentAmountForInvoice = min($newAmount, $invoiceOutstandingBefore);
            
            // Create GL transactions for the payment applied to invoice
            $this->createFeePaymentGlTransactions($payment, $invoice, $paymentAmountForInvoice, $request->date, 'bank_transfer', $request->notes, $request->bank_account_id);
            
            // If there's an overpayment, handle it
            if ($overpayment > 0) {
                // Get the student
                $student = $invoice->student;
                
                // Calculate the overpayment amount from this specific payment
                $overpaymentFromThisPayment = max(0, $newAmount - $paymentAmountForInvoice);
                
                if ($overpaymentFromThisPayment > 0) {
                    // Check if there are any other invoices for this student
                    $otherInvoices = FeeInvoice::where('student_id', $student->id)
                        ->where('id', '!=', $invoice->id)
                        ->where('status', '!=', 'cancelled')
                        ->exists();
                    
                    if (!$otherInvoices) {
                        // No other invoices exist - create prepaid account credit for overpayment
                        $this->createAdvancePayment(
                            $student,
                            $overpaymentFromThisPayment,
                            $request->date,
                            'bank_transfer',
                            $request->reference_number,
                            $request->notes . ' (Overpayment from payment update - no other invoices exist)',
                            'All',
                            $request->bank_account_id
                        );
                    } else {
                        // Other invoices exist - try to apply to next invoice
                        $nextInvoice = FeeInvoice::where('student_id', $student->id)
                            ->where('period', '>', $invoice->period)
                            ->where('status', '!=', 'paid')
                            ->where('status', '!=', 'cancelled')
                            ->orderBy('period', 'asc')
                            ->first();
                        
                        if ($nextInvoice) {
                            $nextOutstanding = $nextInvoice->total_amount - ($nextInvoice->paid_amount ?? 0);
                            $nextPaymentAmount = min($overpaymentFromThisPayment, $nextOutstanding);
                            
                            if ($nextPaymentAmount > 0) {
                                $this->recordInvoicePayment(
                                    $nextInvoice,
                                    $nextPaymentAmount,
                                    $request->date,
                                    'bank_transfer',
                                    $request->reference_number,
                                    $request->notes . ' (Applied from overpayment on previous invoice)',
                                    $request->bank_account_id
                                );
                                
                                // If still overpayment after applying to next invoice, create prepaid credit
                                $remainingOverpayment = $overpaymentFromThisPayment - $nextPaymentAmount;
                                if ($remainingOverpayment > 0) {
                                    $this->createAdvancePayment(
                                        $student,
                                        $remainingOverpayment,
                                        $request->date,
                                        'bank_transfer',
                                        $request->reference_number,
                                        $request->notes . ' (Excess overpayment after applying to next invoice)',
                                        'All',
                                        $request->bank_account_id
                                    );
                                }
                            }
                        } else {
                            // No next invoice - create prepaid account credit
                            $this->createAdvancePayment(
                                $student,
                                $overpaymentFromThisPayment,
                                $request->date,
                                'bank_transfer',
                                $request->reference_number,
                                $request->notes . ' (Overpayment - no other invoices exist)',
                                'All',
                                $request->bank_account_id
                            );
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully',
                'payment' => $payment->load('bankAccount')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment update failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an existing payment
     */
    public function deletePayment($paymentId)
    {
        try {
            DB::beginTransaction();

            $payment = \App\Models\Payment::findOrFail($paymentId);

            // Verify the payment belongs to a fee invoice
            if ($payment->reference_type !== 'fee_invoice') {
                throw new \Exception('Invalid payment type for fee invoice deletion');
            }

            // Find the associated invoice
            $invoice = FeeInvoice::where('invoice_number', $payment->reference)->first();
            if (!$invoice) {
                throw new \Exception('Associated invoice not found');
            }

            $paymentAmount = $payment->amount;

            // Reverse GL transactions
            $this->reversePaymentGlTransactions($payment);

            // Update invoice paid amount
            $invoice->decrement('paid_amount', $paymentAmount);

            // Update invoice status
            if ($invoice->paid_amount >= $invoice->total_amount) {
                $invoice->status = 'paid';
            } elseif ($invoice->paid_amount > 0) {
                $invoice->status = 'issued'; // Partially paid invoice remains issued
            } else {
                $invoice->status = 'issued';
            }
            $invoice->save();

            // Delete the payment record
            $payment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment deletion failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reverse GL transactions for a payment
     */
    private function reversePaymentGlTransactions($payment)
    {
        // Find and delete existing GL transactions for this payment
        \App\Models\GlTransaction::where('transaction_id', $payment->id)
            ->where('transaction_type', 'payment')
            ->delete();
    }

    /**
     * Get the appropriate payment account ID based on payment method
     */
    private function getPaymentAccountId($paymentMethod, $bankAccountId = null)
    {
        // If bank account is provided, use its chart account
        if ($bankAccountId) {
            $bankAccount = \App\Models\BankAccount::find($bankAccountId);
            if ($bankAccount && $bankAccount->chart_account_id) {
                return $bankAccount->chart_account_id;
            }
        }

        // Default to cash account based on payment method
        switch ($paymentMethod) {
            case 'bank_transfer':
            case 'cheque':
                // For bank transfers/cheques, use a default bank account if available
                $defaultBankAccount = \App\Models\BankAccount::where('company_id', auth()->user()->company_id)->first();
                return $defaultBankAccount ? $defaultBankAccount->chart_account_id : 1; // Default to account ID 1
            case 'cash':
            default:
                // Use system setting for cash account
                return \App\Models\SystemSetting::where('key', 'cash_account_id')->value('value') ?? 1;
        }
    }

    /**
     * Validate invoice generation without creating invoices.
     */
    public function validateInvoices(Request $request)
    {
        $rules = [
            'generation_type' => 'required|in:single,bulk',
            'class_id' => 'required|exists:classes,id',
            'fee_group_id' => 'required|exists:fee_groups,id',
            'period' => 'required|integer|min:1|max:7',
        ];

        // Add student_id validation only for single generation
        if ($request->generation_type === 'single') {
            $rules['student_id'] = 'required|exists:students,id';
        }

        $request->validate($rules);

        try {
            // Get common data
            $class = \App\Models\School\Classe::findOrFail($request->class_id);
            $feeGroup = \App\Models\FeeGroup::findOrFail($request->fee_group_id);
            $academicYear = AcademicYear::where('company_id', Auth::user()->company_id)
                ->where('is_current', true)
                ->first();

            if (!$academicYear) {
                throw new \Exception('No current academic year found.');
            }

            $currencySymbol = 'TZS'; // Default currency, you can make this dynamic

            // Use academic year name or generate one from dates
            $academicYearName = $academicYear->year_name ?: ($academicYear->start_date . ' - ' . $academicYear->end_date);

            if ($request->generation_type === 'single') {
                $validationData = $this->validateSingleInvoice($request);

                return response()->json([
                    'success' => true,
                    'type' => 'single',
                    'class_name' => $class->name,
                    'fee_group_name' => $feeGroup->name,
                    'period' => $request->period,
                    'academic_year_name' => $academicYearName,
                    'currency_symbol' => $currencySymbol,
                    'student' => $validationData
                ]);
            } else {
                $validationData = $this->validateBulkInvoices($request);

                // Calculate total amount from all students that will be created
                $totalAmount = 0;
                $totalDiscountAmount = 0;
                $totalSubtotal = 0;
                $totalTransportFare = 0;
                $students = [];

                foreach ($validationData['validation_list'] as $studentValidation) {
                    if ($studentValidation['status'] === 'will_create') {
                        $totalAmount += $studentValidation['total_amount'];
                        $totalDiscountAmount += $studentValidation['discount_amount'] ?? 0;
                        $totalSubtotal += $studentValidation['subtotal'];
                        $totalTransportFare += $studentValidation['transport_fare'];
                    }

                    // For Full Year, show detailed breakdown
                    if ($request->period == 5) {
                        $students[] = [
                            'name' => $studentValidation['student_name'],
                            'admission_number' => $studentValidation['admission_number'],
                            'amount' => $studentValidation['subtotal'], // Total for all quarters
                            'subtotal' => $studentValidation['subtotal'], // Total for all quarters
                            'transport_fare' => $studentValidation['transport_fare'], // Total transport fare
                            'discount_amount' => $studentValidation['discount_amount'] ?? 0,
                            'total_amount' => $studentValidation['total_amount'], // Total for all quarters
                            'boarding_type' => $studentValidation['boarding_type'] ?? 'day',
                            'has_transport' => $studentValidation['has_transport'] ?? false,
                            'bus_stop_name' => $studentValidation['periods'][0]['bus_stop_name'] ?? null, // Use first period's bus stop info
                            'bus_stop_fare' => $studentValidation['transport_fare'], // Total transport fare
                            'status' => $studentValidation['status'],
                            'existing_invoice_id' => null, // For Full Year, we don't show individual edit links
                            'reason' => $studentValidation['status'] === 'already_exists' ? 'Some or all quarters already have invoices' : null,
                            'periods_detail' => $studentValidation['periods'] // Include detailed period breakdown
                        ];
                    } else {
                        // For single period, use existing logic
                        $students[] = [
                            'name' => $studentValidation['student_name'],
                            'admission_number' => $studentValidation['admission_number'],
                            'amount' => $studentValidation['subtotal'] ?? 0,
                            'subtotal' => $studentValidation['subtotal'] ?? 0,
                            'transport_fare' => $studentValidation['transport_fare'] ?? 0,
                            'discount_amount' => $studentValidation['discount_amount'] ?? 0,
                            'total_amount' => $studentValidation['total_amount'] ?? 0,
                            'boarding_type' => $studentValidation['boarding_type'] ?? 'day',
                            'has_transport' => $studentValidation['has_transport'] ?? false,
                            'bus_stop_name' => $studentValidation['periods'][0]['bus_stop_name'] ?? null,
                            'bus_stop_fare' => $studentValidation['periods'][0]['bus_stop_fare'] ?? 0,
                            'status' => $studentValidation['status'],
                            'existing_invoice_id' => $studentValidation['periods'][0]['existing_invoice_id'] ?? null,
                            'existing_invoice_number' => $studentValidation['periods'][0]['existing_invoice_number'] ?? null,
                            'reason' => $studentValidation['periods'][0]['reason'] ?? null
                        ];
                    }
                }

                return response()->json([
                    'success' => true,
                    'type' => 'bulk',
                    'class_name' => $class->name,
                    'fee_group_name' => $feeGroup->name,
                    'period' => $request->period,
                    'academic_year_name' => $academicYearName,
                    'currency_symbol' => $currencySymbol,
                    'total_students' => $validationData['total_students'],
                    'will_create' => $validationData['will_create'],
                    'will_skip' => $validationData['will_skip'],
                    'periods_to_generate' => $validationData['periods_to_generate'],
                    'subtotal' => $totalSubtotal,
                    'transport_total' => $totalTransportFare,
                    'total_discount' => $totalDiscountAmount,
                    'total_amount' => $totalAmount,
                    'students' => $students
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Fee invoice validation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate single student invoice.
     */
    private function validateSingleInvoice(Request $request)
    {
        $student = Student::findOrFail($request->student_id);

        // Use the student's academic year, not the current academic year
        // This allows creating invoices for students in different academic years
        $academicYearId = $student->academic_year_id;
        
        if (!$academicYearId) {
            // Fallback to current academic year if student doesn't have one set
            $academicYear = AcademicYear::where('company_id', Auth::user()->company_id)
                ->where('is_current', true)
                ->first();

            if (!$academicYear) {
                throw new \Exception('No current academic year found and student has no academic year set.');
            }
            
            $academicYearId = $academicYear->id;
        }

        return $this->validateInvoiceForStudent($student, $request->class_id, $academicYearId, $request->period, $request->fee_group_id);
    }

    /**
     * Validate bulk invoices for class.
     */
    private function validateBulkInvoices(Request $request)
    {
        // Get current academic year
        $academicYear = AcademicYear::where('company_id', Auth::user()->company_id)
            ->where('is_current', true)
            ->first();

        if (!$academicYear) {
            throw new \Exception('No current academic year found.');
        }

        $students = Student::where('class_id', $request->class_id)
            ->where('academic_year_id', $academicYear->id)
            ->where('status', 'active')
            ->get();

        $validationList = [];
        $willCreate = 0;
        $willSkip = 0;

        // If Full Year (period 5) is selected, validate for all quarters (1-4)
        // Note: Full Year only validates Q1-Q4, not Terms
        $periodsToValidate = $request->period == 5 ? [1, 2, 3, 4] : [$request->period];

        foreach ($students as $student) {
            // Use the student's academic year for validation, not the current academic year
            // This ensures invoices are checked against the correct academic year
            $studentAcademicYearId = $student->academic_year_id ?? $academicYear->id;
            
            $studentValidation = [
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'admission_number' => $student->admission_number,
                'boarding_type' => $student->boarding_type,
                'has_transport' => $student->bus_stop_id ? true : false,
                'periods' => []
            ];

            $studentWillCreate = false;
            $studentWillSkip = false;

            foreach ($periodsToValidate as $period) {
                $periodValidation = $this->validateInvoiceForStudent($student, $request->class_id, $studentAcademicYearId, $period, $request->fee_group_id);
                $studentValidation['periods'][] = [
                    'period' => $period,
                    'status' => $periodValidation['status'],
                    'subtotal' => $periodValidation['subtotal'] ?? 0,
                    'transport_fare' => $periodValidation['transport_fare'] ?? 0,
                    'discount_type' => $periodValidation['discount_type'] ?? null,
                    'discount_value' => $periodValidation['discount_value'] ?? null,
                    'discount_amount' => $periodValidation['discount_amount'] ?? 0,
                    'total_amount' => $periodValidation['total_amount'] ?? 0,
                    'bus_stop_name' => $periodValidation['bus_stop_name'] ?? null,
                    'bus_stop_fare' => $periodValidation['bus_stop_fare'] ?? 0,
                    'reason' => $periodValidation['reason'] ?? null,
                    'existing_invoice_id' => $periodValidation['existing_invoice_id'] ?? null,
                    'existing_invoice_number' => $periodValidation['existing_invoice_number'] ?? null
                ];

                if ($periodValidation['status'] === 'will_create') {
                    $studentWillCreate = true;
                } elseif ($periodValidation['status'] === 'already_exists') {
                    $studentWillSkip = true;
                }
            }

            // Determine overall status for the student
            $canCreateAny = false;
            foreach ($studentValidation['periods'] as $periodData) {
                if ($periodData['status'] === 'will_create') {
                    $canCreateAny = true;
                    break;
                }
            }

            if ($canCreateAny) {
                $studentValidation['status'] = 'will_create';
                $willCreate++;
            } elseif ($studentWillSkip) {
                $studentValidation['status'] = 'already_exists';
                $willSkip++;
            } else {
                $studentValidation['status'] = 'no_fee_settings';
                $willSkip++;
            }

            // Calculate total amounts for all periods
            $totalSubtotal = 0;
            $totalTransportFare = 0;
            $totalDiscountAmount = 0;
            $totalAmount = 0;
            foreach ($studentValidation['periods'] as $periodData) {
                if ($periodData['status'] === 'will_create') {
                    $totalSubtotal += $periodData['subtotal'];
                    $totalTransportFare += $periodData['transport_fare'];
                    $totalDiscountAmount += $periodData['discount_amount'];
                    $totalAmount += $periodData['total_amount'];
                }
            }

            $studentValidation['subtotal'] = $totalSubtotal;
            $studentValidation['transport_fare'] = $totalTransportFare;
            $studentValidation['discount_amount'] = $totalDiscountAmount;
            $studentValidation['total_amount'] = $totalAmount;

            $validationList[] = $studentValidation;
        }

        return [
            'type' => 'bulk',
            'total_students' => $students->count(),
            'will_create' => $willCreate,
            'will_skip' => $willSkip,
            'periods_to_generate' => count($periodsToValidate),
            'validation_list' => $validationList
        ];
    }

    /**
     * Validate invoice for a specific student without creating it.
     */
    private function validateInvoiceForStudent($student, $classId, $academicYearId, $period, $feeGroupId)
    {
        // Check if invoice already exists
        // Must match: student, class, academic year, period, AND fee group
        $existingInvoice = FeeInvoice::where('student_id', $student->id)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('period', $period)
            ->where('fee_group_id', $feeGroupId)
            ->where('status', '!=', 'cancelled')
            ->first();

        // Get bus stop information if available
        $busStopName = null;
        $busStopFare = 0;
        if ($student->bus_stop_id) {
            $busStop = BusStop::find($student->bus_stop_id);
            $busStopName = $busStop ? $busStop->stop_name : null;
            $busStopFare = $busStop ? $busStop->fare : 0;
        }

        if ($existingInvoice) {
            // For existing invoices, calculate the base amount (subtotal) and transport fare
            $baseAmount = $existingInvoice->subtotal;
            $transportFare = $existingInvoice->transport_fare;

            return [
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'admission_number' => $student->admission_number,
                'boarding_type' => $student->boarding_type,
                'has_transport' => $student->bus_stop_id ? true : false,
                'bus_stop_name' => $busStopName,
                'bus_stop_fare' => $transportFare, // Use existing invoice's transport fare
                'status' => 'already_exists',
                'reason' => 'Invoice already exists for this period',
                'existing_invoice_id' => $existingInvoice->hashid,
                'existing_invoice_number' => $existingInvoice->invoice_number,
                'subtotal' => $baseAmount, // Base fee amount
                'transport_fare' => $transportFare,
                'total_amount' => $existingInvoice->total_amount,
                'can_edit' => true // Flag to indicate this invoice can be edited
            ];
        }

        // Get fee settings for the class and specific period
        $feePeriod = $this->mapPeriodToFeePeriod($period);

        // Get branches the user has access to
        $userBranches = Auth::user()->branches()->pluck('branches.id')->toArray();
        // Include the user's primary branch if set
        if (Auth::user()->branch_id) {
            $userBranches[] = Auth::user()->branch_id;
        }
        $userBranches = array_unique($userBranches);

        // Fee settings are now reusable across academic years (academic_year_id is optional)
        $feeSetting = FeeSetting::where('class_id', $classId)
            ->where('fee_period', $feePeriod)
            ->where('is_active', true)
            ->where('company_id', Auth::user()->company_id)
            ->where(function ($query) use ($userBranches) {
                $query->whereIn('branch_id', $userBranches)
                      ->orWhereNull('branch_id');
            })
            ->first();

        if (!$feeSetting) {
            return [
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'admission_number' => $student->admission_number,
                'boarding_type' => $student->boarding_type,
                'has_transport' => $student->bus_stop_id ? true : false,
                'bus_stop_name' => $busStopName,
                'bus_stop_fare' => $busStopFare,
                'status' => 'no_fee_settings',
                'reason' => 'No fee settings found for this class and period',
                'total_amount' => 0
            ];
        }

        $subtotal = 0;
        $transportFare = 0;
        $feeItems = [];

        // Find the fee setting item that matches the student's boarding type
        $studentCategory = $student->boarding_type ?? 'day'; // Default to 'day' if not set
        // Ensure category is valid for the enum
        if (!in_array($studentCategory, ['day', 'boarding'])) {
            $studentCategory = 'day';
        }
        $feeItem = $feeSetting->feeSettingItems->where('category', $studentCategory)->first();

        if ($feeItem) {
            // Use the amount directly since fee settings already specify amounts per period
            $itemAmount = $feeItem->amount;
            $subtotal += $itemAmount;

            // Add transport fare if applicable
            if ($feeItem->includes_transport && $student->bus_stop_id) {
                $busStop = BusStop::find($student->bus_stop_id);

                if ($busStop && $busStop->fare) {
                    $transportFare += $busStop->fare; // Remove the * $period multiplication
                }
            }

            $feeItems[] = [
                'name' => $feeItem->name ?: ucfirst($studentCategory) . ' Fee',
                'amount' => $itemAmount,
                'includes_transport' => $feeItem->includes_transport,
                'category' => $feeItem->category
            ];
        }

        // Calculate discount based on student's discount settings
        $discountAmount = 0;
        $discountType = null;
        $discountValue = null;

        if ($student->discount_type && $student->discount_value) {
            $discountType = $student->discount_type;
            $discountValue = $student->discount_value;

            $baseAmount = $subtotal + $transportFare; // Amount before discount

            if ($discountType === 'percentage') {
                $discountAmount = ($baseAmount * $discountValue) / 100;
            } elseif ($discountType === 'fixed') {
                $discountAmount = min($discountValue, $baseAmount); // Don't allow discount to exceed the total amount
            }
        }

        $totalAmount = $subtotal + $transportFare - $discountAmount;

        return [
            'student_id' => $student->id,
            'student_name' => $student->first_name . ' ' . $student->last_name,
            'admission_number' => $student->admission_number,
            'boarding_type' => $student->boarding_type,
            'has_transport' => $student->bus_stop_id ? true : false,
            'bus_stop_name' => $busStopName,
            'bus_stop_fare' => $busStopFare,
            'status' => 'will_create',
            'subtotal' => $subtotal,
            'transport_fare' => $transportFare,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'fee_items' => $feeItems
        ];
    }

    /**
     * Format status for display
     */
    private function formatStatus($status)
    {
        $badges = [
            'draft' => '<span class="badge bg-secondary text-dark">Draft</span>',
            'issued' => '<span class="badge bg-primary">Issued</span>',
            'paid' => '<span class="badge bg-success">Paid</span>',
            'overdue' => '<span class="badge bg-danger">Overdue</span>',
            'cancelled' => '<span class="badge bg-dark">Cancelled</span>',
        ];
        return $badges[$status] ?? '<span class="badge bg-warning">' . ucfirst($status) . '</span>';
    }

    /**
     * Calculate overall status for a student's invoices
     */
    private function calculateOverallStatus($studentInvoices)
    {
        $totalAmount = $studentInvoices->sum('total_amount');
        $paidAmount = $studentInvoices->sum('paid_amount');

        if ($paidAmount >= $totalAmount) {
            return 'paid';
        } elseif ($paidAmount > 0) {
            return 'partially_paid';
        } elseif ($studentInvoices->contains('status', 'overdue')) {
            return 'overdue';
        } else {
            return 'issued';
        }
    }

    /**
     * Format overall status for display
     */
    private function formatOverallStatus($status)
    {
        $badges = [
            'paid' => '<span class="badge bg-success">Paid</span>',
            'partially_paid' => '<span class="badge bg-warning text-dark">Partially Paid</span>',
            'issued' => '<span class="badge bg-primary">Issued</span>',
            'overdue' => '<span class="badge bg-danger">Overdue</span>',
            'cancelled' => '<span class="badge bg-dark">Cancelled</span>',
        ];
        return $badges[$status] ?? '<span class="badge bg-warning">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
    }

    /**
     * Format actions for display
     */
    private function formatActions($invoice)
    {
        $student = $invoice->student;
        return '<a href="' . route('school.fee-invoices.student', $student) . '" class="btn btn-info btn-sm" title="View Fee Invoices">
                        <i class="bx bx-show"></i>
                    </a>
                    <a href="' . route('school.students.show', $student) . '" class="btn btn-secondary btn-sm" title="View Student Details">
                        <i class="bx bx-user"></i>
                    </a>';
    }

    /**
     * Generate bulk remaining balance PDF report
     */
    public function bulkRemainingBalance(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'quarters' => 'required|array|min:1',
            'quarters.*' => 'integer|min:1|max:4'
        ]);

        try {
            // Get current academic year
            $academicYear = AcademicYear::where('company_id', Auth::user()->company_id)
                ->where('is_current', true)
                ->first();

            if (!$academicYear) {
                return response()->json(['message' => 'No current academic year found.'], 400);
            }

            // Get class information
            $class = \App\Models\School\Classe::findOrFail($request->class_id);

            // Get all students in the class with active status
            $students = Student::where('class_id', $request->class_id)
                ->where('academic_year_id', $academicYear->id)
                ->where('status', 'active')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            \Log::info('Bulk remaining balance debug', [
                'class_id' => $request->class_id,
                'quarters' => $request->quarters,
                'academic_year_id' => $academicYear->id,
                'students_count' => $students->count(),
                'students' => $students->pluck('id')->toArray()
            ]);

            $reportData = [];
            $totalOutstanding = 0;

            foreach ($students as $student) {
                $studentData = [
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'admission_number' => $student->admission_number,
                    'quarters' => []
                ];

                $studentTotalOutstanding = 0;

                foreach ($request->quarters as $quarter) {
                    // Get invoice for this quarter
                    $invoice = FeeInvoice::where('student_id', $student->id)
                        ->where('class_id', $request->class_id)
                        ->where('academic_year_id', $academicYear->id)
                        ->where('period', $quarter)
                        ->where('status', '!=', 'cancelled')
                        ->first();

                    if ($invoice) {
                        $outstanding = $invoice->total_amount - ($invoice->paid_amount ?? 0);
                        if ($outstanding > 0) {
                            $studentData['quarters'][] = [
                                'quarter' => 'Q' . $quarter,
                                'total_amount' => $invoice->total_amount,
                                'paid_amount' => $invoice->paid_amount ?? 0,
                                'outstanding' => $outstanding,
                                'invoice_number' => $invoice->invoice_number
                            ];
                            $studentTotalOutstanding += $outstanding;
                        }
                    }
                }

                // Only include students with outstanding balances
                if ($studentTotalOutstanding > 0) {
                    $studentData['total_outstanding'] = $studentTotalOutstanding;
                    $reportData[] = $studentData;
                    $totalOutstanding += $studentTotalOutstanding;
                }
            }

            \Log::info('Bulk remaining balance report data', [
                'report_data_count' => count($reportData),
                'total_outstanding' => $totalOutstanding,
                'empty_report' => empty($reportData)
            ]);

            if (empty($reportData)) {
                \Log::info('No students with outstanding balances found');
                return response()->json(['message' => 'No students with outstanding balances found for the selected criteria.'], 200);
            }

            // Generate PDF
            $pdf = $this->generateBulkRemainingBalancePDF($reportData, $class, $academicYear, $request->quarters, $totalOutstanding);

            return $pdf->download('bulk_remaining_balance_report.pdf');

        } catch (\Exception $e) {
            \Log::error('Bulk remaining balance report generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Failed to generate report. Please try again. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate bulk PDF of invoices for students with outstanding balances
     */
    public function generateBulkInvoicesForOutstandingStudents(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'outstanding_quarters' => 'required|array|min:1',
            'outstanding_quarters.*' => 'integer|min:1|max:4'
        ]);

        try {
            // Get current academic year
            $academicYear = AcademicYear::where('company_id', Auth::user()->company_id)
                ->where('is_current', true)
                ->first();

            if (!$academicYear) {
                return response()->json(['message' => 'No current academic year found.'], 400);
            }

            // Get class information
            $class = \App\Models\School\Classe::findOrFail($request->class_id);

            // Find students with invoices in the specified quarters (regardless of payment status)
            $studentsWithOutstanding = Student::where('class_id', $request->class_id)
                ->where('academic_year_id', $academicYear->id)
                ->where('status', 'active')
                ->whereHas('feeInvoices', function($query) use ($request, $academicYear) {
                    $query->where('academic_year_id', $academicYear->id)
                          ->whereIn('period', $request->outstanding_quarters)
                          ->where('status', '!=', 'cancelled');
                })
                ->with(['feeInvoices' => function($query) use ($request, $academicYear) {
                    $query->where('academic_year_id', $academicYear->id)
                          ->whereIn('period', $request->outstanding_quarters)
                          ->where('status', '!=', 'cancelled')
                          ->orderBy('period', 'asc');
                }])
                ->get();

            \Log::info('Bulk invoice PDF generation for students with invoices in selected quarters', [
                'class_id' => $request->class_id,
                'outstanding_quarters' => $request->outstanding_quarters,
                'students_found' => $studentsWithOutstanding->count()
            ]);

            if ($studentsWithOutstanding->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students found with invoices in the selected quarters.'
                ], 200);
            }

            // Collect all invoice IDs for these students
            $invoiceIds = [];
            foreach ($studentsWithOutstanding as $student) {
                $invoiceIds = array_merge($invoiceIds, $student->feeInvoices->pluck('id')->toArray());
            }

            if (empty($invoiceIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No invoices found for students with outstanding balances.'
                ], 200);
            }

            // Load all invoices with relationships
            $allInvoices = FeeInvoice::with(['student', 'classe', 'academicYear', 'feeGroup', 'items'])
                ->whereIn('id', $invoiceIds)
                ->orderBy('student_id')
                ->orderBy('period')
                ->get();

            \Log::info('Loaded invoices with relationships for PDF generation', [
                'total_invoices' => $allInvoices->count(),
                'invoice_ids' => $allInvoices->pluck('id')->toArray()
            ]);

            // Generate PDF with all invoices
            $pdf = $this->generateBulkInvoicesPDF($allInvoices, $class, $academicYear, $request->outstanding_quarters);

            return $pdf->download('bulk_invoices_outstanding_students.pdf');

        } catch (\Exception $e) {
            \Log::error('Bulk invoice PDF generation for outstanding students failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate bulk remaining balance PDF
     */
    private function generateBulkRemainingBalancePDF($reportData, $class, $academicYear, $quarters, $totalOutstanding)
    {
        try {
            \Log::info('Starting PDF generation', [
                'report_data_count' => count($reportData),
                'class_name' => $class->name,
                'academic_year' => $academicYear->year_name,
                'quarters' => $quarters
            ]);

            // Use DomPDF which is already installed
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('school.fee-invoices.bulk-remaining-balance-pdf', [
                'reportData' => $reportData,
                'class' => $class,
                'academicYear' => $academicYear,
                'quarters' => $quarters,
                'totalOutstanding' => $totalOutstanding,
                'company' => \App\Models\Company::find(Auth::user()->company_id)
            ]);

            // Set paper size and orientation
            $pdf->setPaper('a4', 'landscape');

            \Log::info('PDF generated successfully');
            return $pdf;
        } catch (\Exception $e) {
            \Log::error('PDF generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate bulk invoices PDF for outstanding students
     */
    private function generateBulkInvoicesPDF($invoices, $class, $academicYear, $outstandingQuarters)
    {
        try {
            \Log::info('Starting bulk invoices PDF generation', [
                'invoices_count' => $invoices->count(),
                'class_name' => $class->name,
                'academic_year' => $academicYear->year_name,
                'outstanding_quarters' => $outstandingQuarters
            ]);

            // Check if invoices have required relationships
            $firstInvoice = $invoices->first();
            if ($firstInvoice) {
                \Log::info('First invoice relationships check', [
                    'has_student' => $firstInvoice->student ? true : false,
                    'has_classe' => $firstInvoice->classe ? true : false,
                    'has_academic_year' => $firstInvoice->academicYear ? true : false,
                    'has_fee_group' => $firstInvoice->feeGroup ? true : false,
                    'has_items' => $firstInvoice->items ? true : false,
                ]);
            }

            // Use DomPDF to generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('school.fee-invoices.bulk-invoices-pdf', [
                'invoices' => $invoices,
                'class' => $class,
                'academicYear' => $academicYear,
                'outstandingQuarters' => $outstandingQuarters,
                'company' => \App\Models\Company::find(Auth::user()->company_id)
            ]);

            // Set paper size
            $pdf->setPaper('a4', 'portrait');

            \Log::info('Bulk invoices PDF generated successfully');
            return $pdf;
        } catch (\Exception $e) {
            \Log::error('Bulk invoices PDF generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    public function getStudents(Request $request)
    {
        \Log::info('getStudents method called - START', [
            'request_all' => $request->all(),
            'user_authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'user_branch_id' => Auth::user()->branch_id,
            'session_branch_id' => session('branch_id'),
            'session_id' => session()->getId(),
            'headers' => $request->headers->all()
        ]);

        $classId = $request->get('class_id');

        if (!$classId) {
            \Log::warning('getStudents: No class_id provided');
            return response()->json([]);
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        \Log::info('getStudents - branch info', [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'session_branch_id' => session('branch_id'),
            'user_branch_id' => Auth::user()->branch_id
        ]);

        // Get current academic year for the authenticated user's company
        $academicYear = AcademicYear::where('company_id', $companyId)
            ->where('is_current', true)
            ->first();

        if (!$academicYear) {
            \Log::info('getStudents: No current academic year found for company', [
                'company_id' => $companyId
            ]);
            return response()->json([]);
        }

        \Log::info('getStudents called', [
            'class_id' => $classId,
            'academic_year_id' => $academicYear->id,
            'user_id' => Auth::id(),
            'user_company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $query = Student::where('class_id', $classId)
            ->where('academic_year_id', $academicYear->id)
            ->where('status', 'active')
            ->where('company_id', $companyId);

        if ($branchId) {
            $query->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });
        }

        \Log::info('getStudents - query built', [
            'query_sql' => $query->toSql(),
            'query_bindings' => $query->getBindings()
        ]);

        $students = $query->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'admission_number', 'branch_id'])
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name . ' (' . $student->admission_number . ')',
                ];
            });

        \Log::info('getStudents result', [
            'student_count' => $students->count(),
            'first_few_students' => $students->take(3)->toArray()
        ]);

        return response()->json($students);
    }
}
