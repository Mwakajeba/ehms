<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\Student;
use App\Models\School\Classe;
use App\Models\School\Stream;
use App\Models\School\AcademicYear;
use App\Models\School\StudentFeeOpeningBalance;
use App\Models\School\StudentPrepaidAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class PromoteStudentsController extends Controller
{
    /**
     * Display a listing of students available for promotion.
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get filter options
        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->orderBy('year_name', 'desc')
            ->get();

        $currentAcademicYear = AcademicYear::current();

        return view('school.promote-students.index', compact('classes', 'academicYears', 'currentAcademicYear'));
    }

    /**
     * Get students for promotion based on filters.
     */
    public function getStudents(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Student::with(['class', 'stream', 'academicYear'])
            ->where('status', 'active')
            ->where('company_id', $companyId);

        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
            });
        }

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

        $students = $query->orderBy('admission_number')->get();

        return response()->json([
            'students' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'encoded_id' => Hashids::encode($student->id),
                    'admission_number' => $student->admission_number,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'full_name' => $student->first_name . ' ' . $student->last_name,
                    'class' => $student->class ? $student->class->name : 'N/A',
                    'stream' => $student->stream ? $student->stream->name : 'N/A',
                    'academic_year' => $student->academicYear ? $student->academicYear->year_name : 'N/A',
                ];
            })
        ]);
    }

    /**
     * Show the form for promoting students.
     */
    public function create(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get filter options
        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->orderBy('year_name', 'desc')
            ->get();

        $currentAcademicYear = AcademicYear::current();

        // Get students based on filters
        $students = [];
        if ($request->filled('class_id') || $request->filled('stream_id') || $request->filled('academic_year_id')) {
            $query = Student::with(['class', 'stream', 'academicYear'])
                ->where('status', 'active')
                ->where('company_id', $companyId);

            if ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            }

            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            if ($request->filled('stream_id')) {
                $query->where('stream_id', $request->stream_id);
            }

            if ($request->filled('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            $students = $query->orderBy('admission_number')->get();
        }

        return view('school.promote-students.create', compact('classes', 'academicYears', 'currentAcademicYear', 'students'));
    }

    /**
     * Store promotion records.
     */
    public function store(Request $request)
    {
        $request->validate([
            'students' => 'required|array|min:1',
            'students.*' => 'exists:students,id',
            'new_class_id' => 'required|exists:classes,id',
            'new_stream_id' => 'nullable|exists:streams,id',
            'new_academic_year_id' => 'required|exists:academic_years,id',
            'promotion_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $promotedCount = 0;
            $errors = [];

            foreach ($request->students as $studentId) {
                try {
                    $student = Student::findOrFail($studentId);

                    // Check if student belongs to user's company/branch
                    if ($student->company_id != Auth::user()->company_id) {
                        $errors[] = "Student {$student->admission_number} does not belong to your company.";
                        continue;
                    }

                    $branchId = session('branch_id') ?: Auth::user()->branch_id;
                    if ($branchId && $student->branch_id != $branchId && $student->branch_id !== null) {
                        $errors[] = "Student {$student->admission_number} does not belong to your branch.";
                        continue;
                    }

                    // Get old class and stream for logging
                    $oldClass = $student->class ? $student->class->name : 'N/A';
                    $oldStream = $student->stream ? $student->stream->name : 'N/A';
                    $newClass = Classe::find($request->new_class_id);
                    $newStream = $request->new_stream_id ? Stream::find($request->new_stream_id) : null;

                    // Update student
                    $student->update([
                        'class_id' => $request->new_class_id,
                        'stream_id' => $request->new_stream_id,
                        'academic_year_id' => $request->new_academic_year_id,
                    ]);

                    // Log promotion in status notes
                    $promotionNote = "Promoted from {$oldClass}" . ($oldStream != 'N/A' ? " ({$oldStream})" : '') . 
                                    " to {$newClass->name}" . ($newStream ? " ({$newStream->name})" : '') . 
                                    " on {$request->promotion_date}";
                    
                    if ($request->notes) {
                        $promotionNote .= "\nNotes: {$request->notes}";
                    }

                    $student->updateStatus('active', $promotionNote);

                    $promotedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to promote student ID {$studentId}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Successfully promoted {$promotedCount} student(s).";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " error(s) occurred.";
                return redirect()->back()
                    ->with('success', $message)
                    ->with('errors', $errors)
                    ->withInput();
            }

            return redirect()->route('school.promote-students.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to promote students: ' . $e->getMessage());
        }
    }

    /**
     * Show bulk promotion selection page.
     */
    public function bulkSelect(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get filter options
        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->orderBy('year_name', 'desc')
            ->get();

        $currentAcademicYear = AcademicYear::current();

        // Get students based on filters if provided
        $students = collect();
        $fromClass = null;
        $fromStream = null;
        $fromAcademicYear = null;
        $toClass = null;
        $toStream = null;
        $toAcademicYear = null;

        // Decode hash IDs from request
        $fromClassId = null;
        $fromStreamId = null;
        $fromAcademicYearId = null;
        $toClassId = null;
        $toStreamId = null;
        $toAcademicYearId = null;

        if ($request->filled('from_class_id')) {
            try {
                $decoded = Hashids::decode($request->from_class_id);
                $fromClassId = $decoded[0] ?? null;
            } catch (\Exception $e) {
                // If decode fails, try as regular ID
                $fromClassId = $request->from_class_id;
            }
        }

        if ($request->filled('from_stream_id')) {
            try {
                $decoded = Hashids::decode($request->from_stream_id);
                $fromStreamId = $decoded[0] ?? null;
            } catch (\Exception $e) {
                $fromStreamId = $request->from_stream_id;
            }
        }

        if ($request->filled('from_academic_year_id')) {
            try {
                $decoded = Hashids::decode($request->from_academic_year_id);
                $fromAcademicYearId = $decoded[0] ?? null;
            } catch (\Exception $e) {
                $fromAcademicYearId = $request->from_academic_year_id;
            }
        }

        if ($request->filled('to_class_id')) {
            try {
                $decoded = Hashids::decode($request->to_class_id);
                $toClassId = $decoded[0] ?? null;
            } catch (\Exception $e) {
                $toClassId = $request->to_class_id;
            }
        }

        if ($request->filled('to_stream_id')) {
            try {
                $decoded = Hashids::decode($request->to_stream_id);
                $toStreamId = $decoded[0] ?? null;
            } catch (\Exception $e) {
                $toStreamId = $request->to_stream_id;
            }
        }

        if ($request->filled('to_academic_year_id')) {
            try {
                $decoded = Hashids::decode($request->to_academic_year_id);
                $toAcademicYearId = $decoded[0] ?? null;
            } catch (\Exception $e) {
                $toAcademicYearId = $request->to_academic_year_id;
            }
        }

        if ($fromClassId && $fromAcademicYearId) {
            $fromClass = Classe::find($fromClassId);
            $fromStream = $fromStreamId ? Stream::find($fromStreamId) : null;
            $fromAcademicYear = AcademicYear::find($fromAcademicYearId);
            $toClass = $toClassId ? Classe::find($toClassId) : null;
            $toStream = $toStreamId ? Stream::find($toStreamId) : null;
            $toAcademicYear = $toAcademicYearId ? AcademicYear::find($toAcademicYearId) : null;

            $query = Student::with(['class', 'stream', 'academicYear'])
                ->where('status', 'active')
                ->where('company_id', $companyId)
                ->where('class_id', $fromClassId)
                ->where('academic_year_id', $fromAcademicYearId);

            if ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            }

            if ($fromStreamId) {
                $query->where('stream_id', $fromStreamId);
            }

            $students = $query->orderBy('admission_number')->get();

            // Check for already promoted students and opening balances
            foreach ($students as $student) {
                // Check if already promoted to target class/academic year
                if ($toClass && $toAcademicYear) {
                    $student->already_promoted = ($student->class_id == $toClass->id && 
                                                   $student->academic_year_id == $toAcademicYear->id);
                } else {
                    $student->already_promoted = false;
                }

                // Get opening balance (sum of all unpaid balances for the academic year)
                // 1. Get opening balances from student_fee_opening_balances table
                $openingBalance = StudentFeeOpeningBalance::where('student_id', $student->id)
                    ->where('academic_year_id', $fromAcademicYearId)
                    ->where('balance_due', '>', 0)
                    ->sum('balance_due');
                
                // 2. Get unpaid invoices for the academic year
                $unpaidInvoices = \App\Models\FeeInvoice::where('student_id', $student->id)
                    ->where('academic_year_id', $fromAcademicYearId)
                    ->where('status', '!=', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->get();
                
                // Calculate outstanding amount from unpaid invoices
                $unpaidInvoiceBalance = 0;
                foreach ($unpaidInvoices as $invoice) {
                    $outstanding = $invoice->total_amount - ($invoice->paid_amount ?? 0);
                    if ($outstanding > 0) {
                        $unpaidInvoiceBalance += $outstanding;
                    }
                }
                
                // Total opening balance = opening balances + unpaid invoice balances
                $student->opening_balance_due = $openingBalance + $unpaidInvoiceBalance;
            }
        }

        return view('school.promote-students.bulk-select', compact(
            'classes', 
            'academicYears', 
            'currentAcademicYear',
            'students',
            'fromClass',
            'fromStream',
            'fromAcademicYear',
            'toClass',
            'toStream',
            'toAcademicYear'
        ));
    }

    /**
     * Bulk promote students from one class to another.
     */
    public function bulkPromote(Request $request)
    {
        // Decode hash IDs before validation
        $fromClassId = null;
        $toClassId = null;
        $fromAcademicYearId = null;
        $toAcademicYearId = null;
        $fromStreamId = null;
        $toStreamId = null;

        try {
            $decoded = Hashids::decode($request->from_class_id);
            $fromClassId = $decoded[0] ?? null;
        } catch (\Exception $e) {
            $fromClassId = $request->from_class_id;
        }

        try {
            $decoded = Hashids::decode($request->to_class_id);
            $toClassId = $decoded[0] ?? null;
        } catch (\Exception $e) {
            $toClassId = $request->to_class_id;
        }

        try {
            $decoded = Hashids::decode($request->from_academic_year_id);
            $fromAcademicYearId = $decoded[0] ?? null;
        } catch (\Exception $e) {
            $fromAcademicYearId = $request->from_academic_year_id;
        }

        try {
            $decoded = Hashids::decode($request->to_academic_year_id);
            $toAcademicYearId = $decoded[0] ?? null;
        } catch (\Exception $e) {
            $toAcademicYearId = $request->to_academic_year_id;
        }

        if ($request->filled('from_stream_id')) {
            try {
                $decoded = Hashids::decode($request->from_stream_id);
                $fromStreamId = $decoded[0] ?? null;
            } catch (\Exception $e) {
                $fromStreamId = $request->from_stream_id;
            }
        }

        if ($request->filled('to_stream_id')) {
            try {
                $decoded = Hashids::decode($request->to_stream_id);
                $toStreamId = $decoded[0] ?? null;
            } catch (\Exception $e) {
                $toStreamId = $request->to_stream_id;
            }
        }

        // Validate with decoded IDs
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'promotion_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Additional validation for decoded IDs
        if (!$fromClassId || !Classe::find($fromClassId)) {
            return redirect()->back()
                ->with('error', 'Invalid from class selected.')
                ->withInput();
        }

        if (!$toClassId || !Classe::find($toClassId)) {
            return redirect()->back()
                ->with('error', 'Invalid to class selected.')
                ->withInput();
        }

        if (!$fromAcademicYearId || !AcademicYear::find($fromAcademicYearId)) {
            return redirect()->back()
                ->with('error', 'Invalid from academic year selected.')
                ->withInput();
        }

        if (!$toAcademicYearId || !AcademicYear::find($toAcademicYearId)) {
            return redirect()->back()
                ->with('error', 'Invalid to academic year selected.')
                ->withInput();
        }

        if ($fromStreamId && !Stream::find($fromStreamId)) {
            return redirect()->back()
                ->with('error', 'Invalid from stream selected.')
                ->withInput();
        }

        if ($toStreamId && !Stream::find($toStreamId)) {
            return redirect()->back()
                ->with('error', 'Invalid to stream selected.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            $students = Student::whereIn('id', $request->student_ids)
                ->where('status', 'active')
                ->where('company_id', $companyId)
                ->get();

            if ($students->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'No valid students selected for promotion.')
                    ->withInput();
            }

            // Use the already decoded IDs from validation
            $fromClass = Classe::find($fromClassId);
            $fromStream = $fromStreamId ? Stream::find($fromStreamId) : null;
            $fromAcademicYear = AcademicYear::find($fromAcademicYearId);
            $toClass = Classe::find($toClassId);
            $toStream = $toStreamId ? Stream::find($toStreamId) : null;
            $toAcademicYear = AcademicYear::find($toAcademicYearId);

            $promotedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($students as $student) {
                // Check if student is already promoted to target class/academic year
                if ($student->class_id == $toClassId && 
                    $student->academic_year_id == $toAcademicYearId) {
                    $skippedCount++;
                    $errors[] = "Student {$student->admission_number} ({$student->first_name} {$student->last_name}) is already in {$toClass->name} for {$toAcademicYear->year_name}.";
                    continue;
                }

                // Check if student belongs to user's company/branch
                if ($student->company_id != $companyId) {
                    $skippedCount++;
                    $errors[] = "Student {$student->admission_number} does not belong to your company.";
                    continue;
                }

                if ($branchId && $student->branch_id != $branchId && $student->branch_id !== null) {
                    $skippedCount++;
                    $errors[] = "Student {$student->admission_number} does not belong to your branch.";
                    continue;
                }

                $oldClass = $student->class ? $student->class->name : 'N/A';
                $oldStream = $student->stream ? $student->stream->name : 'N/A';

                // Update student
                $student->update([
                    'class_id' => $toClassId,
                    'stream_id' => $toStreamId,
                    'academic_year_id' => $toAcademicYearId,
                ]);

                // Carry forward opening balances
                $openingBalances = StudentFeeOpeningBalance::with('feeGroup')
                    ->where('student_id', $student->id)
                    ->where('academic_year_id', $fromAcademicYearId)
                    ->where('balance_due', '>', 0)
                    ->get();

                foreach ($openingBalances as $openingBalance) {
                    // Check if opening balance already exists for new academic year
                    $existingBalance = StudentFeeOpeningBalance::where('student_id', $student->id)
                        ->where('academic_year_id', $toAcademicYearId)
                        ->where('fee_group_id', $openingBalance->fee_group_id)
                        ->first();

                    $carryForwardAmount = $openingBalance->balance_due;
                    $feeGroup = $openingBalance->feeGroup;
                    
                    // Skip if fee group is missing
                    if (!$feeGroup) {
                        $errors[] = "Student {$student->admission_number}: Opening balance has no fee group associated. Skipping opening balance carry forward.";
                        \Log::warning('Opening balance missing fee group during promotion', [
                            'student_id' => $student->id,
                            'opening_balance_id' => $openingBalance->id
                        ]);
                        continue;
                    }

                        if ($existingBalance) {
                        // Update existing balance
                        $existingBalance->update([
                            'amount' => $existingBalance->amount + $carryForwardAmount,
                            'balance_due' => $existingBalance->balance_due + $carryForwardAmount,
                            'notes' => ($existingBalance->notes ?? '') . "\nCarried forward from {$fromClass->name} ({$fromAcademicYearId}) on {$request->promotion_date}",
                        ]);
                    } else {
                        // Get or create LIPISHA control number if LIPISHA is enabled
                        $controlNumber = null;
                        if (\App\Services\LipishaService::isEnabled()) {
                            try {
                                \Log::info('🔍 Attempting to get LIPISHA control number for carried forward opening balance', [
                                    'student_id' => $student->id,
                                    'amount' => $carryForwardAmount,
                                    'academic_year_id' => $toAcademicYearId
                                ]);
                                
                                $controlNumber = \App\Services\LipishaService::getControlNumberForInvoice(
                                    $student,
                                    $carryForwardAmount,
                                    null, // No period for opening balance
                                    $toAcademicYearId,
                                    null, // No invoice number for opening balance
                                    "Carried Forward Opening Balance - {$student->admission_number} ({$student->first_name} {$student->last_name})"
                                );
                                
                                \Log::info('🔍 LIPISHA control number result for carried forward opening balance', [
                                    'student_id' => $student->id,
                                    'control_number' => $controlNumber,
                                ]);
                            } catch (\Exception $e) {
                                \Log::error('❌ Failed to get LIPISHA control number for carried forward opening balance', [
                                    'student_id' => $student->id,
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                                // Continue without control number - don't fail the promotion
                            }
                        }
                        
                        // Create new opening balance for new academic year
                        $newOpeningBalance = StudentFeeOpeningBalance::create([
                            'student_id' => $student->id,
                            'academic_year_id' => $toAcademicYearId,
                            'fee_group_id' => $openingBalance->fee_group_id,
                            'opening_date' => $request->promotion_date,
                            'amount' => $carryForwardAmount,
                            'paid_amount' => 0,
                            'balance_due' => $carryForwardAmount,
                            'status' => 'posted',
                            'notes' => "Carried forward from {$fromClass->name} ({$fromAcademicYear->year_name}) on {$request->promotion_date}",
                            'lipisha_control_number' => $controlNumber,
                            'company_id' => $companyId,
                            'branch_id' => $branchId,
                            'created_by' => Auth::id(),
                        ]);

                        // Create GL transactions for carried forward opening balance
                        if ($feeGroup && $feeGroup->receivable_account_id && $feeGroup->opening_balance_account_id) {
                            $userId = Auth::id();
                            $description = "Carried Forward Opening Balance - {$student->admission_number} ({$student->first_name} {$student->last_name})";

                            // 1. Debit: Receivable Account
                            \App\Models\GlTransaction::create([
                                'chart_account_id' => $feeGroup->receivable_account_id,
                                'customer_id' => null,
                                'supplier_id' => null,
                                'amount' => $carryForwardAmount,
                                'nature' => 'debit',
                                'transaction_id' => $newOpeningBalance->id,
                                'transaction_type' => 'student_fee_opening_balance',
                                'date' => $request->promotion_date,
                                'description' => $description,
                                'branch_id' => $branchId,
                                'user_id' => $userId,
                            ]);

                            // 2. Credit: Opening Balance Account
                            \App\Models\GlTransaction::create([
                                'chart_account_id' => $feeGroup->opening_balance_account_id,
                                'customer_id' => null,
                                'supplier_id' => null,
                                'amount' => $carryForwardAmount,
                                'nature' => 'credit',
                                'transaction_id' => $newOpeningBalance->id,
                                'transaction_type' => 'student_fee_opening_balance',
                                'date' => $request->promotion_date,
                                'description' => $description,
                                'branch_id' => $branchId,
                                'user_id' => $userId,
                            ]);
                        }
                    }
                }

                // Convert unpaid invoices to opening balances
                $unpaidInvoices = \App\Models\FeeInvoice::where('student_id', $student->id)
                    ->where('academic_year_id', $fromAcademicYearId)
                    ->where('status', '!=', 'paid')
                    ->where('status', '!=', 'cancelled')
                    ->with('feeGroup')
                    ->get();

                // Group unpaid invoices by fee_group_id and calculate outstanding amounts
                $unpaidInvoicesByFeeGroup = [];
                foreach ($unpaidInvoices as $invoice) {
                    $outstanding = $invoice->total_amount - ($invoice->paid_amount ?? 0);
                    if ($outstanding > 0) {
                        if (!$invoice->feeGroup) {
                            $errors[] = "Student {$student->admission_number}: Invoice {$invoice->invoice_number} has no fee group. Skipping conversion to opening balance.";
                            \Log::warning('Invoice missing fee group during promotion', [
                                'student_id' => $student->id,
                                'invoice_id' => $invoice->id,
                                'invoice_number' => $invoice->invoice_number
                            ]);
                            continue;
                        }
                        
                        $feeGroupId = $invoice->fee_group_id;
                        if (!isset($unpaidInvoicesByFeeGroup[$feeGroupId])) {
                            $unpaidInvoicesByFeeGroup[$feeGroupId] = [
                                'fee_group' => $invoice->feeGroup,
                                'total_outstanding' => 0,
                                'invoices' => []
                            ];
                        }
                        $unpaidInvoicesByFeeGroup[$feeGroupId]['total_outstanding'] += $outstanding;
                        $unpaidInvoicesByFeeGroup[$feeGroupId]['invoices'][] = [
                            'invoice_number' => $invoice->invoice_number,
                            'outstanding' => $outstanding
                        ];
                    }
                }

                // Create opening balances for unpaid invoices
                foreach ($unpaidInvoicesByFeeGroup as $feeGroupId => $data) {
                    $feeGroup = $data['fee_group'];
                    $totalOutstanding = $data['total_outstanding'];
                    $invoiceNumbers = collect($data['invoices'])->pluck('invoice_number')->join(', ');

                    // Check if opening balance already exists for this fee group
                    $existingBalance = StudentFeeOpeningBalance::where('student_id', $student->id)
                        ->where('academic_year_id', $toAcademicYearId)
                        ->where('fee_group_id', $feeGroupId)
                        ->first();

                    if ($existingBalance) {
                        // Update existing balance
                        $existingBalance->update([
                            'amount' => $existingBalance->amount + $totalOutstanding,
                            'balance_due' => $existingBalance->balance_due + $totalOutstanding,
                            'notes' => ($existingBalance->notes ?? '') . "\nUnpaid invoices converted: {$invoiceNumbers} (from {$fromClass->name}, {$fromAcademicYear->year_name}) on {$request->promotion_date}",
                        ]);
                    } else {
                        // Get or create LIPISHA control number if LIPISHA is enabled
                        $controlNumber = null;
                        if (\App\Services\LipishaService::isEnabled()) {
                            try {
                                $controlNumber = \App\Services\LipishaService::getControlNumberForInvoice(
                                    $student,
                                    $totalOutstanding,
                                    null, // No period for opening balance
                                    $toAcademicYearId,
                                    null, // No invoice number for opening balance
                                    "Unpaid Invoices Opening Balance - {$student->admission_number} ({$student->first_name} {$student->last_name})"
                                );
                            } catch (\Exception $e) {
                                \Log::error('❌ Failed to get LIPISHA control number for unpaid invoices opening balance', [
                                    'student_id' => $student->id,
                                    'error' => $e->getMessage(),
                                ]);
                                // Continue without control number - don't fail the promotion
                            }
                        }

                        // Create new opening balance for unpaid invoices
                        $newOpeningBalance = StudentFeeOpeningBalance::create([
                            'student_id' => $student->id,
                            'academic_year_id' => $toAcademicYearId,
                            'fee_group_id' => $feeGroupId,
                            'opening_date' => $request->promotion_date,
                            'amount' => $totalOutstanding,
                            'paid_amount' => 0,
                            'balance_due' => $totalOutstanding,
                            'status' => 'posted',
                            'notes' => "Unpaid invoices converted from {$fromClass->name} ({$fromAcademicYear->year_name}) on {$request->promotion_date}. Invoices: {$invoiceNumbers}",
                            'lipisha_control_number' => $controlNumber,
                            'company_id' => $companyId,
                            'branch_id' => $branchId,
                            'created_by' => Auth::id(),
                        ]);

                        // Create GL transactions for unpaid invoices opening balance
                        if ($feeGroup && $feeGroup->receivable_account_id && $feeGroup->opening_balance_account_id) {
                            $userId = Auth::id();
                            $description = "Unpaid Invoices Opening Balance - {$student->admission_number} ({$student->first_name} {$student->last_name}) - Invoices: {$invoiceNumbers}";

                            // 1. Debit: Receivable Account
                            \App\Models\GlTransaction::create([
                                'chart_account_id' => $feeGroup->receivable_account_id,
                                'customer_id' => null,
                                'supplier_id' => null,
                                'amount' => $totalOutstanding,
                                'nature' => 'debit',
                                'transaction_id' => $newOpeningBalance->id,
                                'transaction_type' => 'student_fee_opening_balance',
                                'date' => $request->promotion_date,
                                'description' => $description,
                                'branch_id' => $branchId,
                                'user_id' => $userId,
                            ]);

                            // 2. Credit: Opening Balance Account
                            \App\Models\GlTransaction::create([
                                'chart_account_id' => $feeGroup->opening_balance_account_id,
                                'customer_id' => null,
                                'supplier_id' => null,
                                'amount' => $totalOutstanding,
                                'nature' => 'credit',
                                'transaction_id' => $newOpeningBalance->id,
                                'transaction_type' => 'student_fee_opening_balance',
                                'date' => $request->promotion_date,
                                'description' => $description,
                                'branch_id' => $branchId,
                                'user_id' => $userId,
                            ]);
                        }
                    }
                }

                // Handle prepaid account - balance carries forward to new academic year
                // Prepaid accounts are per student (not per academic year), so balance remains available
                $prepaidAccount = StudentPrepaidAccount::where('student_id', $student->id)->first();
                if ($prepaidAccount) {
                    // Refresh to get latest balance
                    $prepaidAccount->refresh();
                    
                    if ($prepaidAccount->credit_balance > 0) {
                        // Log promotion in transaction history (using notes field since we can't modify type enum)
                        // We'll create a zero-amount transaction just for tracking purposes
                        // Note: This won't affect the balance since amount is 0
                        try {
                            $prepaidAccount->transactions()->create([
                                'type' => 'deposit', // Using deposit type, but amount is 0 so balance doesn't change
                                'amount' => 0,
                                'balance_before' => $prepaidAccount->credit_balance,
                                'balance_after' => $prepaidAccount->credit_balance,
                                'reference' => 'PROMOTION-' . $toAcademicYear->year_name,
                                'notes' => "Student promoted from {$oldClass} to {$toClass->name} (Academic Year: {$toAcademicYear->year_name}) on {$request->promotion_date}. Prepaid balance of " . number_format($prepaidAccount->credit_balance, 2) . " " . config('app.currency', 'TZS') . " carried forward to new academic year.",
                                'created_by' => Auth::id(),
                            ]);
                        } catch (\Exception $e) {
                            // If transaction creation fails, just log it - don't fail the promotion
                            \Log::warning('Failed to create promotion transaction for prepaid account', [
                                'student_id' => $student->id,
                                'prepaid_account_id' => $prepaidAccount->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                        
                        // Try to automatically apply prepaid credit to any unpaid invoices in the new academic year
                        $autoApplyResult = $prepaidAccount->autoApplyCreditToUnpaidInvoices();
                        
                        \Log::info('Prepaid account handled during promotion', [
                            'student_id' => $student->id,
                            'student_name' => "{$student->first_name} {$student->last_name}",
                            'from_class' => $oldClass,
                            'to_class' => $toClass->name,
                            'from_academic_year' => $request->from_academic_year_id,
                            'to_academic_year' => $toAcademicYear->year_name,
                            'prepaid_balance' => $prepaidAccount->credit_balance,
                            'auto_applied' => $autoApplyResult['applied'],
                            'auto_applied_amount' => $autoApplyResult['total_applied'],
                            'auto_applied_invoices' => $autoApplyResult['invoices_paid']
                        ]);
                    }
                }

                $promotionNote = "Bulk promoted from {$oldClass}" . ($oldStream != 'N/A' ? " ({$oldStream})" : '') . 
                                " to {$toClass->name}" . ($toStream ? " ({$toStream->name})" : '') . 
                                " on {$request->promotion_date}";
                
                if ($request->notes) {
                    $promotionNote .= "\nNotes: {$request->notes}";
                }

                $student->updateStatus('active', $promotionNote);
                $promotedCount++;
            }

            DB::commit();

            $message = "Successfully promoted {$promotedCount} student(s) from {$fromClass->name} to {$toClass->name}.";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} student(s) were skipped.";
            }

            if (!empty($errors)) {
                return redirect()->back()
                    ->with('success', $message)
                    ->with('errors', $errors)
                    ->withInput();
            }

            return redirect()->route('school.promote-students.index')
                ->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk promote students error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->except(['_token', 'password']),
                'user_id' => Auth::id()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to promote students: ' . $e->getMessage());
        }
    }
}

