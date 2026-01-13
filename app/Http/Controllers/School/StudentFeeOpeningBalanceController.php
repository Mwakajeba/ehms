<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\StudentFeeOpeningBalance;
use App\Models\School\Student;
use App\Models\School\AcademicYear;
use App\Models\School\Classe;
use App\Models\School\Stream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;

class StudentFeeOpeningBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->orderBy('year_name', 'desc')
            ->get();

        $currentAcademicYear = AcademicYear::where('company_id', $companyId)
            ->where('is_current', true)
            ->first();

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        return view('school.student-fee-opening-balance.index', compact('academicYears', 'classes', 'currentAcademicYear'));
    }

    /**
     * Get data for DataTables.
     */
    public function data(Request $request)
    {
        try {
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            $query = StudentFeeOpeningBalance::with(['student.class', 'student.stream', 'academicYear', 'creator'])
                ->forCompany($companyId);

            if ($branchId) {
                $query->forBranch($branchId);
            }

            // Apply filters
            if ($request->filled('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            } else {
                // Default to current academic year if no filter is set
                $currentAcademicYear = AcademicYear::where('company_id', $companyId)
                    ->where('is_current', true)
                    ->first();
                if ($currentAcademicYear) {
                    $query->where('academic_year_id', $currentAcademicYear->id);
                }
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('class_id')) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('class_id', $request->class_id);
                });
            }

            if ($request->filled('stream_id')) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('stream_id', $request->stream_id);
                });
            }

            if ($request->filled('search') && $request->search['value']) {
                $search = $request->search['value'];
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('admission_number', 'like', "%{$search}%");
                });
            }

            return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('student_name', function ($openingBalance) {
                return $openingBalance->student 
                    ? $openingBalance->student->first_name . ' ' . $openingBalance->student->last_name 
                    : 'N/A';
            })
            ->addColumn('admission_number', function ($openingBalance) {
                return $openingBalance->student ? $openingBalance->student->admission_number : 'N/A';
            })
            ->addColumn('class', function ($openingBalance) {
                return $openingBalance->student && $openingBalance->student->class 
                    ? $openingBalance->student->class->name 
                    : 'N/A';
            })
            ->addColumn('stream', function ($openingBalance) {
                return $openingBalance->student && $openingBalance->student->stream 
                    ? $openingBalance->student->stream->name 
                    : 'N/A';
            })
            ->addColumn('academic_year', function ($openingBalance) {
                return $openingBalance->academicYear ? $openingBalance->academicYear->year_name : 'N/A';
            })
            ->editColumn('opening_date', function ($openingBalance) {
                if (!$openingBalance->opening_date) {
                    return 'N/A';
                }
                try {
                    if (is_string($openingBalance->opening_date)) {
                        return \Carbon\Carbon::parse($openingBalance->opening_date)->format('M d, Y');
                    }
                    return $openingBalance->opening_date->format('M d, Y');
                } catch (\Exception $e) {
                    return 'N/A';
                }
            })
            ->editColumn('amount', function ($openingBalance) {
                return number_format($openingBalance->amount, 2) . ' ' . (config('app.currency', 'TZS'));
            })
            ->editColumn('paid_amount', function ($openingBalance) {
                return number_format($openingBalance->paid_amount, 2) . ' ' . (config('app.currency', 'TZS'));
            })
            ->editColumn('balance_due', function ($openingBalance) {
                return '<strong class="text-danger">' . number_format($openingBalance->balance_due, 2) . ' ' . (config('app.currency', 'TZS')) . '</strong>';
            })
            ->addColumn('opening_balance', function ($openingBalance) {
                return number_format($openingBalance->amount, 2) . ' ' . (config('app.currency', 'TZS'));
            })
            ->addColumn('lipisha_control_number', function ($openingBalance) {
                if (!empty($openingBalance->lipisha_control_number)) {
                    return '<span class="badge bg-info">' . htmlspecialchars($openingBalance->lipisha_control_number) . '</span>';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('status_badge', function ($openingBalance) {
                $badges = [
                    'draft' => '<span class="badge bg-secondary">Draft</span>',
                    'posted' => '<span class="badge bg-success">Posted</span>',
                    'closed' => '<span class="badge bg-danger">Closed</span>',
                ];
                return $badges[$openingBalance->status] ?? '<span class="badge bg-secondary">' . ucfirst($openingBalance->status) . '</span>';
            })
            ->addColumn('actions', function ($openingBalance) {
                $actions = '<div class="btn-group" role="group">';
                $actions .= '<a href="' . route('school.student-fee-opening-balance.show', $openingBalance->getRouteKey()) . '" class="btn btn-sm btn-info" title="View"><i class="bx bx-show"></i></a>';
                // Allow editing for draft and posted status (not closed)
                if (in_array($openingBalance->status, ['draft', 'posted'])) {
                    $actions .= '<a href="' . route('school.student-fee-opening-balance.edit', $openingBalance->getRouteKey()) . '" class="btn btn-sm btn-primary" title="Edit"><i class="bx bx-edit"></i></a>';
                }
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['status_badge', 'actions', 'balance_due', 'lipisha_control_number'])
            ->make(true);
        } catch (\Exception $e) {
            \Log::error('StudentFeeOpeningBalance DataTables Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error loading data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $students = Student::where('status', 'active')
            ->where('company_id', $companyId)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->with(['class', 'stream'])
            ->orderBy('admission_number')
            ->get();

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->orderBy('year_name', 'desc')
            ->get();

        $currentAcademicYear = AcademicYear::current();

        $feeGroups = \App\Models\FeeGroup::where('company_id', $companyId)
            ->where('is_active', true)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->orderBy('name')
            ->get();

        return view('school.student-fee-opening-balance.create', compact('students', 'academicYears', 'currentAcademicYear', 'feeGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'fee_group_id' => 'required|exists:fee_groups,id',
            'opening_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Check if opening balance already exists for this student and academic year
        $existing = StudentFeeOpeningBalance::where('student_id', $request->student_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Opening balance already exists for this student and academic year.');
        }

        try {
            DB::beginTransaction();

            // Get fee group and student
            $feeGroup = \App\Models\FeeGroup::findOrFail($request->fee_group_id);
            $student = \App\Models\School\Student::findOrFail($request->student_id);

            // Validate that fee group has required accounts
            if (!$feeGroup->receivable_account_id) {
                throw new \Exception('Fee group does not have a receivable account configured.');
            }
            if (!$feeGroup->opening_balance_account_id) {
                throw new \Exception('Fee group does not have an opening balance account configured.');
            }

            // Get or create LIPISHA control number if LIPISHA is enabled
            $controlNumber = null;
            if (\App\Services\LipishaService::isEnabled()) {
                try {
                    \Log::info('🔍 Attempting to get LIPISHA control number for opening balance', [
                        'student_id' => $request->student_id,
                        'amount' => $request->amount,
                        'academic_year_id' => $request->academic_year_id
                    ]);
                    
                    $controlNumber = \App\Services\LipishaService::getControlNumberForInvoice(
                        $student,
                        $request->amount,
                        null, // No period for opening balance
                        $request->academic_year_id,
                        null, // No invoice number for opening balance
                        "Opening Balance - {$student->admission_number} ({$student->first_name} {$student->last_name})"
                    );
                    
                    \Log::info('🔍 LIPISHA control number result', [
                        'student_id' => $request->student_id,
                        'control_number' => $controlNumber,
                        'control_number_type' => gettype($controlNumber),
                        'control_number_empty' => empty($controlNumber),
                        'control_number_length' => $controlNumber ? strlen($controlNumber) : 0
                    ]);
                } catch (\Exception $e) {
                    \Log::error('❌ Failed to get LIPISHA control number for opening balance', [
                        'student_id' => $request->student_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continue without control number - opening balance can still be created
                }
            }

            $openingBalance = StudentFeeOpeningBalance::create([
                'student_id' => $request->student_id,
                'academic_year_id' => $request->academic_year_id,
                'fee_group_id' => $request->fee_group_id,
                'opening_date' => $request->opening_date,
                'amount' => $request->amount,
                'paid_amount' => 0,
                'balance_due' => $request->amount,
                'status' => 'posted', // Always posted when created
                'reference' => null, // Removed from form
                'lipisha_control_number' => $controlNumber,
                'notes' => $request->notes,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'created_by' => Auth::id(),
            ]);

            // Create GL transactions
            $userId = Auth::id();
            $description = "Student Opening Balance - {$student->admission_number} ({$student->first_name} {$student->last_name})";

            // 1. Debit: Receivable Account (student owes the fee)
            \App\Models\GlTransaction::create([
                'chart_account_id' => $feeGroup->receivable_account_id,
                'customer_id' => null,
                'supplier_id' => null,
                'amount' => $request->amount,
                'nature' => 'debit',
                'transaction_id' => $openingBalance->id,
                'transaction_type' => 'student_fee_opening_balance',
                'date' => $request->opening_date,
                'description' => $description,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);

            // 2. Credit: Opening Balance Account
            \App\Models\GlTransaction::create([
                'chart_account_id' => $feeGroup->opening_balance_account_id,
                'customer_id' => null,
                'supplier_id' => null,
                'amount' => $request->amount,
                'nature' => 'credit',
                'transaction_id' => $openingBalance->id,
                'transaction_type' => 'student_fee_opening_balance',
                'date' => $request->opening_date,
                'description' => $description,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);

            DB::commit();

            return redirect()->route('school.student-fee-opening-balance.index')
                ->with('success', 'Student opening balance created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create opening balance: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($encodedId)
    {
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($encodedId);
        $id = $decoded[0] ?? null;
        $openingBalance = StudentFeeOpeningBalance::findOrFail($id);
        $openingBalance->load(['student.class', 'student.stream', 'academicYear', 'creator', 'updater']);

        return view('school.student-fee-opening-balance.show', compact('openingBalance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($encodedId)
    {
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($encodedId);
        $id = $decoded[0] ?? null;
        $openingBalance = StudentFeeOpeningBalance::findOrFail($id);
        
        if ($openingBalance->status === 'closed') {
            return redirect()->route('school.student-fee-opening-balance.show', $openingBalance->getRouteKey())
                ->with('error', 'Closed opening balances cannot be edited.');
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->orderBy('year_name', 'desc')
            ->get();

        $feeGroups = \App\Models\FeeGroup::where('company_id', $companyId)
            ->where('is_active', true)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->orderBy('name')
            ->get();

        return view('school.student-fee-opening-balance.edit', compact('openingBalance', 'academicYears', 'feeGroups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $encodedId)
    {
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($encodedId);
        $id = $decoded[0] ?? null;
        $openingBalance = StudentFeeOpeningBalance::findOrFail($id);

        if ($openingBalance->status === 'closed') {
            return redirect()->route('school.student-fee-opening-balance.show', $openingBalance->getRouteKey())
                ->with('error', 'Closed opening balances cannot be edited.');
        }

        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'fee_group_id' => 'required|exists:fee_groups,id',
            'opening_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get fee group and student
            $feeGroup = \App\Models\FeeGroup::findOrFail($request->fee_group_id);
            $student = $openingBalance->student;

            // Validate that fee group has required accounts
            if (!$feeGroup->receivable_account_id) {
                throw new \Exception('Fee group does not have a receivable account configured.');
            }
            if (!$feeGroup->opening_balance_account_id) {
                throw new \Exception('Fee group does not have an opening balance account configured.');
            }

            // Delete old GL transactions if status is posted
            if ($openingBalance->status === 'posted') {
                \App\Models\GlTransaction::where('transaction_id', $openingBalance->id)
                    ->where('transaction_type', 'student_fee_opening_balance')
                    ->delete();
            }

            $openingBalance->update([
                'academic_year_id' => $request->academic_year_id,
                'fee_group_id' => $request->fee_group_id,
                'opening_date' => $request->opening_date,
                'amount' => $request->amount,
                'balance_due' => $request->amount - $openingBalance->paid_amount,
                'status' => 'posted', // Keep as posted
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            // Create new GL transactions
            $userId = Auth::id();
            $branchId = session('branch_id') ?: Auth::user()->branch_id;
            $description = "Student Opening Balance - {$student->admission_number} ({$student->first_name} {$student->last_name})";

            // 1. Debit: Receivable Account
            \App\Models\GlTransaction::create([
                'chart_account_id' => $feeGroup->receivable_account_id,
                'customer_id' => null,
                'supplier_id' => null,
                'amount' => $request->amount,
                'nature' => 'debit',
                'transaction_id' => $openingBalance->id,
                'transaction_type' => 'student_fee_opening_balance',
                'date' => $request->opening_date,
                'description' => $description,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);

            // 2. Credit: Opening Balance Account
            \App\Models\GlTransaction::create([
                'chart_account_id' => $feeGroup->opening_balance_account_id,
                'customer_id' => null,
                'supplier_id' => null,
                'amount' => $request->amount,
                'nature' => 'credit',
                'transaction_id' => $openingBalance->id,
                'transaction_type' => 'student_fee_opening_balance',
                'date' => $request->opening_date,
                'description' => $description,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);

            DB::commit();

            return redirect()->route('school.student-fee-opening-balance.index')
                ->with('success', 'Opening balance updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update opening balance: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($encodedId)
    {
        $decoded = \Vinkla\Hashids\Facades\Hashids::decode($encodedId);
        $id = $decoded[0] ?? null;
        $openingBalance = StudentFeeOpeningBalance::findOrFail($id);

        if ($openingBalance->status !== 'draft') {
            return redirect()->back()
                ->with('error', 'Only draft opening balances can be deleted.');
        }

        try {
            $openingBalance->delete();
            return redirect()->route('school.student-fee-opening-balance.index')
                ->with('success', 'Opening balance deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete opening balance: ' . $e->getMessage());
        }
    }

    /**
     * Show the import form.
     */
    public function import()
    {
        $companyId = Auth::user()->company_id;
        $academicYears = AcademicYear::where('company_id', $companyId)
            ->orderBy('year_name', 'desc')
            ->get();

        $currentAcademicYear = AcademicYear::current();

        return view('school.student-fee-opening-balance.import', compact('academicYears', 'currentAcademicYear'));
    }

    /**
     * Download import template.
     */
    public function downloadTemplate(Request $request)
    {
        $classId = $request->get('class_id');
        $streamId = $request->get('stream_id');
        $academicYearId = $request->get('academic_year_id');

        // Normalize stream_id - convert empty string to null for "All Streams"
        $streamId = !empty($streamId) ? $streamId : null;

        $class = Classe::findOrFail($classId);
        $stream = $streamId ? Stream::findOrFail($streamId) : null;
        $academicYear = AcademicYear::findOrFail($academicYearId);

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get students directly from students table
        // If stream_id is null (All Streams), get ALL students in the class
        // If stream_id is provided, filter by that specific stream
        $studentsQuery = Student::where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', $companyId);
        
        // Only filter by stream if a specific stream is selected (not "All Streams")
        if ($streamId) {
            $studentsQuery->where('stream_id', $streamId);
        }
        // If stream_id is null, we get ALL students in the class (no stream filter)
        
        $students = $studentsQuery
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->orderBy('admission_number')
            ->get();
            
        // If no students found with academic_year_id, try without it (some students might not have it set)
        if ($students->isEmpty()) {
            $studentsQuery2 = Student::where('class_id', $classId)
                ->where('company_id', $companyId);
            
            // Only filter by stream if a specific stream is selected (not "All Streams")
            if ($streamId) {
                $studentsQuery2->where('stream_id', $streamId);
            }
            // If stream_id is null, we get ALL students in the class (no stream filter)
            
            $students = $studentsQuery2
                ->when($branchId, function ($query) use ($branchId) {
                    $query->where(function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                    });
                })
                ->orderBy('admission_number')
                ->get();
        }

        // Log for debugging
        \Log::info('Student Opening Balance Template Download', [
            'class_id' => $classId,
            'stream_id' => $streamId ?: 'ALL_STREAMS',
            'academic_year_id' => $academicYearId,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'students_count' => $students->count(),
            'sample_student' => $students->first() ? [
                'id' => $students->first()->id,
                'admission_number' => $students->first()->admission_number,
                'name' => $students->first()->first_name . ' ' . $students->first()->last_name,
                'status' => $students->first()->status,
                'academic_year_id' => $students->first()->academic_year_id,
                'stream_id' => $students->first()->stream_id
            ] : null
        ]);
        
        if ($students->isEmpty()) {
            $totalInClass = Student::where('class_id', $classId)
                ->where('company_id', $companyId)
                ->when($branchId, function ($query) use ($branchId) {
                    $query->where(function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                    });
                })
                ->count();
                
            \Log::warning('No students found for template', [
                'class_id' => $classId,
                'stream_id' => $streamId ?: 'ALL_STREAMS',
                'academic_year_id' => $academicYearId,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'total_students_in_class' => $totalInClass,
                'total_students_in_class_with_academic_year' => Student::where('class_id', $classId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('company_id', $companyId)
                    ->when($branchId, function ($query) use ($branchId) {
                        $query->where(function ($q) use ($branchId) {
                            $q->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                        });
                    })
                    ->count()
            ]);
        }

        // Get fee groups
        $feeGroups = \App\Models\FeeGroup::where('company_id', $companyId)
            ->where('is_active', true)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->orderBy('name')
            ->get();

        $streamName = $stream ? $stream->name : 'All_Streams';
        $filename = "student_opening_balances_{$class->name}_{$streamName}_" . date('Y-m-d') . ".xlsx";

        return Excel::download(new class($students, $feeGroups, $academicYear) implements 
            \Maatwebsite\Excel\Concerns\FromArray, 
            \Maatwebsite\Excel\Concerns\WithHeadings, 
            \Maatwebsite\Excel\Concerns\WithEvents, 
            \Maatwebsite\Excel\Concerns\ShouldAutoSize 
        {
            private $students;
            private $feeGroups;
            private $academicYear;

            public function __construct($students, $feeGroups, $academicYear)
            {
                $this->students = $students;
                $this->feeGroups = $feeGroups;
                $this->academicYear = $academicYear;
            }

            public function array(): array
            {
                $data = [];
                
                if ($this->students->isEmpty()) {
                    // Return at least one empty row if no students found
                    $data[] = [
                        'admission_number' => '',
                        'student_name' => '',
                        'amount' => '',
                        'fee_group' => '',
                        'opening_date' => date('Y-m-d'),
                        'notes' => 'opening balance',
                    ];
                    return $data;
                }
                
                foreach ($this->students as $student) {
                    $data[] = [
                        'admission_number' => $student->admission_number ?? '',
                        'student_name' => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
                        'amount' => '',
                        'fee_group' => '',
                        'opening_date' => date('Y-m-d'),
                        'notes' => 'opening balance',
                    ];
                }
                
                return $data;
            }

            public function headings(): array
            {
                return [
                    'Admission Number',
                    'Student Name',
                    'Amount',
                    'Fee Group',
                    'Opening Date',
                    'Notes',
                ];
            }

            public function registerEvents(): array
            {
                return [
                    \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $workbook = $event->sheet->getDelegate()->getParent();

                        // Create dropdown data sheet
                        $dropdownSheet = $workbook->createSheet();
                        $dropdownSheet->setTitle('DropdownData');

                        // Fee Groups (column A)
                        $dropdownSheet->setCellValue('A1', 'Fee Group Options');
                        foreach ($this->feeGroups as $index => $feeGroup) {
                            $dropdownSheet->setCellValue('A' . ($index + 2), $feeGroup->fee_code . ' - ' . $feeGroup->name);
                        }

                        // Hide the dropdown data sheet
                        $dropdownSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

                        // Style header row first
                        $sheet->getStyle('A1:F1')->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '4472C4']
                            ],
                            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
                        ]);

                        // Create data validation for Fee Group column (D - after adding Student Name)
                        if (count($this->feeGroups) > 0) {
                            $feeGroupRange = 'DropdownData!$A$2:$A$' . (count($this->feeGroups) + 1);
                            
                            // Apply validation to range D2:D1000
                            $validation = $sheet->getDataValidation('D2:D1000');
                            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                            $validation->setAllowBlank(false);
                            $validation->setShowInputMessage(true);
                            $validation->setShowErrorMessage(true);
                            $validation->setShowDropDown(true);
                            $validation->setErrorTitle('Invalid Fee Group');
                            $validation->setError('Please select a fee group from the dropdown list.');
                            $validation->setPromptTitle('Select Fee Group');
                            $validation->setPrompt('Click the dropdown arrow to select a fee group.');
                            $validation->setFormula1($feeGroupRange);
                        }

                        // Auto-size columns
                        foreach (range('A', 'F') as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }
                        
                        // Freeze first row
                        $sheet->freezePane('A2');
                    }
                ];
            }
        }, $filename);
    }

    /**
     * Process the import.
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'nullable|exists:streams,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $class = Classe::where('company_id', $companyId)->findOrFail($request->class_id);
        $stream = $request->stream_id ? Stream::findOrFail($request->stream_id) : null;
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);

        try {
            $import = new \App\Imports\StudentOpeningBalanceImport($class, $stream, $academicYear, $companyId, $branchId);
            Excel::import($import, $request->file('excel_file'));

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            if ($successCount > 0) {
                $message = "Successfully imported {$successCount} opening balance(s).";
                if ($errorCount > 0) {
                    $message .= " {$errorCount} row(s) had errors.";
                    if (!empty($errors)) {
                        $message .= " Errors: " . implode('; ', array_slice($errors, 0, 5));
                        if (count($errors) > 5) {
                            $message .= " and " . (count($errors) - 5) . " more.";
                        }
                    }
                }
                return redirect()->route('school.student-fee-opening-balance.index')
                    ->with('success', $message);
            } else {
                $errorMessage = 'No opening balances were imported. ';
                if (!empty($errors)) {
                    $errorMessage .= "Errors: " . implode('; ', array_slice($errors, 0, 10));
                    if (count($errors) > 10) {
                        $errorMessage .= " and " . (count($errors) - 10) . " more. ";
                    }
                    $errorMessage .= "Please check your file format and data.";
                } else {
                    $errorMessage .= "Please check your file format and data. Make sure the Excel file has the correct columns: Admission Number, Student Name, Amount, Fee Group, Opening Date, Notes.";
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Export opening balances to PDF
     */
    public function exportPdf(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = StudentFeeOpeningBalance::with(['student.class', 'student.stream', 'academicYear', 'feeGroup'])
            ->forCompany($companyId);

        if ($branchId) {
            $query->forBranch($branchId);
        }

        // Apply filters
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        } else {
            $currentAcademicYear = AcademicYear::where('company_id', $companyId)
                ->where('is_current', true)
                ->first();
            if ($currentAcademicYear) {
                $query->where('academic_year_id', $currentAcademicYear->id);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('stream_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('stream_id', $request->stream_id);
            });
        }

        $openingBalances = $query->orderBy('opening_date', 'desc')->get();

        $company = \App\Models\Company::find($companyId);
        $branch = $branchId ? \App\Models\Branch::find($branchId) : null;
        $academicYear = $request->filled('academic_year_id') 
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::where('company_id', $companyId)->where('is_current', true)->first();
        $class = $request->filled('class_id') ? Classe::find($request->class_id) : null;
        $stream = $request->filled('stream_id') ? Stream::find($request->stream_id) : null;

        // Calculate totals
        $totalAmount = $openingBalances->sum('amount');
        $totalPaid = $openingBalances->sum('paid_amount');
        $totalBalance = $openingBalances->sum('balance_due');

        $generatedAt = now();

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('school.student-fee-opening-balance.pdf', compact(
                'openingBalances',
                'company',
                'branch',
                'academicYear',
                'class',
                'stream',
                'totalAmount',
                'totalPaid',
                'totalBalance',
                'generatedAt'
            ));

            $pdf->setPaper('A4', 'landscape');
            $filename = 'Student_Opening_Balance_' . date('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export opening balances to Excel
     */
    public function exportExcel(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = StudentFeeOpeningBalance::with(['student.class', 'student.stream', 'academicYear', 'feeGroup'])
            ->forCompany($companyId);

        if ($branchId) {
            $query->forBranch($branchId);
        }

        // Apply filters
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        } else {
            $currentAcademicYear = AcademicYear::where('company_id', $companyId)
                ->where('is_current', true)
                ->first();
            if ($currentAcademicYear) {
                $query->where('academic_year_id', $currentAcademicYear->id);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('stream_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('stream_id', $request->stream_id);
            });
        }

        $openingBalances = $query->orderBy('opening_date', 'desc')->get();

        $data = [];
        foreach ($openingBalances as $balance) {
            $data[] = [
                ($balance->student ? $balance->student->first_name . ' ' . $balance->student->last_name : 'N/A'),
                $balance->student ? $balance->student->admission_number : 'N/A',
                $balance->student && $balance->student->class ? $balance->student->class->name : 'N/A',
                $balance->student && $balance->student->stream ? $balance->student->stream->name : 'N/A',
                $balance->academicYear ? $balance->academicYear->year_name : 'N/A',
                $balance->opening_date ? \Carbon\Carbon::parse($balance->opening_date)->format('Y-m-d') : 'N/A',
                $balance->feeGroup ? $balance->feeGroup->name : 'N/A',
                number_format($balance->amount, 2),
                number_format($balance->paid_amount, 2),
                number_format($balance->balance_due, 2),
                ucfirst($balance->status),
                $balance->notes ?? ''
            ];
        }

        $filename = 'Student_Opening_Balance_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new class($data) implements 
            \Maatwebsite\Excel\Concerns\FromArray,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\ShouldAutoSize
        {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return [
                    'Student Name',
                    'Admission Number',
                    'Class',
                    'Stream',
                    'Academic Year',
                    'Opening Date',
                    'Fee Group',
                    'Amount',
                    'Paid Amount',
                    'Balance Due',
                    'Status',
                    'Notes'
                ];
            }
        }, $filename);
    }
}

