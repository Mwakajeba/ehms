<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\School\AcademicYear;
use App\Models\College\FeeInvoice;
use App\Models\College\FeeInvoiceItem;
use App\Models\College\FeeSetting;
use App\Models\College\FeeSettingItem;
use App\Models\College\Program;
use App\Models\College\Student;
use App\Models\FeeGroup;
use App\Models\GlTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class FeeInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $programs = Program::where('company_id', $companyId)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $feeGroups = FeeGroup::active()
            ->orderBy('name')
            ->get();

        // Calculate statistics directly
        $query = FeeInvoice::forCompany($companyId);

        if ($branchId) {
            $query->forBranch($branchId);
        }

        // Total invoices count
        $totalInvoices = $query->count();

        // Paid invoices count
        $paidInvoices = (clone $query)->where('status', 'paid')->count();

        // Pending invoices count (not paid and not cancelled)
        $pendingInvoices = (clone $query)->where('status', '!=', 'paid')
                                         ->where('status', '!=', 'cancelled')
                                         ->count();

        // Total amount due (unpaid amounts)
        $totalAmount = $query->sum('total_amount');
        $paidAmount = $query->sum('paid_amount');
        $amountDue = $totalAmount - $paidAmount;

        // Overdue invoices count
        $overdueInvoices = (clone $query)->where('due_date', '<', now())
                                        ->where('status', '!=', 'paid')
                                        ->count();

        $statistics = [
            'total_invoices' => $totalInvoices,
            'paid_invoices' => $paidInvoices,
            'pending_invoices' => $pendingInvoices,
            'amount_due' => $amountDue,
            'overdue_invoices' => $overdueInvoices,
            'currency' => config('app.currency', 'TZS')
        ];

        return view('college.fee-invoices.index', compact('programs', 'feeGroups', 'statistics'));
    }

    /**
     * Get fee invoice statistics for dashboard.
     */
    public function statistics(Request $request)
    {
        $query = FeeInvoice::forCompany(Auth::user()->company_id);

        if (Auth::user()->branch_id) {
            $query->forBranch(Auth::user()->branch_id);
        }

        // Apply filters if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('program_id') && $request->program_id) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->has('fee_group_id') && $request->fee_group_id) {
            $query->where('fee_group_id', $request->fee_group_id);
        }

        $totalInvoices = $query->count();
        $totalAmount = $query->sum('total_amount');
        $paidAmount = $query->sum('paid_amount');
        $pendingAmount = $totalAmount - $paidAmount;

        // Status counts
        $statusCounts = FeeInvoice::forCompany(Auth::user()->company_id)
            ->when(Auth::user()->branch_id, function ($query) {
                $query->forBranch(Auth::user()->branch_id);
            })
            ->when($request->has('program_id') && $request->program_id, function ($query) use ($request) {
                $query->where('program_id', $request->program_id);
            })
            ->when($request->has('fee_group_id') && $request->fee_group_id, function ($query) use ($request) {
                $query->where('fee_group_id', $request->fee_group_id);
            })
            ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Overdue invoices
        $overdueInvoices = FeeInvoice::forCompany(Auth::user()->company_id)
            ->when(Auth::user()->branch_id, function ($query) {
                $query->forBranch(Auth::user()->branch_id);
            })
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->when($request->has('program_id') && $request->program_id, function ($query) use ($request) {
                $query->where('program_id', $request->program_id);
            })
            ->when($request->has('fee_group_id') && $request->fee_group_id, function ($query) use ($request) {
                $query->where('fee_group_id', $request->fee_group_id);
            })
            ->count();

        $overdueAmount = FeeInvoice::forCompany(Auth::user()->company_id)
            ->when(Auth::user()->branch_id, function ($query) {
                $query->forBranch(Auth::user()->branch_id);
            })
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->when($request->has('program_id') && $request->program_id, function ($query) use ($request) {
                $query->where('program_id', $request->program_id);
            })
            ->when($request->has('fee_group_id') && $request->fee_group_id, function ($query) use ($request) {
                $query->where('fee_group_id', $request->fee_group_id);
            })
            ->sum('total_amount');

        return response()->json([
            'total_invoices' => $totalInvoices,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'pending_amount' => $pendingAmount,
            'draft_count' => $statusCounts->get('draft', (object)['count' => 0, 'amount' => 0])->count ?? 0,
            'sent_count' => $statusCounts->get('issued', (object)['count' => 0, 'amount' => 0])->count ?? 0,
            'paid_count' => $statusCounts->get('paid', (object)['count' => 0, 'amount' => 0])->count ?? 0,
            'overdue_count' => $statusCounts->get('overdue', (object)['count' => 0, 'amount' => 0])->count ?? 0,
            'cancelled_count' => $statusCounts->get('cancelled', (object)['count' => 0, 'amount' => 0])->count ?? 0,
            'overdue_invoices' => $overdueInvoices,
            'overdue_amount' => $overdueAmount,
            'currency' => config('app.currency', 'TZS')
        ]);
    }

    /**
     * Get fee invoices data for DataTables.
     */
    public function data(Request $request)
    {
        $query = FeeInvoice::with(['student', 'program', 'feeGroup'])
            ->forCompany(Auth::user()->company_id);

        if (Auth::user()->branch_id) {
            $query->forBranch(Auth::user()->branch_id);
        }

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by program if provided
        if ($request->has('program_id') && $request->program_id) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by fee group if provided
        if ($request->has('fee_group_id') && $request->fee_group_id) {
            $query->where('fee_group_id', $request->fee_group_id);
        }

        // Handle DataTables search
        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->whereHas('student', function ($studentQuery) use ($searchValue) {
                    $studentQuery->where('first_name', 'like', '%' . $searchValue . '%')
                                ->orWhere('last_name', 'like', '%' . $searchValue . '%')
                                ->orWhere('student_number', 'like', '%' . $searchValue . '%');
                })
                ->orWhere('invoice_number', 'like', '%' . $searchValue . '%')
                ->orWhereHas('program', function ($programQuery) use ($searchValue) {
                    $programQuery->where('name', 'like', '%' . $searchValue . '%');
                })
                ->orWhereHas('feeGroup', function ($feeGroupQuery) use ($searchValue) {
                    $feeGroupQuery->where('name', 'like', '%' . $searchValue . '%');
                });
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('student_name', function ($invoice) {
                return $invoice->student->full_name ?? 'N/A';
            })
            ->addColumn('program_name', function ($invoice) {
                return $invoice->program->name ?? 'N/A';
            })
            ->addColumn('fee_period', function ($invoice) {
                return $invoice->getFeePeriodOptions()[$invoice->period] ?? $invoice->period;
            })
            ->addColumn('amount', function ($invoice) {
                return config('app.currency', 'TZS') . ' ' . number_format($invoice->total_amount, 2);
            })
            ->addColumn('due_date', function ($invoice) {
                return $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A';
            })
            ->addColumn('status_badge', function ($invoice) {
                $statusColors = [
                    'draft' => 'secondary',
                    'issued' => 'info',
                    'paid' => 'success',
                    'overdue' => 'danger',
                    'cancelled' => 'warning'
                ];
                $color = $statusColors[$invoice->status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($invoice->status) . '</span>';
            })
            ->addColumn('actions', function ($invoice) {
                $hashid = $invoice->hashid;
                $actions = '<a href="' . route('college.fee-invoices.show', $hashid) . '" class="btn btn-info btn-sm" title="View">
                                <i class="bx bx-show"></i>
                            </a>';

                if ($invoice->status === 'draft') {
                    $actions .= ' <a href="' . route('college.fee-invoices.edit', $hashid) . '" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="bx bx-edit"></i>
                                </a>';
                }

                if ($invoice->status === 'draft' && Auth::user()->hasRole(['admin', 'super-admin'])) {
                    $actions .= ' <form action="' . route('college.fee-invoices.send', $hashid) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to send this invoice? This will change its status to sent and prevent further editing.\')">
                                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                                    <button type="submit" class="btn btn-primary btn-sm" title="Send Invoice">
                                        <i class="bx bx-send"></i> Send
                                    </button>
                                </form>';
                }

                if ($invoice->status === 'issued') {
                    $actions .= ' <a href="' . route('college.fee-invoices.pay', $hashid) . '" class="btn btn-success btn-sm" title="Pay Invoice">
                                    <i class="bx bx-credit-card"></i> Pay
                                </a>';
                }

                if ($invoice->status === 'draft') {
                    $actions .= ' <form action="' . route('college.fee-invoices.destroy', $hashid) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to delete this fee invoice? This action cannot be undone.\')">
                                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>';
                }

                return $actions;
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $programs = Program::where('company_id', $companyId)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $feeGroups = FeeGroup::active()
            ->orderBy('name')
            ->get();

        return view('college.fee-invoices.create', compact('programs', 'feeGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:college_programs,id',
            'fee_group_id' => 'required|exists:fee_groups,id',
            'fee_period' => 'required|in:' . implode(',', array_keys(FeeInvoice::getFeePeriodOptions())),
            'due_date' => 'required|date|after:today',
            'generation_type' => 'required|in:all_students,specific_students',
            'student_ids' => 'sometimes|array',
            'student_ids.*' => 'exists:college_students,id',
        ]);

        DB::beginTransaction();
        try {
            // Get current academic year
            $currentAcademicYear = AcademicYear::where('is_current', true)
                ->where('company_id', Auth::user()->company_id)
                ->first();

            $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;
            // Get fee setting for the selected criteria
            $feeSetting = FeeSetting::where('program_id', $request->program_id)
                ->where('fee_period', $request->fee_period)
                ->where('is_active', true)
                ->whereHas('collegeFeeSettingItems', function ($query) use ($request) {
                    $query->where('fee_group_id', $request->fee_group_id)
                          ->where('fee_period', $request->fee_period);
                })
                ->with(['collegeFeeSettingItems' => function ($query) use ($request) {
                    $query->where('fee_group_id', $request->fee_group_id)
                          ->where('fee_period', $request->fee_period);
                }])
                ->first();

            if (!$feeSetting) {
                return back()->withErrors(['error' => 'No active fee setting found for the selected criteria.']);
            }

            $feeSettingItem = $feeSetting->collegeFeeSettingItems->first();

            if (!$feeSettingItem) {
                return back()->withErrors(['error' => 'No fee setting item found for the selected fee group.']);
            }

            // Get the fee group for GL transactions
            $feeGroup = FeeGroup::find($request->fee_group_id);

            // Get students based on generation type
            if ($request->generation_type === 'all_students') {
                $students = Student::where('program_id', $request->program_id)
                    ->where('company_id', Auth::user()->company_id)
                    ->when(Auth::user()->branch_id, function ($query) {
                        $query->where('branch_id', Auth::user()->branch_id);
                    })
                    ->active()
                    ->get();
            } else {
                // For specific students, if no students selected, treat as all students
                if ($request->has('student_ids') && !empty($request->student_ids)) {
                    $students = Student::whereIn('id', $request->student_ids)
                        ->where('company_id', Auth::user()->company_id)
                        ->when(Auth::user()->branch_id, function ($query) {
                            $query->where('branch_id', Auth::user()->branch_id);
                        })
                        ->get();
                } else {
                    // If specific_students but no students selected, get all students
                    $students = Student::where('program_id', $request->program_id)
                        ->where('company_id', Auth::user()->company_id)
                        ->when(Auth::user()->branch_id, function ($query) {
                            $query->where('branch_id', Auth::user()->branch_id);
                        })
                        ->active()
                        ->get();
                }
            }

            $createdInvoices = 0;
            $totalAmount = 0;

            foreach ($students as $student) {
                // Check if invoice already exists for this student, fee group, and period
                $existingInvoice = FeeInvoice::where('student_id', $student->id)
                    ->where('fee_group_id', $request->fee_group_id)
                    ->where('period', $request->fee_period)
                    ->where('academic_year_id', $academicYearId)
                    ->first();

                if ($existingInvoice) {
                    continue; // Skip if invoice already exists
                }

                // Get branch ID - use session, user's branch, or default to first branch for company
                $branchId = session('branch_id') ?: Auth::user()->branch_id;
                if (!$branchId) {
                    // Get first branch for the company
                    $defaultBranch = DB::table('branches')->where('company_id', Auth::user()->company_id)->first();
                    $branchId = $defaultBranch ? $defaultBranch->id : null;
                }

                $invoice = FeeInvoice::create([
                    'invoice_number' => 'INV-' . strtoupper(uniqid()),
                    'student_id' => $student->id,
                    'program_id' => $request->program_id,
                    'academic_year_id' => $academicYearId,
                    'fee_group_id' => $request->fee_group_id,
                    'period' => $request->fee_period,
                    'subtotal' => $feeSettingItem->amount,
                    'transport_fare' => 0, // Will be calculated if needed
                    'total_amount' => $feeSettingItem->amount,
                    'paid_amount' => 0,
                    'due_date' => $request->due_date,
                    'issue_date' => now()->toDateString(),
                    //'status' => 'draft',
                    'status' => 'issued', // Directly mark as issued
                    'company_id' => Auth::user()->company_id,
                    'branch_id' => $branchId,
                    'created_by' => Auth::id(),
                ]);

                // Create invoice item
                FeeInvoiceItem::create([
                    'college_fee_invoice_id' => $invoice->id,
                    'description' => $feeSettingItem->description ?? 'Fee Item',
                    'quantity' => 1,
                    'unit_price' => $feeSettingItem->amount,
                    'amount' => $feeSettingItem->amount,
                ]);

                // Create GL transactions for this invoice
                $this->createGlTransactions($invoice, $feeGroup);

                $createdInvoices++;
                $totalAmount += $feeSettingItem->amount;
            }

            DB::commit();

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully created {$createdInvoices} fee invoice(s) with total amount " . config('app.currency', 'TZS') . ' ' . number_format($totalAmount, 2) . ". GL transactions have been posted.",
                    'created_count' => $createdInvoices,
                    'total_amount' => $totalAmount,
                    'redirect_url' => route('college.fee-invoices.index')
                ]);
            }

            return redirect()->route('college.fee-invoices.index')
                ->with('success', "Successfully created {$createdInvoices} fee invoice(s) with total amount " . config('app.currency', 'TZS') . ' ' . number_format($totalAmount, 2) . ". GL transactions have been posted.");

        } catch (\Exception $e) {
            DB::rollBack();

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create fee invoices: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to create fee invoices: ' . $e->getMessage()]);
        }
    }

    /**
     * Create GL transactions for a fee invoice.
     */
    private function createGlTransactions(FeeInvoice $invoice, FeeGroup $feeGroup)
    {
        // Load necessary relationships
        $invoice->load(['student', 'program', 'academicYear']);

        $date = now();
        $description = "Fee Invoice {$invoice->invoice_number} - {$invoice->student->full_name} ({$invoice->program->name})";

        // Get branch ID - use session, user's branch, or default to first branch for company
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if (!$branchId) {
            // Get first branch for the company
            $defaultBranch = DB::table('branches')->where('company_id', Auth::user()->company_id)->first();
            $branchId = $defaultBranch ? $defaultBranch->id : null;
        }

        $userId = Auth::id();

        // Only create GL transactions if we have a valid branch
        if (!$branchId) {
            // Log warning but don't fail the invoice creation
            \Log::warning('Cannot create GL transactions for fee invoice: no branch available', [
                'invoice_id' => $invoice->id,
                'user_id' => $userId,
                'company_id' => Auth::user()->company_id
            ]);
            return;
        }

        // 1. Debit: Accounts Receivable (student owes the fee)
        if ($feeGroup->receivable_account_id) {
            GlTransaction::create([
                'chart_account_id' => $feeGroup->receivable_account_id,
                'customer_id' => null, // Could link to student if student is treated as customer
                'supplier_id' => null,
                'amount' => $invoice->total_amount,
                'nature' => 'debit',
                'transaction_id' => $invoice->id,
                'transaction_type' => 'college_fee_invoice',
                'date' => $date,
                'description' => $description,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);
        }

        // 2. Credit: Fee Income Account (revenue from fees)
        if ($feeGroup->income_account_id) {
            GlTransaction::create([
                'chart_account_id' => $feeGroup->income_account_id,
                'customer_id' => null,
                'supplier_id' => null,
                'amount' => $invoice->total_amount,
                'nature' => 'credit',
                'transaction_id' => $invoice->id,
                'transaction_type' => 'college_fee_invoice',
                'date' => $date,
                'description' => $description,
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);
        }

        // Note: Transport fare would be handled separately if needed
        // For now, we're only handling the main fee amount
    }

    /**
     * Display the specified resource.
     */
    public function show(FeeInvoice $feeInvoice)
    {
        $feeInvoice->load(['student', 'program', 'feeGroup', 'academicYear', 'feeInvoiceItems']);

        return view('college.fee-invoices.show', compact('feeInvoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FeeInvoice $feeInvoice)
    {
        if ($feeInvoice->status !== 'draft') {
            return redirect()->route('college.fee-invoices.show', $feeInvoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $programs = Program::where('company_id', $companyId)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $feeGroups = FeeGroup::active()
            ->orderBy('name')
            ->get();

        $feePeriodOptions = FeeInvoice::getFeePeriodOptions();

        return view('college.fee-invoices.edit', compact('feeInvoice', 'programs', 'feeGroups', 'feePeriodOptions'));
    }

    /**
     * Process payment for the specified fee invoice.
     */
    public function pay(Request $request, FeeInvoice $feeInvoice)
    {
        \Log::info('Pay method called', [
            'fee_invoice_id' => $feeInvoice->id,
            'status' => $feeInvoice->status,
            'method' => $request->method(),
            'is_post' => $request->isMethod('post'),
            'all_data' => $request->all()
        ]);

        // Check if invoice is in issued status (can be paid)
        if ($feeInvoice->status !== 'issued') {
            $message = 'This invoice cannot be paid because it is in "' . ucfirst($feeInvoice->status) . '" status. ';
            $message .= 'Only invoices in "Issued" status can be paid. ';
            if ($feeInvoice->status === 'draft') {
                $message .= 'Please send/issue the invoice first.';
            }
            return redirect()->route('college.fee-invoices.show', $feeInvoice)
                ->with('error', $message);
        }

        // Check if invoice is already fully paid
        if ($feeInvoice->paid_amount >= $feeInvoice->total_amount) {
            return redirect()->route('college.fee-invoices.show', $feeInvoice)
                ->with('error', 'This invoice is already fully paid.');
        }

        if ($request->isMethod('post')) {
            \Log::info('Processing POST request for payment', [
                'payment_amount' => $request->payment_amount ?? 'not set',
                'payment_method' => $request->payment_method ?? 'not set'
            ]);
            $request->validate([
                'payment_amount' => 'required|numeric|min:0.01|max:' . ($feeInvoice->total_amount - $feeInvoice->paid_amount),
                'payment_date' => 'required|date',
                'payment_method' => 'required|string',
                'bank_account_id' => 'nullable|exists:bank_accounts,id',
                'reference_number' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
            ]);

            DB::beginTransaction();
            try {
                $paymentAmount = $request->payment_amount;
                $newPaidAmount = $feeInvoice->paid_amount + $paymentAmount;

                // Update invoice payment amount
                $feeInvoice->update([
                    'paid_amount' => $newPaidAmount,
                    'status' => $newPaidAmount >= $feeInvoice->total_amount ? 'paid' : 'issued',
                ]);

                // Create receipt and receipt items
                $receipt = $this->createPaymentReceipt($feeInvoice, $request, $paymentAmount);

                // Create GL transactions through the receipt
                $receipt->createGlTransactions();

                DB::commit();

                return redirect()->route('college.fee-invoices.show', $feeInvoice)
                    ->with('success', 'Payment processed successfully. Amount: ' . config('app.currency', 'TZS') . ' ' . number_format($paymentAmount, 2) . '. Receipt created: ' . $receipt->reference);

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Failed to process payment: ' . $e->getMessage()]);
            }
        }

        // Show payment form
        $bankAccounts = \App\Models\BankAccount::with('chartAccount')
            ->whereHas('chartAccount.accountClassGroup', function ($query) {
                $query->where('company_id', Auth::user()->company_id);
            })
            ->get();

        return view('college.fee-invoices.pay', compact('feeInvoice', 'bankAccounts'));
    }

    /**
     * Create payment receipt and receipt items.
     */
    private function createPaymentReceipt(FeeInvoice $invoice, Request $request, $paymentAmount)
    {
        // Load necessary relationships
        $invoice->load(['student', 'program', 'feeGroup', 'feeInvoiceItems']);

        // Get branch ID
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        if (!$branchId) {
            $defaultBranch = DB::table('branches')->where('company_id', Auth::user()->company_id)->first();
            $branchId = $defaultBranch ? $defaultBranch->id : null;
        }

        // Generate receipt reference
        $receiptNumber = 'RCP-' . date('Y') . '-' . str_pad(\App\Models\Receipt::count() + 1, 4, '0', STR_PAD_LEFT);

        // Create the receipt
        $receipt = \App\Models\Receipt::create([
            'reference' => $receiptNumber,
            'reference_type' => 'college_fee_invoice',
            'reference_number' => $invoice->invoice_number,
            'amount' => $paymentAmount,
            'currency' => config('app.currency', 'TZS'),
            'date' => $request->payment_date,
            'description' => 'Payment for Fee Invoice ' . $invoice->invoice_number . ' - ' . $invoice->student->full_name,
            'user_id' => Auth::id(),
            'bank_account_id' => $request->bank_account_id,
            'payee_type' => 'customer', // Student is treated as customer
            'payee_id' => $invoice->student_id,
            'payee_name' => $invoice->student->full_name,
            'branch_id' => $branchId,
            'approved' => true, // Auto-approve fee payments
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Create receipt items - one for each fee invoice item
        foreach ($invoice->feeInvoiceItems as $invoiceItem) {
            // Calculate the proportion of this payment that applies to this item
            $itemProportion = $invoiceItem->amount / $invoice->total_amount;
            $itemPaymentAmount = round($paymentAmount * $itemProportion, 2);

            // Get the income account from the fee group
            $incomeAccountId = $invoice->feeGroup->income_account_id;

            \App\Models\ReceiptItem::create([
                'receipt_id' => $receipt->id,
                'chart_account_id' => $incomeAccountId,
                'amount' => $itemPaymentAmount,
                'base_amount' => $itemPaymentAmount, // No VAT for fee payments
                'description' => $invoiceItem->description ?? 'Fee payment - ' . $invoice->student->full_name,
            ]);
        }

        // If there are any rounding differences, adjust the last item
        $totalReceiptItems = $receipt->receiptItems()->sum('amount');
        if ($totalReceiptItems != $paymentAmount) {
            $difference = $paymentAmount - $totalReceiptItems;
            $lastItem = $receipt->receiptItems()->latest()->first();
            if ($lastItem) {
                $lastItem->update([
                    'amount' => $lastItem->amount + $difference,
                    'base_amount' => $lastItem->base_amount + $difference,
                ]);
            }
        }

        return $receipt;
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FeeInvoice $feeInvoice)
    {
        if ($feeInvoice->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft invoices can be updated.']);
        }

        $request->validate([
            'due_date' => 'required|date|after:today',
        ]);

        $feeInvoice->update([
            'due_date' => $request->due_date,
        ]);

        return redirect()->route('college.fee-invoices.show', $feeInvoice)
            ->with('success', 'Fee invoice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FeeInvoice $feeInvoice)
    {
        if ($feeInvoice->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft invoices can be deleted.']);
        }

        $feeInvoice->delete();

        return redirect()->route('college.fee-invoices.index')
            ->with('success', 'Fee invoice deleted successfully.');
    }

    /**
     * Send the specified invoice (change status to sent).
     */
    public function send(FeeInvoice $feeInvoice)
    {
        // Check if user has permission (admin or super admin)
        if (!Auth::user()->hasRole(['admin', 'super-admin'])) {
            return back()->withErrors(['error' => 'You do not have permission to send invoices.']);
        }

        if ($feeInvoice->status !== 'draft') {
            return back()->withErrors(['error' => 'Only draft invoices can be sent.']);
        }

        $feeInvoice->update([
            'status' => 'issued',
            'sent_at' => now(),
            'sent_by' => Auth::id(),
        ]);

        return redirect()->route('college.fee-invoices.show', $feeInvoice)
            ->with('success', 'Fee invoice sent successfully.');
    }

    /**
     * Preview bulk send for draft invoices.
     */
    public function bulkSendPreview(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:college_programs,id',
            'fee_group_id' => 'required|exists:fee_groups,id',
            'period' => 'required|in:' . implode(',', array_keys(FeeInvoice::getFeePeriodOptions())),
        ]);

        try {
            // Get current academic year
            $currentAcademicYear = AcademicYear::where('is_current', true)
                ->where('company_id', Auth::user()->company_id)
                ->first();

            $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;

            // Get draft invoices matching the criteria
            $query = FeeInvoice::with(['student', 'program', 'feeGroup'])
                ->where('company_id', Auth::user()->company_id)
                ->where('status', 'draft')
                ->where('program_id', $request->program_id)
                ->where('fee_group_id', $request->fee_group_id)
                ->where('period', $request->period);

            if (Auth::user()->branch_id) {
                $query->where('branch_id', Auth::user()->branch_id);
            }

            if ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            }

            $invoices = $query->get();

            $program = Program::find($request->program_id);
            $feeGroup = FeeGroup::find($request->fee_group_id);
            $currencySymbol = config('app.currency', 'TZS');
            $totalAmount = $invoices->sum('total_amount');

            $html = '
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-send me-2"></i>Bulk Send Preview - Draft Invoices
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Program:</strong> ' . ($program->name ?? 'N/A') . '</p>
                                <p><strong>Fee Group:</strong> ' . ($feeGroup->name ?? 'N/A') . '</p>
                                <p><strong>Fee Period:</strong> ' . FeeInvoice::getFeePeriodOptions()[$request->period] . '</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Draft Invoices Found:</strong> ' . number_format($invoices->count()) . '</p>
                                <p><strong>Total Amount:</strong> ' . $currencySymbol . ' ' . number_format($totalAmount, 2) . '</p>
                            </div>
                        </div>';

            if ($invoices->isEmpty()) {
                $html .= '
                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>No Draft Invoices Found</strong><br>
                            There are no draft invoices matching the selected criteria.
                        </div>';
            } else {
                $html .= '
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-1"></i>
                            <strong>Ready to Send:</strong> ' . number_format($invoices->count()) . ' draft invoice(s) will be sent.
                            This action cannot be undone.
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6>Draft Invoices to Send:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Invoice #</th>
                                                <th>Student</th>
                                                <th>Amount</th>
                                                <th>Due Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>';

                foreach ($invoices->take(10) as $invoice) {
                    $html .= '
                                            <tr>
                                                <td>' . $invoice->invoice_number . '</td>
                                                <td>' . ($invoice->student->full_name ?? 'N/A') . '</td>
                                                <td class="text-end">' . $currencySymbol . ' ' . number_format($invoice->total_amount, 2) . '</td>
                                                <td>' . ($invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A') . '</td>
                                            </tr>';
                }

                if ($invoices->count() > 10) {
                    $html .= '
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">
                                                    <em>... and ' . ($invoices->count() - 10) . ' more invoices</em>
                                                </td>
                                            </tr>';
                }

                $html .= '
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-active">
                                                <th colspan="2">Total</th>
                                                <th class="text-end">' . $currencySymbol . ' ' . number_format($totalAmount, 2) . '</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <form action="' . route('college.fee-invoices.bulk-send') . '" method="POST" class="d-inline">
                                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                                    <input type="hidden" name="program_id" value="' . $request->program_id . '">
                                    <input type="hidden" name="fee_group_id" value="' . $request->fee_group_id . '">
                                    <input type="hidden" name="period" value="' . $request->period . '">
                                    <button type="submit" class="btn btn-success btn-lg" onclick="return confirm(\'Are you sure you want to send ' . $invoices->count() . ' draft invoices? This will change their status to sent and they cannot be edited anymore.\')">
                                        <i class="bx bx-send me-2"></i>Send All ' . $invoices->count() . ' Invoices
                                    </button>
                                </form>
                            </div>
                        </div>';
            }

            $html .= '
                    </div>
                </div>';

            return response()->json(['html' => $html]);

        } catch (\Exception $e) {
            return response()->json([
                'html' => '<div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Preview Error:</strong> ' . $e->getMessage() . '
                </div>'
            ], 500);
        }
    }

    /**
     * Show bulk send form.
     */
    public function bulkSendForm()
    {
        // Check if user has permission (admin or super admin)
        if (!Auth::user()->hasRole(['admin', 'super-admin'])) {
            return redirect()->route('college.fee-invoices.index')
                ->with('error', 'You do not have permission to send invoices.');
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $programs = Program::where('company_id', $companyId)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        $feeGroups = FeeGroup::active()
            ->orderBy('name')
            ->get();

        $feePeriodOptions = FeeInvoice::getFeePeriodOptions();

        return view('college.fee-invoices.bulk-send', compact('programs', 'feeGroups', 'feePeriodOptions'));
    }
    public function bulkSend(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:college_programs,id',
            'fee_group_id' => 'required|exists:fee_groups,id',
            'period' => 'required|in:' . implode(',', array_keys(FeeInvoice::getFeePeriodOptions())),
        ]);

        // Check if user has permission (admin or super admin)
        if (!Auth::user()->hasRole(['admin', 'super-admin'])) {
            return back()->withErrors(['error' => 'You do not have permission to send invoices.']);
        }

        DB::beginTransaction();
        try {
            // Get current academic year
            $currentAcademicYear = AcademicYear::where('is_current', true)
                ->where('company_id', Auth::user()->company_id)
                ->first();

            $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;

            // Get draft invoices matching the criteria
            $query = FeeInvoice::where('company_id', Auth::user()->company_id)
                ->where('status', 'draft')
                ->where('program_id', $request->program_id)
                ->where('fee_group_id', $request->fee_group_id)
                ->where('period', $request->period);

            if (Auth::user()->branch_id) {
                $query->where('branch_id', Auth::user()->branch_id);
            }

            if ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            }

            $invoices = $query->get();

            if ($invoices->isEmpty()) {
                return back()->withErrors(['error' => 'No draft invoices found matching the selected criteria.']);
            }

            $sentCount = 0;
            $totalAmount = 0;

            foreach ($invoices as $invoice) {
                $invoice->update([
                    'status' => 'issued',
                    'sent_at' => now(),
                    'sent_by' => Auth::id(),
                ]);
                $sentCount++;
                $totalAmount += $invoice->total_amount;
            }

            DB::commit();

            return redirect()->route('college.fee-invoices.index')
                ->with('success', "Successfully sent {$sentCount} fee invoice(s) with total amount " . config('app.currency', 'TZS') . ' ' . number_format($totalAmount, 2) . ".");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to send invoices: ' . $e->getMessage()]);
        }
    }

    /**
     * Get students for the specified program and fee period.
     */
    public function getStudents(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:college_programs,id',
            'fee_period' => 'required|string',
        ]);

        $students = Student::where('program_id', $request->program_id)
            ->where('company_id', Auth::user()->company_id)
            ->when(Auth::user()->branch_id, function ($query) {
                $query->where('branch_id', Auth::user()->branch_id);
            })
            ->active()
            ->select('id', 'first_name', 'last_name', 'student_number')
            ->orderBy('first_name')
            ->get()
            ->map(function ($student) {
                $student->name = $student->first_name . ' ' . $student->last_name;
                return $student;
            });

        return response()->json([
            'students' => $students
        ]);
    }

    /**
     * Validate invoice generation configuration.
     */
    public function validateInvoices(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:college_programs,id',
            'fee_group_id' => 'required|exists:fee_groups,id',
            'fee_period' => 'required|in:' . implode(',', array_keys(FeeInvoice::getFeePeriodOptions())),
            'due_date' => 'required|date|after:today',
            'generation_type' => 'required|in:all_students,specific_students',
            'student_ids' => 'sometimes|array',
            'student_ids.*' => 'exists:college_students,id',
        ]);

        try {
            // Get current academic year
            $currentAcademicYear = AcademicYear::where('is_current', true)
                ->where('company_id', Auth::user()->company_id)
                ->first();

            $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;

            // Get common data
            $program = Program::findOrFail($request->program_id);
            $feeGroup = FeeGroup::findOrFail($request->fee_group_id);
            $currencySymbol = config('app.currency', 'TZS');

            // Use academic year name or generate one from dates
            $academicYearName = $currentAcademicYear ? ($currentAcademicYear->year_name ?: ($currentAcademicYear->start_date . ' - ' . $currentAcademicYear->end_date)) : 'Current Year';

            if ($request->generation_type === 'specific_students') {
                $validationData = $this->validateSpecificStudentsInvoices($request);

                return response()->json([
                    'success' => true,
                    'type' => 'specific',
                    'program_name' => $program->name,
                    'fee_group_name' => $feeGroup->name,
                    'fee_period' => $request->fee_period,
                    'academic_year_name' => $academicYearName,
                    'currency_symbol' => $currencySymbol,
                    'due_date' => $request->due_date,
                    'selected_students' => $validationData['selected_students'],
                    'total_amount' => $validationData['total_amount']
                ]);
            } else {
                $validationData = $this->validateBulkInvoices($request);

                // Check if debug info exists (no students found)
                if (isset($validationData['debug_info'])) {
                    return response()->json([
                        'success' => false,
                        'message' => $validationData['debug_info']['message'],
                        'debug_info' => $validationData['debug_info']
                    ]);
                }

                // Calculate total amount from all students that will be created
                $totalAmount = 0;
                $students = [];

                foreach ($validationData['validation_list'] as $studentValidation) {
                    if ($studentValidation['status'] === 'will_create') {
                        $totalAmount += $studentValidation['total_amount'];
                    }

                    $students[] = [
                        'id' => $studentValidation['student_id'],
                        'name' => $studentValidation['student_name'],
                        'student_number' => $studentValidation['student_number'],
                        'amount' => $studentValidation['total_amount'],
                        'status' => $studentValidation['status'],
                        'has_existing_invoice' => $studentValidation['status'] === 'already_exists'
                    ];
                }

                return response()->json([
                    'success' => true,
                    'type' => 'bulk',
                    'program_name' => $program->name,
                    'fee_group_name' => $feeGroup->name,
                    'fee_period' => $request->fee_period,
                    'academic_year_name' => $academicYearName,
                    'currency_symbol' => $currencySymbol,
                    'due_date' => $request->due_date,
                    'total_students' => $validationData['total_students'],
                    'new_invoices' => $validationData['will_create'],
                    'existing_invoices' => $validationData['will_skip'],
                    'total_amount' => $totalAmount,
                    'students' => $students
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate bulk invoices for program.
     */
    private function validateBulkInvoices(Request $request)
    {
        // Get current academic year
        $currentAcademicYear = AcademicYear::where('is_current', true)
            ->where('company_id', Auth::user()->company_id)
            ->first();

        $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;

        $students = Student::where('program_id', $request->program_id)
            ->where('company_id', Auth::user()->company_id)
            ->when(Auth::user()->branch_id, function ($query) {
                $query->where('branch_id', Auth::user()->branch_id);
            })
            ->active()
            ->get();

        // Debug: Check if students exist
        if ($students->isEmpty()) {
            // Check without active filter
            $allStudents = Student::where('program_id', $request->program_id)
                ->where('company_id', Auth::user()->company_id)
                ->when(Auth::user()->branch_id, function ($query) {
                    $query->where('branch_id', Auth::user()->branch_id);
                })
                ->get();

            return [
                'type' => 'bulk',
                'total_students' => 0,
                'will_create' => 0,
                'will_skip' => 0,
                'validation_list' => [],
                'debug_info' => [
                    'program_id' => $request->program_id,
                    'company_id' => Auth::user()->company_id,
                    'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
                    'total_students_in_program' => $allStudents->count(),
                    'active_students_in_program' => $students->count(),
                    'student_statuses' => $allStudents->pluck('status')->unique()->toArray(),
                    'message' => 'No active students found in the selected program. Check if students exist and have status="active".'
                ]
            ];
        }

        $validationList = [];
        $willCreate = 0;
        $willSkip = 0;

        foreach ($students as $student) {
            $studentValidation = $this->validateInvoiceForStudent($student, $request->program_id, $academicYearId, $request->fee_period, $request->fee_group_id);

            if ($studentValidation['status'] === 'will_create') {
                $willCreate++;
            } elseif ($studentValidation['status'] === 'already_exists') {
                $willSkip++;
            } else {
                $willSkip++;
            }

            $validationList[] = $studentValidation;
        }

        return [
            'type' => 'bulk',
            'total_students' => $students->count(),
            'will_create' => $willCreate,
            'will_skip' => $willSkip,
            'validation_list' => $validationList
        ];
    }

    /**
     * Validate specific students invoices.
     */
    private function validateSpecificStudentsInvoices(Request $request)
    {
        // Get current academic year
        $currentAcademicYear = AcademicYear::where('is_current', true)
            ->where('company_id', Auth::user()->company_id)
            ->first();

        $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;

        $students = Student::whereIn('id', $request->student_ids)
            ->where('company_id', Auth::user()->company_id)
            ->when(Auth::user()->branch_id, function ($query) {
                $query->where('branch_id', Auth::user()->branch_id);
            })
            ->get();

        $selectedStudents = [];
        $totalAmount = 0;

        foreach ($students as $student) {
            $studentValidation = $this->validateInvoiceForStudent($student, $request->program_id, $academicYearId, $request->fee_period, $request->fee_group_id);

            if ($studentValidation['status'] === 'will_create') {
                $totalAmount += $studentValidation['total_amount'];
            }

            $selectedStudents[] = [
                'id' => $studentValidation['student_id'],
                'name' => $studentValidation['student_name'],
                'student_number' => $studentValidation['student_number'],
                'amount' => $studentValidation['total_amount'],
                'status' => $studentValidation['status'],
                'has_existing_invoice' => $studentValidation['status'] === 'already_exists'
            ];
        }

        return [
            'selected_students' => $selectedStudents,
            'total_amount' => $totalAmount
        ];
    }

    /**
     * Validate invoice for a specific student without creating it.
     */
    private function validateInvoiceForStudent($student, $programId, $academicYearId, $feePeriod, $feeGroupId)
    {
        // Check if invoice already exists
        $existingInvoice = FeeInvoice::where('student_id', $student->id)
            ->where('fee_group_id', $feeGroupId)
            ->where('period', $feePeriod)
            ->where('academic_year_id', $academicYearId)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingInvoice) {
            return [
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'student_number' => $student->student_number,
                'status' => 'already_exists',
                'reason' => 'Invoice already exists for this period',
                'total_amount' => $existingInvoice->total_amount,
                'existing_invoice_id' => $existingInvoice->id,
                'existing_invoice_number' => $existingInvoice->invoice_number
            ];
        }

        // Get fee settings for the program and specific period
        $feeSetting = FeeSetting::where('program_id', $programId)
            ->where('fee_period', $feePeriod)
            ->where('is_active', true)
            ->whereHas('collegeFeeSettingItems', function ($query) use ($feeGroupId, $feePeriod) {
                $query->where('fee_group_id', $feeGroupId)
                      ->where('fee_period', $feePeriod);
            })
            ->with(['collegeFeeSettingItems' => function ($query) use ($feeGroupId, $feePeriod) {
                $query->where('fee_group_id', $feeGroupId)
                      ->where('fee_period', $feePeriod);
            }])
            ->first();

        if (!$feeSetting) {
            return [
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'student_number' => $student->student_number,
                'status' => 'no_fee_settings',
                'reason' => 'No fee settings found for this program and period',
                'total_amount' => 0,
                'debug_info' => [
                    'program_id' => $programId,
                    'fee_period' => $feePeriod,
                    'fee_group_id' => $feeGroupId,
                    'fee_settings_count' => FeeSetting::where('program_id', $programId)->count(),
                    'active_fee_settings_count' => FeeSetting::where('program_id', $programId)->where('is_active', true)->count(),
                    'fee_settings_for_period' => FeeSetting::where('program_id', $programId)->where('fee_period', $feePeriod)->count(),
                    'active_fee_settings_for_period' => FeeSetting::where('program_id', $programId)->where('fee_period', $feePeriod)->where('is_active', true)->count()
                ]
            ];
        }

        $feeSettingItem = $feeSetting->collegeFeeSettingItems->first();

        if (!$feeSettingItem) {
            return [
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'student_number' => $student->student_number,
                'status' => 'no_fee_settings',
                'reason' => 'No fee setting item found for this fee group',
                'total_amount' => 0,
                'debug_info' => [
                    'program_id' => $programId,
                    'fee_period' => $feePeriod,
                    'fee_group_id' => $feeGroupId,
                    'fee_setting_items_count' => $feeSetting->collegeFeeSettingItems->count(),
                    'fee_setting_items_for_group' => $feeSetting->collegeFeeSettingItems->where('fee_group_id', $feeGroupId)->count()
                ]
            ];
        }

        $totalAmount = $feeSettingItem->amount;

        return [
            'student_id' => $student->id,
            'student_name' => $student->first_name . ' ' . $student->last_name,
            'student_number' => $student->student_number,
            'status' => 'will_create',
            'total_amount' => $totalAmount,
            'fee_amount' => $feeSettingItem->amount
        ];
    }

    /**
     * Preview invoice generation.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:college_programs,id',
            'fee_group_id' => 'required|exists:fee_groups,id',
            'fee_period' => 'required|in:' . implode(',', array_keys(FeeInvoice::getFeePeriodOptions())),
            'due_date' => 'required|date',
            'generation_type' => 'required|in:all_students,specific_students',
        ]);

        try {
            // Check if fee setting exists
            $feeSetting = FeeSetting::where('program_id', $request->program_id)
                ->where('fee_period', $request->fee_period)
                ->where('is_active', true)
                ->whereHas('collegeFeeSettingItems', function ($query) use ($request) {
                    $query->where('fee_group_id', $request->fee_group_id)
                          ->where('fee_period', $request->fee_period);
                })
                ->with(['collegeFeeSettingItems' => function ($query) use ($request) {
                    $query->where('fee_group_id', $request->fee_group_id)
                          ->where('fee_period', $request->fee_period);
                }])
                ->first();

            if (!$feeSetting || !$feeSetting->collegeFeeSettingItems->first()) {
                return response()->json([
                    'html' => '<div class="alert alert-warning">
                        <i class="bx bx-error-circle me-2"></i>
                        <strong>No Fee Settings Found</strong><br>
                        No active fee settings found for the selected program, fee group, and period combination.
                        Please configure fee settings first before generating invoices.
                    </div>'
                ]);
            }

            $feeSettingItem = $feeSetting->collegeFeeSettingItems->first();
            $program = Program::find($request->program_id);
            $feeGroup = FeeGroup::find($request->fee_group_id);
            $currencySymbol = config('app.currency', 'TZS');

            // Get students count and calculate totals
            $studentsCount = 0;
            $estimatedTotal = 0;
            $studentDetails = [];

            if ($request->generation_type === 'all_students') {
                $students = Student::where('program_id', $request->program_id)
                    ->where('company_id', Auth::user()->company_id)
                    ->when(Auth::user()->branch_id, function ($query) {
                        $query->where('branch_id', Auth::user()->branch_id);
                    })
                    ->active()
                    ->get();

                $studentsCount = $students->count();

                // Calculate estimated total for all students
                foreach ($students as $student) {
                    $studentValidation = $this->validateInvoiceForStudent($student, $request->program_id, null, $request->fee_period, $request->fee_group_id);
                    if ($studentValidation['status'] === 'will_create') {
                        $estimatedTotal += $studentValidation['total_amount'];
                        $studentDetails[] = [
                            'name' => $studentValidation['student_name'],
                            'number' => $studentValidation['student_number'],
                            'amount' => $studentValidation['total_amount'],
                            'status' => 'will_create'
                        ];
                    } else {
                        $studentDetails[] = [
                            'name' => $studentValidation['student_name'],
                            'number' => $studentValidation['student_number'],
                            'amount' => $studentValidation['total_amount'],
                            'status' => $studentValidation['status']
                        ];
                    }
                }
            } else {
                // Specific students
                if ($request->has('student_ids') && !empty($request->student_ids)) {
                    $students = Student::whereIn('id', $request->student_ids)
                        ->where('company_id', Auth::user()->company_id)
                        ->when(Auth::user()->branch_id, function ($query) {
                            $query->where('branch_id', Auth::user()->branch_id);
                        })
                        ->get();

                    $studentsCount = $students->count();

                    // Calculate estimated total for selected students
                    foreach ($students as $student) {
                        $studentValidation = $this->validateInvoiceForStudent($student, $request->program_id, null, $request->fee_period, $request->fee_group_id);
                        if ($studentValidation['status'] === 'will_create') {
                            $estimatedTotal += $studentValidation['total_amount'];
                        }
                        $studentDetails[] = [
                            'name' => $studentValidation['student_name'],
                            'number' => $studentValidation['student_number'],
                            'amount' => $studentValidation['total_amount'],
                            'status' => $studentValidation['status']
                        ];
                    }
                }
            }

            // Count students by status
            $willCreateCount = count(array_filter($studentDetails, function($s) { return $s['status'] === 'will_create'; }));
            $existingCount = count(array_filter($studentDetails, function($s) { return $s['status'] === 'already_exists'; }));
            $noSettingsCount = count(array_filter($studentDetails, function($s) { return $s['status'] === 'no_fee_settings'; }));

            $html = '
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-receipt me-2"></i>Invoice Preview - ' . ($request->generation_type === 'all_students' ? 'Bulk Generation' : 'Specific Students') . '
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Program:</strong> ' . ($program->name ?? 'N/A') . '</p>
                                <p><strong>Fee Group:</strong> ' . ($feeGroup->name ?? 'N/A') . '</p>
                                <p><strong>Fee Period:</strong> ' . FeeInvoice::getFeePeriodOptions()[$request->fee_period] . '</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Due Date:</strong> ' . date('M d, Y', strtotime($request->due_date)) . '</p>
                                <p><strong>Students Selected:</strong> ' . number_format($studentsCount) . '</p>
                                <p><strong>Estimated Total:</strong> ' . $currencySymbol . ' ' . number_format($estimatedTotal, 2) . '</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading mb-2">
                                        <i class="bx bx-info-circle me-1"></i>Generation Summary
                                    </h6>
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="p-2">
                                                <h5 class="text-success mb-1">' . $willCreateCount . '</h5>
                                                <small class="text-muted">New Invoices</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2">
                                                <h5 class="text-warning mb-1">' . $existingCount . '</h5>
                                                <small class="text-muted">Existing</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2">
                                                <h5 class="text-danger mb-1">' . $noSettingsCount . '</h5>
                                                <small class="text-muted">No Settings</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6>Fee Breakdown:</h6>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>' . ($feeSettingItem->description ?? 'Fee Item') . '</td>
                                            <td class="text-end">' . $currencySymbol . ' ' . number_format($feeSettingItem->amount, 2) . '</td>
                                            <td class="text-center">' . $willCreateCount . '</td>
                                            <td class="text-end">' . $currencySymbol . ' ' . number_format($estimatedTotal, 2) . '</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <th colspan="3">Estimated Total Amount</th>
                                            <th class="text-end">' . $currencySymbol . ' ' . number_format($estimatedTotal, 2) . '</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>';

            // Show sample students if there are students to create
            if ($willCreateCount > 0) {
                $sampleStudents = array_filter($studentDetails, function($s) { return $s['status'] === 'will_create'; });
                $sampleStudents = array_slice($sampleStudents, 0, 5); // Show first 5

                $html .= '
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6>Sample Students (First ' . count($sampleStudents) . '):</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Student Number</th>
                                                <th>Name</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>';

                foreach ($sampleStudents as $student) {
                    $html .= '
                                            <tr>
                                                <td>' . $student['number'] . '</td>
                                                <td>' . $student['name'] . '</td>
                                                <td class="text-end">' . $currencySymbol . ' ' . number_format($student['amount'], 2) . '</td>
                                            </tr>';
                }

                if ($willCreateCount > 5) {
                    $html .= '
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">
                                                    <em>... and ' . ($willCreateCount - 5) . ' more students</em>
                                                </td>
                                            </tr>';
                }

                $html .= '
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>';
            }

            $html .= '
                        <div class="alert alert-info small mt-3">
                            <i class="bx bx-info-circle me-1"></i>
                            <strong>Note:</strong> This preview shows estimated invoice details based on current fee settings.
                            The actual generation will validate each student individually and may differ slightly.
                        </div>
                    </div>
                </div>';

            return response()->json(['html' => $html]);

        } catch (\Exception $e) {
            return response()->json([
                'html' => '<div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Preview Error:</strong> ' . $e->getMessage() . '
                </div>'
            ], 500);
        }
    }
}