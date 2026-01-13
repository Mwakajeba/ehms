<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccrualSchedule;
use App\Models\AccrualJournal;
use App\Models\ChartAccount;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\Customer;
use App\Services\AccrualScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class AccrualsPrepaymentsController extends Controller
{
    protected $scheduleService;

    public function __construct(AccrualScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Display a listing of accrual schedules.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        
        // Handle DataTables AJAX request
        if ($request->ajax()) {
            return $this->getSchedulesData($request);
        }
        
        // Calculate statistics
        $totalSchedules = AccrualSchedule::forCompany($companyId)->count();
        $activeSchedules = AccrualSchedule::forCompany($companyId)->where('status', 'active')->count();
        $totalAmount = AccrualSchedule::forCompany($companyId)->sum('total_amount');
        $remainingAmount = AccrualSchedule::forCompany($companyId)->sum('remaining_amount');
        $amortisedAmount = AccrualSchedule::forCompany($companyId)->sum('amortised_amount');
        
        $branches = Branch::where('company_id', $companyId)->orderBy('name')->get();
        
        return view('accounting.accruals-prepayments.index', compact(
            'totalSchedules', 
            'activeSchedules', 
            'totalAmount', 
            'remainingAmount', 
            'amortisedAmount',
            'branches'
        ));
    }

    /**
     * Get schedules data for DataTables
     */
    private function getSchedulesData(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        
        $query = AccrualSchedule::forCompany($companyId)
            ->with(['branch', 'expenseIncomeAccount', 'balanceSheetAccount', 'preparedBy', 'approvedBy']);
        
        // Apply filters
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        if ($request->filled('schedule_type')) {
            $query->where('schedule_type', $request->schedule_type);
        }
        
        if ($request->filled('nature')) {
            $query->where('nature', $request->nature);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('schedule_number_link', function ($schedule) {
                return '<a href="' . route('accounting.accruals-prepayments.show', $schedule->encoded_id) . '" class="text-primary fw-bold">' . $schedule->schedule_number . '</a>';
            })
            ->addColumn('category_name', function ($schedule) {
                return $schedule->category_name;
            })
            ->addColumn('formatted_start_date', function ($schedule) {
                return $schedule->start_date->format('M d, Y');
            })
            ->addColumn('formatted_end_date', function ($schedule) {
                return $schedule->end_date->format('M d, Y');
            })
            ->addColumn('formatted_total_amount', function ($schedule) {
                return number_format($schedule->total_amount, 2) . ' ' . $schedule->currency_code;
            })
            ->addColumn('formatted_remaining_amount', function ($schedule) {
                return number_format($schedule->remaining_amount, 2) . ' ' . $schedule->currency_code;
            })
            ->addColumn('status_badge', function ($schedule) {
                $badgeClass = match($schedule->status) {
                    'draft' => 'bg-secondary',
                    'submitted' => 'bg-info',
                    'approved' => 'bg-primary',
                    'active' => 'bg-success',
                    'completed' => 'bg-dark',
                    'cancelled' => 'bg-danger',
                    default => 'bg-secondary',
                };
                return '<span class="badge ' . $badgeClass . '">' . ucfirst($schedule->status) . '</span>';
            })
            ->addColumn('actions', function ($schedule) {
                $actions = '<div class="d-flex gap-1">';
                $actions .= '<a href="' . route('accounting.accruals-prepayments.show', $schedule->encoded_id) . '" class="btn btn-sm btn-info" title="View"><i class="bx bx-show"></i></a>';
                
                if ($schedule->canBeEdited()) {
                    $actions .= '<a href="' . route('accounting.accruals-prepayments.edit', $schedule->encoded_id) . '" class="btn btn-sm btn-primary" title="Edit"><i class="bx bx-edit"></i></a>';
                }
                
                if ($schedule->canBeCancelled()) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteSchedule(\'' . $schedule->encoded_id . '\')" title="Delete"><i class="bx bx-trash"></i></button>';
                }
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['schedule_number_link', 'status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new schedule.
     */
    public function create()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        
        $branches = Branch::where('company_id', $companyId)->get();
        $suppliers = Supplier::where('company_id', $companyId)->get();
        $customers = Customer::where('company_id', $companyId)->get();
        
        // Get P&L accounts (expense/income accounts) using joins
        $expenseIncomeAccounts = ChartAccount::join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $companyId)
            ->whereIn('account_class.name', ['expense', 'expenses', 'income', 'revenue'])
            ->select('chart_accounts.*')
            ->orderBy('chart_accounts.account_code')
            ->get();
        
        // Get balance sheet accounts (asset/liability accounts) using joins
        $balanceSheetAccounts = ChartAccount::join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $companyId)
            ->whereIn('account_class.name', ['assets', 'liabilities'])
            ->select('chart_accounts.*')
            ->orderBy('chart_accounts.account_code')
            ->get();
        
        // Get bank accounts for prepayment payment method
        // Filter by company through chart account relationship (same pattern as other controllers)
        // Also include bank accounts with direct company_id if they exist
        $bankAccounts = \App\Models\BankAccount::where(function($query) use ($companyId) {
                $query->whereHas('chartAccount.accountClassGroup', function($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    })
                    ->orWhere('company_id', $companyId);
            })
            ->orderBy('name')
            ->get();
        
        return view('accounting.accruals-prepayments.create', compact(
            'branches', 'suppliers', 'customers', 'expenseIncomeAccounts', 'balanceSheetAccounts', 'bankAccounts'
        ));
    }

    /**
     * Store a newly created schedule.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_type' => 'required|in:prepayment,accrual',
            'nature' => 'required|in:expense,income',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_amount' => 'required|numeric|min:0.01',
            'expense_income_account_id' => 'required|exists:chart_accounts,id',
            'balance_sheet_account_id' => 'required|exists:chart_accounts,id',
            'frequency' => 'required|in:monthly,quarterly,custom',
            'custom_periods' => 'nullable|integer|min:1',
            'vendor_id' => 'nullable|exists:suppliers,id',
            'customer_id' => 'nullable|exists:customers,id',
            'currency_code' => 'required|string|size:3',
            'description' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'branch_id' => 'nullable|exists:branches,id',
            // Payment fields (required for prepayments)
            'payment_method' => 'nullable|required_if:schedule_type,prepayment|in:bank,cash',
            'bank_account_id' => 'nullable|required_if:payment_method,bank|exists:bank_accounts,id',
            'payment_date' => 'nullable|date',
        ]);
        
        $user = Auth::user();
        $companyId = $user->company_id;
        
        DB::beginTransaction();
        try {
            // Get FX rate at creation
            $fxRate = $this->scheduleService->getFxRate($validated['currency_code'], Carbon::parse($validated['start_date']));
            $homeCurrencyAmount = $validated['total_amount'] * $fxRate;
            
            $schedule = new AccrualSchedule();
            $schedule->schedule_number = $this->scheduleService->generateScheduleNumber($companyId);
            $schedule->schedule_type = $validated['schedule_type'];
            $schedule->nature = $validated['nature'];
            $schedule->start_date = $validated['start_date'];
            $schedule->end_date = $validated['end_date'];
            $schedule->total_amount = $validated['total_amount'];
            $schedule->remaining_amount = $validated['total_amount'];
            $schedule->amortised_amount = 0;
            $schedule->expense_income_account_id = $validated['expense_income_account_id'];
            $schedule->balance_sheet_account_id = $validated['balance_sheet_account_id'];
            $schedule->frequency = $validated['frequency'];
            $schedule->custom_periods = $validated['custom_periods'] ?? null;
            $schedule->vendor_id = $validated['vendor_id'] ?? null;
            $schedule->customer_id = $validated['customer_id'] ?? null;
            $schedule->currency_code = $validated['currency_code'];
            $schedule->payment_method = $validated['payment_method'] ?? null;
            $schedule->bank_account_id = $validated['bank_account_id'] ?? null;
            $schedule->payment_date = $validated['payment_date'] ?? $validated['start_date'];
            $schedule->fx_rate_at_creation = $fxRate;
            $schedule->home_currency_amount = $homeCurrencyAmount;
            $schedule->description = $validated['description'];
            $schedule->notes = $validated['notes'] ?? null;
            $schedule->prepared_by = $user->id;
            $schedule->company_id = $companyId;
            $schedule->branch_id = $validated['branch_id'] ?? null;
            $schedule->created_by = $user->id;
            $schedule->status = 'draft';
            $schedule->save();
            
            // Generate amortisation schedule and journals
            $this->scheduleService->generateJournals($schedule);
            
            DB::commit();
            
            return redirect()->route('accounting.accruals-prepayments.show', $schedule->encoded_id)
                ->with('success', 'Accrual schedule created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create schedule: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified schedule.
     */
    public function show($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }
        
        $schedule = AccrualSchedule::with([
            'branch', 'vendor', 'customer', 'expenseIncomeAccount', 'balanceSheetAccount',
            'preparedBy', 'approvedBy', 'createdBy',
            'journals.journal', 'approvals.approver'
        ])->findOrFail($id);
        
        if ($schedule->company_id != Auth::user()->company_id) {
            abort(403);
        }
        
        // Get amortisation schedule preview
        $amortisationSchedule = $this->scheduleService->calculateAmortisationSchedule($schedule);
        
        return view('accounting.accruals-prepayments.show', compact('schedule', 'amortisationSchedule'));
    }

    /**
     * Show the form for editing the specified schedule.
     */
    public function edit($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }
        
        $schedule = AccrualSchedule::findOrFail($id);
        
        if ($schedule->company_id != Auth::user()->company_id) {
            abort(403);
        }
        
        if (!$schedule->canBeEdited()) {
            return redirect()->route('accounting.accruals-prepayments.show', $schedule->encoded_id)
                ->with('error', 'This schedule cannot be edited.');
        }
        
        $user = Auth::user();
        $companyId = $user->company_id;
        
        $branches = Branch::where('company_id', $companyId)->get();
        $suppliers = Supplier::where('company_id', $companyId)->get();
        $customers = Customer::where('company_id', $companyId)->get();
        
        // Get P&L accounts (expense/income accounts) using joins
        $expenseIncomeAccounts = ChartAccount::join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $companyId)
            ->whereIn('account_class.name', ['expense', 'expenses', 'income', 'revenue'])
            ->select('chart_accounts.*')
            ->orderBy('chart_accounts.account_code')
            ->get();
        
        // Get balance sheet accounts (asset/liability accounts) using joins
        $balanceSheetAccounts = ChartAccount::join('account_class_groups', 'chart_accounts.account_class_group_id', '=', 'account_class_groups.id')
            ->join('account_class', 'account_class_groups.class_id', '=', 'account_class.id')
            ->where('account_class_groups.company_id', $companyId)
            ->whereIn('account_class.name', ['assets', 'liabilities'])
            ->select('chart_accounts.*')
            ->orderBy('chart_accounts.account_code')
            ->get();
        
        return view('accounting.accruals-prepayments.edit', compact(
            'schedule', 'branches', 'suppliers', 'customers', 'expenseIncomeAccounts', 'balanceSheetAccounts'
        ));
    }

    /**
     * Update the specified schedule.
     */
    public function update(Request $request, $encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }
        
        $schedule = AccrualSchedule::findOrFail($id);
        
        if ($schedule->company_id != Auth::user()->company_id) {
            abort(403);
        }
        
        if (!$schedule->canBeEdited()) {
            return redirect()->route('accounting.accruals-prepayments.show', $schedule->encoded_id)
                ->with('error', 'This schedule cannot be edited.');
        }
        
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_amount' => 'required|numeric|min:0.01',
            'expense_income_account_id' => 'required|exists:chart_accounts,id',
            'balance_sheet_account_id' => 'required|exists:chart_accounts,id',
            'frequency' => 'required|in:monthly,quarterly,custom',
            'custom_periods' => 'nullable|integer|min:1',
            'vendor_id' => 'nullable|exists:suppliers,id',
            'customer_id' => 'nullable|exists:customers,id',
            'description' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'branch_id' => 'nullable|exists:branches,id',
        ]);
        
        DB::beginTransaction();
        try {
            $schedule->start_date = $validated['start_date'];
            $schedule->end_date = $validated['end_date'];
            $schedule->total_amount = $validated['total_amount'];
            $schedule->expense_income_account_id = $validated['expense_income_account_id'];
            $schedule->balance_sheet_account_id = $validated['balance_sheet_account_id'];
            $schedule->frequency = $validated['frequency'];
            $schedule->custom_periods = $validated['custom_periods'] ?? null;
            $schedule->vendor_id = $validated['vendor_id'] ?? null;
            $schedule->customer_id = $validated['customer_id'] ?? null;
            $schedule->description = $validated['description'];
            $schedule->notes = $validated['notes'] ?? null;
            $schedule->branch_id = $validated['branch_id'] ?? null;
            $schedule->updated_by = Auth::id();
            $schedule->save();
            
            // Recalculate schedule
            $this->scheduleService->recalculateSchedule($schedule);
            
            DB::commit();
            
            return redirect()->route('accounting.accruals-prepayments.show', $schedule->encoded_id)
                ->with('success', 'Schedule updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update schedule: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified schedule.
     */
    public function destroy($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }
        
        $schedule = AccrualSchedule::findOrFail($id);
        
        if ($schedule->company_id != Auth::user()->company_id) {
            abort(403);
        }
        
        // Check if schedule can be deleted (only draft and submitted, not approved/active)
        if (!$schedule->canBeCancelled()) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This schedule cannot be deleted. Only draft and submitted schedules can be deleted.'
                ], 403);
            }
            return back()->with('error', 'This schedule cannot be deleted. Only draft and submitted schedules can be deleted.');
        }
        
        // Only allow deletion if no journals are posted
        $postedJournals = AccrualJournal::where('accrual_schedule_id', $schedule->id)
            ->where('status', 'posted')
            ->count();
        
        if ($postedJournals > 0) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete schedule with posted journals. Cancel it instead.'
                ], 403);
            }
            return back()->with('error', 'Cannot delete schedule with posted journals. Cancel it instead.');
        }
        
        // Check if initial journal exists (for prepayments) - means it's been approved
        if ($schedule->initial_journal_id) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete schedule with initial payment journal. This schedule has been approved and activated.'
                ], 403);
            }
            return back()->with('error', 'Cannot delete schedule with initial payment journal. This schedule has been approved and activated.');
        }
        
        try {
            DB::beginTransaction();
            
            // Delete all pending journals first
            AccrualJournal::where('accrual_schedule_id', $schedule->id)
                ->where('status', 'pending')
                ->delete();
            
            // Delete the schedule
            $schedule->delete();
            
            DB::commit();
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Schedule deleted successfully.'
                ]);
            }
        
        return redirect()->route('accounting.accruals-prepayments.index')
            ->with('success', 'Schedule deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete schedule: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to delete schedule: ' . $e->getMessage());
        }
    }

    /**
     * Submit schedule for approval
     */
    public function submit($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }
        
        $schedule = AccrualSchedule::findOrFail($id);
        
        if ($schedule->status !== 'draft') {
            return back()->with('error', 'Only draft schedules can be submitted.');
        }
        
        $schedule->status = 'submitted';
        $schedule->save();
        
        return back()->with('success', 'Schedule submitted for approval.');
    }

    /**
     * Approve schedule
     */
    public function approve($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }
        
        $schedule = AccrualSchedule::findOrFail($id);
        
        if (!$schedule->canBeApproved()) {
            return back()->with('error', 'Schedule cannot be approved.');
        }
        
        $schedule->status = 'approved';
        $schedule->approved_by = Auth::id();
        $schedule->approved_at = now();
        $schedule->save();
        
        // Create initial payment/receipt journal entry for prepayments
        if ($schedule->schedule_type === 'prepayment') {
            try {
                $this->scheduleService->createInitialPaymentJournal($schedule);
            } catch (\Exception $e) {
                return back()->with('error', 'Schedule approved but failed to create initial payment journal: ' . $e->getMessage());
            }
        }
        
        // Activate schedule
        $schedule->status = 'active';
        $schedule->save();
        
        return back()->with('success', 'Schedule approved and activated.');
    }

    /**
     * Reject schedule
     */
    public function reject($encodedId, Request $request)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }
        
        $schedule = AccrualSchedule::findOrFail($id);
        
        if ($schedule->status !== 'submitted') {
            return back()->with('error', 'Only submitted schedules can be rejected.');
        }
        
        $schedule->status = 'draft';
        $schedule->save();
        
        return back()->with('success', 'Schedule rejected and returned to draft.');
    }

    /**
     * Post a journal to GL
     */
    public function postJournal($encodedId, $journalId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }
        
        $schedule = AccrualSchedule::findOrFail($id);
        $journal = AccrualJournal::findOrFail($journalId);
        
        if ($journal->accrual_schedule_id != $schedule->id) {
            abort(404);
        }
        
        if ($journal->status === 'posted') {
            return back()->with('error', 'Journal already posted.');
        }
        
        try {
            $this->scheduleService->postJournal($journal);
            return back()->with('success', 'Journal posted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to post journal: ' . $e->getMessage());
        }
    }

    /**
     * Post all pending journals
     */
    public function postAllPending($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }
        
        $schedule = AccrualSchedule::findOrFail($id);
        
        $pendingJournals = AccrualJournal::where('accrual_schedule_id', $schedule->id)
            ->where('status', 'pending')
            ->get();
        
        $posted = 0;
        $errors = [];
        
        foreach ($pendingJournals as $journal) {
            try {
                $this->scheduleService->postJournal($journal);
                $posted++;
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        
        if ($posted > 0) {
            return back()->with('success', "Posted {$posted} journal(s) successfully.");
        } else {
            return back()->with('error', 'Failed to post journals: ' . implode(', ', $errors));
        }
    }

    /**
     * Get amortisation schedule preview
     */
    public function amortisationSchedule($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }
        
        $schedule = AccrualSchedule::findOrFail($id);
        
        $amortisationSchedule = $this->scheduleService->calculateAmortisationSchedule($schedule);
        
        return response()->json($amortisationSchedule);
    }

    /**
     * Export schedule to PDF
     */
    public function exportPdf($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }
        
        $schedule = AccrualSchedule::with([
            'branch', 'vendor', 'customer', 'expenseIncomeAccount', 'balanceSheetAccount',
            'preparedBy', 'approvedBy', 'journals.journal'
        ])->findOrFail($id);
        
        if ($schedule->company_id != Auth::user()->company_id) {
            abort(403);
        }
        
        $amortisationSchedule = $this->scheduleService->calculateAmortisationSchedule($schedule);
        
        $company = $schedule->company;
        $branch = $schedule->branch;
        $user = Auth::user();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('accounting.accruals-prepayments.exports.pdf', compact(
            'schedule', 'amortisationSchedule', 'company', 'branch', 'user'
        ))->setPaper('a4', 'portrait');
        
        return $pdf->download('accrual-schedule-' . $schedule->schedule_number . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export schedule to Excel
     */
    public function exportExcel($encodedId)
    {
        $id = Hashids::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }
        
        $schedule = AccrualSchedule::with([
            'branch', 'vendor', 'customer', 'expenseIncomeAccount', 'balanceSheetAccount',
            'preparedBy', 'approvedBy', 'journals.journal'
        ])->findOrFail($id);
        
        if ($schedule->company_id != Auth::user()->company_id) {
            abort(403);
        }
        
        $amortisationSchedule = $this->scheduleService->calculateAmortisationSchedule($schedule);
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AccrualScheduleExport($schedule, $amortisationSchedule),
            'accrual-schedule-' . $schedule->schedule_number . '-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
