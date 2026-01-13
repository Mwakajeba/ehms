<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\StudentPrepaidAccount;
use App\Models\School\Student;
use App\Models\School\Classe;
use App\Models\School\Stream;
use App\Models\BankAccount;
use App\Models\GlTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PrepaidAccountTemplateExport;
use App\Imports\PrepaidAccountImport;

class PrepaidAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Get statistics
        $totalAccounts = StudentPrepaidAccount::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->count();
        
        $totalCredit = StudentPrepaidAccount::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->sum('credit_balance');

        // Get classes for filter
        $classes = Classe::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->orderBy('name')
            ->get();

        return view('school.prepaid-accounts.index', compact(
            'totalAccounts',
            'totalCredit',
            'classes'
        ));
    }

    /**
     * Get data for DataTables
     */
    public function data(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        $query = StudentPrepaidAccount::with(['student.class', 'student.stream'])
            ->forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId));

        // Apply filters
        if ($request->has('class_id') && $request->class_id) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('student_name', function ($account) {
                return $account->student->first_name . ' ' . $account->student->last_name;
            })
            ->addColumn('admission_number', function ($account) {
                return $account->student->admission_number ?? 'N/A';
            })
            ->addColumn('class_name', function ($account) {
                return $account->student->class->name ?? 'N/A';
            })
            ->addColumn('stream_name', function ($account) {
                return $account->student->stream->name ?? '-';
            })
            ->addColumn('credit_balance_formatted', function ($account) {
                return number_format($account->credit_balance, 2);
            })
            ->addColumn('total_deposited_formatted', function ($account) {
                return number_format($account->total_deposited, 2);
            })
            ->addColumn('total_used_formatted', function ($account) {
                return number_format($account->total_used, 2);
            })
            ->addColumn('actions', function ($account) {
                $account->hashid = \Vinkla\Hashids\Facades\Hashids::encode($account->id);
                return view('school.prepaid-accounts.partials.actions', compact('account'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        $classes = Classe::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->orderBy('name')
            ->get();

        // Get bank accounts
        $bankAccounts = BankAccount::with('chartAccount')
            ->where(function($query) use ($companyId) {
                $query->whereHas('chartAccount.accountClassGroup', function ($subQuery) use ($companyId) {
                    $subQuery->where('company_id', $companyId);
                })
                ->orWhere('company_id', $companyId);
            })
            ->orderBy('name')
            ->get();

        return view('school.prepaid-accounts.create', compact('classes', 'bankAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;
        $userId = auth()->id();

        // Verify student belongs to company/branch
        $student = Student::where('id', $request->student_id)
            ->where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->firstOrFail();

        // Verify bank account belongs to company
        $bankAccount = BankAccount::where('id', $request->bank_account_id)
            ->where(function($query) use ($companyId) {
                $query->whereHas('chartAccount.accountClassGroup', function ($subQuery) use ($companyId) {
                    $subQuery->where('company_id', $companyId);
                })
                ->orWhere('company_id', $companyId);
            })
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $account = StudentPrepaidAccount::getOrCreateForStudent(
                $student->id,
                $companyId,
                $branchId
            );

            // Add credit to account
            $transaction = $account->addCredit(
                $request->amount,
                $request->reference,
                null,
                $request->notes
            );

            // Get prepaid chart account from settings
            $prepaidAccountId = \App\Models\SystemSetting::getValue('prepaid_chart_account_id', null);
            if (!$prepaidAccountId) {
                throw new \Exception('Prepaid chart account not configured. Please set it in settings.');
            }

            // Create GL transactions
            // 1. Debit Bank Account
            GlTransaction::create([
                'chart_account_id' => $bankAccount->chart_account_id,
                'customer_id' => null,
                'supplier_id' => null,
                'amount' => $request->amount,
                'nature' => 'debit',
                'transaction_id' => $transaction->id,
                'transaction_type' => 'student_prepaid_deposit',
                'date' => now(),
                'description' => $request->notes ?? "Prepaid account deposit for student {$student->first_name} {$student->last_name}",
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);

            // 2. Credit Prepaid Account
            GlTransaction::create([
                'chart_account_id' => $prepaidAccountId,
                'customer_id' => null,
                'supplier_id' => null,
                'amount' => $request->amount,
                'nature' => 'credit',
                'transaction_id' => $transaction->id,
                'transaction_type' => 'student_prepaid_deposit',
                'date' => now(),
                'description' => $request->notes ?? "Prepaid account deposit for student {$student->first_name} {$student->last_name}",
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);

            // Automatically apply credit to unpaid invoices
            $autoApplyResult = $account->autoApplyCreditToUnpaidInvoices();

            DB::commit();

            $successMessage = 'Prepaid account credit added successfully.';
            if ($autoApplyResult['applied']) {
                $successMessage .= ' ' . $autoApplyResult['message'];
            }

            return redirect()->route('school.prepaid-accounts.index')
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to add credit: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the import form.
     */
    public function import()
    {
        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        $classes = Classe::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->orderBy('name')
            ->get();

        // Get bank accounts
        $bankAccounts = BankAccount::with('chartAccount')
            ->where(function($query) use ($companyId) {
                $query->whereHas('chartAccount.accountClassGroup', function ($subQuery) use ($companyId) {
                    $subQuery->where('company_id', $companyId);
                })
                ->orWhere('company_id', $companyId);
            })
            ->orderBy('name')
            ->get();

        return view('school.prepaid-accounts.import', compact('classes', 'bankAccounts'));
    }

    /**
     * Export Excel template for import.
     */
    public function exportTemplate(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Verify class belongs to company/branch
        $class = Classe::where('id', $request->class_id)
            ->where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->firstOrFail();

        return Excel::download(new PrepaidAccountTemplateExport($class), 'prepaid_account_template.xlsx');
    }

    /**
     * Process the import.
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
            'class_id' => 'required|exists:classes,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
        ]);

        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;
        $userId = auth()->id();

        // Verify class belongs to company/branch
        $class = Classe::where('id', $request->class_id)
            ->where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->firstOrFail();

        // Verify bank account belongs to company
        $bankAccount = BankAccount::where('id', $request->bank_account_id)
            ->where(function($query) use ($companyId) {
                $query->whereHas('chartAccount.accountClassGroup', function ($subQuery) use ($companyId) {
                    $subQuery->where('company_id', $companyId);
                })
                ->orWhere('company_id', $companyId);
            })
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $import = new PrepaidAccountImport($class, $companyId, $branchId, $bankAccount->id, $userId);
            Excel::import($import, $request->file('excel_file'));

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            DB::commit();

            if ($errorCount > 0) {
                return redirect()->back()
                    ->with('warning', "Import completed with {$successCount} successful and {$errorCount} errors.")
                    ->with('import_errors', $errors);
            }

            return redirect()->route('school.prepaid-accounts.index')
                ->with('success', "Successfully imported {$successCount} prepaid account records.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Show prepaid account settings.
     */
    public function settings()
    {
        $companyId = auth()->user()->company_id;

        // Get current settings
        $autoApplyCredit = \App\Models\SystemSetting::getValue('prepaid_auto_apply_credit', true);
        $applyCreditOrder = \App\Models\SystemSetting::getValue('prepaid_apply_credit_order', 'oldest_first');
        $prepaidChartAccountId = \App\Models\SystemSetting::getValue('prepaid_chart_account_id', null);

        // Get chart accounts (liability accounts for student prepaid credits)
        $chartAccounts = \App\Models\ChartAccount::whereHas('accountClassGroup', function($q) use ($companyId) {
                $q->where('company_id', $companyId)
                  ->whereHas('accountClass', function($q2) {
                      $q2->where('name', 'LIKE', '%liabilit%');
                  });
            })
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name']);

        return view('school.prepaid-accounts.settings', compact(
            'autoApplyCredit',
            'applyCreditOrder',
            'prepaidChartAccountId',
            'chartAccounts'
        ));
    }

    /**
     * Update prepaid account settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'auto_apply_credit' => 'nullable|boolean',
            'apply_credit_order' => 'nullable|in:oldest_first,newest_first',
            'prepaid_chart_account_id' => 'nullable|exists:chart_accounts,id',
        ]);

        // Store settings in system_settings
        \App\Models\SystemSetting::setValue(
            'prepaid_auto_apply_credit',
            $request->has('auto_apply_credit') ? 1 : 0,
            'boolean',
            'prepaid_accounts',
            'Auto Apply Credit',
            'Automatically apply available credit to new invoices'
        );

        \App\Models\SystemSetting::setValue(
            'prepaid_apply_credit_order',
            $request->apply_credit_order ?? 'oldest_first',
            'string',
            'prepaid_accounts',
            'Credit Application Order',
            'Order in which credit is applied (oldest_first or newest_first)'
        );

        if ($request->prepaid_chart_account_id) {
            \App\Models\SystemSetting::setValue(
                'prepaid_chart_account_id',
                $request->prepaid_chart_account_id,
                'integer',
                'prepaid_accounts',
                'Prepaid Chart Account',
                'Chart account for student prepaid credit balances'
            );
        }

        return redirect()->route('school.prepaid-accounts.settings')
            ->with('success', 'Settings updated successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($hashId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($hashId)[0] ?? null;
        
        $account = StudentPrepaidAccount::with(['student.class', 'student.stream', 'transactions.feeInvoice', 'transactions.creator'])
            ->findOrFail($id);

        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Verify account belongs to user's company/branch
        if ($account->company_id != $companyId) {
            abort(403, 'Unauthorized access.');
        }

        return view('school.prepaid-accounts.show', compact('account'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($hashId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($hashId)[0] ?? null;
        
        $account = StudentPrepaidAccount::with(['student.class', 'student.stream'])
            ->findOrFail($id);

        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Verify account belongs to user's company/branch
        if ($account->company_id != $companyId) {
            abort(403, 'Unauthorized access.');
        }

        // Get classes for dropdown
        $classes = Classe::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->orderBy('name')
            ->get();

        // Get students for the selected class
        $students = Student::where('class_id', $account->student->class_id)
            ->where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'admission_number']);

        // Get bank accounts
        $bankAccounts = BankAccount::with('chartAccount')
            ->where(function($query) use ($companyId) {
                $query->whereHas('chartAccount.accountClassGroup', function ($subQuery) use ($companyId) {
                    $subQuery->where('company_id', $companyId);
                })
                ->orWhere('company_id', $companyId);
            })
            ->orderBy('name')
            ->get();

        // Get all deposit transactions with their bank account information
        $depositTransactions = \App\Models\School\StudentPrepaidAccountTransaction::where('prepaid_account_id', $account->id)
            ->where('type', 'deposit')
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($transaction) use ($companyId) {
                // Get the bank account from GL transaction (debit transaction)
                $glTransaction = GlTransaction::where('transaction_type', 'student_prepaid_deposit')
                    ->where('transaction_id', $transaction->id)
                    ->where('nature', 'debit')
                    ->first();
                
                $bankAccountId = null;
                if ($glTransaction) {
                    $bankAccount = BankAccount::where('chart_account_id', $glTransaction->chart_account_id)
                        ->where(function($query) use ($companyId) {
                            $query->whereHas('chartAccount.accountClassGroup', function ($subQuery) use ($companyId) {
                                $subQuery->where('company_id', $companyId);
                            })
                            ->orWhere('company_id', $companyId);
                        })
                        ->first();
                    
                    if ($bankAccount) {
                        $bankAccountId = $bankAccount->id;
                    }
                }
                
                return [
                    'transaction' => $transaction,
                    'bank_account_id' => $bankAccountId,
                ];
            });

        return view('school.prepaid-accounts.edit', compact('account', 'classes', 'students', 'bankAccounts', 'depositTransactions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $hashId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($hashId)[0] ?? null;
        
        $account = StudentPrepaidAccount::with('student')->findOrFail($id);

        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;
        $userId = auth()->id();

        // Verify account belongs to user's company/branch
        if ($account->company_id != $companyId) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'transactions' => 'required|array',
            'transactions.*.id' => 'nullable|exists:student_prepaid_account_transactions,id',
            'transactions.*.amount' => 'required|numeric|min:0',
            'transactions.*.bank_account_id' => 'required|exists:bank_accounts,id',
            'transactions.*.reference' => 'nullable|string|max:255',
            'transactions.*.notes' => 'nullable|string',
            'transactions.*.delete' => 'nullable|boolean',
        ]);

        // Get prepaid chart account from settings
        $prepaidAccountId = \App\Models\SystemSetting::getValue('prepaid_chart_account_id', null);
        if (!$prepaidAccountId) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Prepaid chart account not configured. Please set it in settings.']);
        }

        DB::beginTransaction();
        try {
            $totalDeposited = 0;
            $transactionsToDelete = [];

            // Process each transaction
            foreach ($request->transactions as $index => $transactionData) {
                // Check if transaction should be deleted
                if (isset($transactionData['delete']) && $transactionData['delete']) {
                    if (isset($transactionData['id']) && $transactionData['id']) {
                        $transactionsToDelete[] = $transactionData['id'];
                    }
                    continue;
                }

                // Verify bank account belongs to company
                $bankAccount = BankAccount::where('id', $transactionData['bank_account_id'])
                    ->where(function($query) use ($companyId) {
                        $query->whereHas('chartAccount.accountClassGroup', function ($subQuery) use ($companyId) {
                            $subQuery->where('company_id', $companyId);
                        })
                        ->orWhere('company_id', $companyId);
                    })
                    ->first();

                if (!$bankAccount) {
                    throw new \Exception("Invalid bank account selected for transaction #" . ($index + 1));
                }

                $amount = (float) $transactionData['amount'];
                $totalDeposited += $amount;

                if (isset($transactionData['id']) && $transactionData['id']) {
                    // Update existing transaction
                    $transaction = \App\Models\School\StudentPrepaidAccountTransaction::find($transactionData['id']);
                    
                    if ($transaction && $transaction->prepaid_account_id == $account->id && $transaction->type == 'deposit') {
                        $oldAmount = $transaction->amount;
                        $amountDiff = $amount - $oldAmount;

                        // Update transaction
                        $transaction->update([
                            'amount' => $amount,
                            'reference' => $transactionData['reference'] ?? null,
                            'notes' => $transactionData['notes'] ?? null,
                        ]);

                        // Get existing GL transactions
                        $debitGl = GlTransaction::where('transaction_type', 'student_prepaid_deposit')
                            ->where('transaction_id', $transaction->id)
                            ->where('nature', 'debit')
                            ->first();

                        $creditGl = GlTransaction::where('transaction_type', 'student_prepaid_deposit')
                            ->where('transaction_id', $transaction->id)
                            ->where('nature', 'credit')
                            ->first();

                        if ($debitGl && $creditGl) {
                            // Check if bank account changed
                            if ($debitGl->chart_account_id != $bankAccount->chart_account_id) {
                                // Delete old GL transactions
                                $debitGl->delete();
                                $creditGl->delete();

                                // Create new GL transactions
                                GlTransaction::create([
                                    'chart_account_id' => $bankAccount->chart_account_id,
                                    'customer_id' => null,
                                    'supplier_id' => null,
                                    'amount' => $amount,
                                    'nature' => 'debit',
                                    'transaction_id' => $transaction->id,
                                    'transaction_type' => 'student_prepaid_deposit',
                                    'date' => $transaction->created_at,
                                    'description' => $transactionData['notes'] ?? "Prepaid account deposit for student {$account->student->first_name} {$account->student->last_name}",
                                    'branch_id' => $branchId,
                                    'user_id' => $userId,
                                ]);

                                GlTransaction::create([
                                    'chart_account_id' => $prepaidAccountId,
                                    'customer_id' => null,
                                    'supplier_id' => null,
                                    'amount' => $amount,
                                    'nature' => 'credit',
                                    'transaction_id' => $transaction->id,
                                    'transaction_type' => 'student_prepaid_deposit',
                                    'date' => $transaction->created_at,
                                    'description' => $transactionData['notes'] ?? "Prepaid account deposit for student {$account->student->first_name} {$account->student->last_name}",
                                    'branch_id' => $branchId,
                                    'user_id' => $userId,
                                ]);
                            } else {
                                // Update amounts if changed
                                if ($amountDiff != 0) {
                                    $debitGl->update(['amount' => $amount]);
                                    $creditGl->update(['amount' => $amount]);
                                }

                                // Update descriptions
                                $description = $transactionData['notes'] ?? "Prepaid account deposit for student {$account->student->first_name} {$account->student->last_name}";
                                $debitGl->update(['description' => $description]);
                                $creditGl->update(['description' => $description]);
                            }
                        }
                    }
                } else {
                    // Create new transaction
                    $balanceBefore = $account->credit_balance;
                    $transaction = $account->transactions()->create([
                        'type' => 'deposit',
                        'amount' => $amount,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceBefore + $amount,
                        'reference' => $transactionData['reference'] ?? null,
                        'notes' => $transactionData['notes'] ?? null,
                        'created_by' => $userId,
                    ]);

                    // Create GL transactions
                    GlTransaction::create([
                        'chart_account_id' => $bankAccount->chart_account_id,
                        'customer_id' => null,
                        'supplier_id' => null,
                        'amount' => $amount,
                        'nature' => 'debit',
                        'transaction_id' => $transaction->id,
                        'transaction_type' => 'student_prepaid_deposit',
                        'date' => now(),
                        'description' => $transactionData['notes'] ?? "Prepaid account deposit for student {$account->student->first_name} {$account->student->last_name}",
                        'branch_id' => $branchId,
                        'user_id' => $userId,
                    ]);

                    GlTransaction::create([
                        'chart_account_id' => $prepaidAccountId,
                        'customer_id' => null,
                        'supplier_id' => null,
                        'amount' => $amount,
                        'nature' => 'credit',
                        'transaction_id' => $transaction->id,
                        'transaction_type' => 'student_prepaid_deposit',
                        'date' => now(),
                        'description' => $transactionData['notes'] ?? "Prepaid account deposit for student {$account->student->first_name} {$account->student->last_name}",
                        'branch_id' => $branchId,
                        'user_id' => $userId,
                    ]);
                }
            }

            // Delete marked transactions
            if (!empty($transactionsToDelete)) {
                foreach ($transactionsToDelete as $transactionId) {
                    $transaction = \App\Models\School\StudentPrepaidAccountTransaction::find($transactionId);
                    if ($transaction && $transaction->prepaid_account_id == $account->id && $transaction->type == 'deposit') {
                        // Delete GL transactions
                        GlTransaction::where('transaction_type', 'student_prepaid_deposit')
                            ->where('transaction_id', $transaction->id)
                            ->delete();

                        // Delete transaction
                        $transaction->delete();
                    }
                }
            }

            // Recalculate account balances
            $account->refresh();
            $allDeposits = $account->transactions()->where('type', 'deposit')->sum('amount');
            $allWithdrawals = $account->transactions()->where('type', 'invoice_application')->sum('amount');
            
            $account->total_deposited = $allDeposits;
            $account->total_used = $allWithdrawals;
            $account->credit_balance = $allDeposits - $allWithdrawals;
            $account->updated_by = $userId;
            $account->save();

            // Recalculate balance_after for all transactions in chronological order
            $transactions = $account->transactions()->orderBy('created_at')->get();
            $runningBalance = 0;
            foreach ($transactions as $trans) {
                $trans->balance_before = $runningBalance;
                if ($trans->type == 'deposit') {
                    $runningBalance += $trans->amount;
                } else {
                    $runningBalance -= $trans->amount;
                }
                $trans->balance_after = $runningBalance;
                $trans->save();
            }

            // Automatically apply credit to unpaid invoices if new deposits were added
            $autoApplyResult = $account->autoApplyCreditToUnpaidInvoices();

            DB::commit();

            $successMessage = 'Transactions updated successfully.';
            if ($autoApplyResult['applied']) {
                $successMessage .= ' ' . $autoApplyResult['message'];
            }

            return redirect()->route('school.prepaid-accounts.show', $account->hashid)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update transactions: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($hashId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($hashId)[0] ?? null;
        
        $account = StudentPrepaidAccount::with('student')->findOrFail($id);

        $companyId = auth()->user()->company_id;

        // Verify account belongs to user's company
        if ($account->company_id != $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        // Get all transaction IDs for this account
        $transactionIds = $account->transactions()->pluck('id')->toArray();
        
        DB::beginTransaction();
        try {
            // Delete all GL transactions related to this account's transactions
            // This includes both deposit and invoice_application GL transactions
            if (!empty($transactionIds)) {
                // Delete deposit GL transactions
                GlTransaction::where('transaction_type', 'student_prepaid_deposit')
                    ->whereIn('transaction_id', $transactionIds)
                    ->delete();

                // Also delete any invoice application GL transactions if they exist
                // (These would be created when credit is applied to invoices)
                // Note: Invoice application GL transactions might have different transaction_type
                // We'll delete all GL transactions linked to these transaction IDs
                GlTransaction::whereIn('transaction_id', $transactionIds)
                    ->where(function($query) {
                        $query->where('transaction_type', 'student_prepaid_deposit')
                              ->orWhere('transaction_type', 'like', '%prepaid%');
                    })
                    ->delete();
            }

            // Delete all prepaid account transactions for this student
            $account->transactions()->delete();

            // Delete the prepaid account
            $account->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Prepaid account and all related transactions deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to delete prepaid account', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add credit to existing account (via AJAX/modal).
     */
    public function addCredit(Request $request, $encodedId)
    {
        $id = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;
        
        $account = StudentPrepaidAccount::with('student')->findOrFail($id);

        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;
        $userId = auth()->id();

        // Verify account belongs to user's company
        if ($account->company_id != $companyId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Verify bank account belongs to company
        $bankAccount = BankAccount::where('id', $request->bank_account_id)
            ->where(function($query) use ($companyId) {
                $query->whereHas('chartAccount.accountClassGroup', function ($subQuery) use ($companyId) {
                    $subQuery->where('company_id', $companyId);
                })
                ->orWhere('company_id', $companyId);
            })
            ->first();

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid bank account selected.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Add credit to account
            $transaction = $account->addCredit(
                $request->amount,
                $request->reference,
                null,
                $request->notes
            );

            // Get prepaid chart account from settings
            $prepaidAccountId = \App\Models\SystemSetting::getValue('prepaid_chart_account_id', null);
            if (!$prepaidAccountId) {
                throw new \Exception('Prepaid chart account not configured. Please set it in settings.');
            }

            // Create GL transactions
            // 1. Debit Bank Account
            GlTransaction::create([
                'chart_account_id' => $bankAccount->chart_account_id,
                'customer_id' => null,
                'supplier_id' => null,
                'amount' => $request->amount,
                'nature' => 'debit',
                'transaction_id' => $transaction->id,
                'transaction_type' => 'student_prepaid_deposit',
                'date' => now(),
                'description' => $request->notes ?? "Prepaid account deposit for student {$account->student->first_name} {$account->student->last_name}",
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);

            // 2. Credit Prepaid Account
            GlTransaction::create([
                'chart_account_id' => $prepaidAccountId,
                'customer_id' => null,
                'supplier_id' => null,
                'amount' => $request->amount,
                'nature' => 'credit',
                'transaction_id' => $transaction->id,
                'transaction_type' => 'student_prepaid_deposit',
                'date' => now(),
                'description' => $request->notes ?? "Prepaid account deposit for student {$account->student->first_name} {$account->student->last_name}",
                'branch_id' => $branchId,
                'user_id' => $userId,
            ]);

            // Automatically apply credit to unpaid invoices
            $autoApplyResult = $account->autoApplyCreditToUnpaidInvoices();

            DB::commit();

            $message = 'Credit added successfully.';
            if ($autoApplyResult['applied']) {
                $message .= ' ' . $autoApplyResult['message'];
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'auto_applied' => $autoApplyResult['applied'],
                'auto_applied_amount' => $autoApplyResult['total_applied'],
                'auto_applied_invoices' => $autoApplyResult['invoices_paid'],
                'account' => [
                    'credit_balance' => number_format($account->fresh()->credit_balance, 2),
                    'total_deposited' => number_format($account->fresh()->total_deposited, 2),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add credit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync GL transactions for prepaid account applications.
     * This fixes missing GL transactions when credit was used.
     */
    public function syncGlTransactions()
    {
        $prepaidAccountId = \App\Models\SystemSetting::getValue('prepaid_chart_account_id', null);
        if (!$prepaidAccountId) {
            return response()->json([
                'success' => false,
                'message' => 'Prepaid chart account not configured. Please set it in settings.'
            ], 400);
        }

        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        $accounts = StudentPrepaidAccount::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->with('transactions')
            ->get();

        $missingCount = 0;
        $fixedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($accounts as $account) {
                // Get all invoice application transactions
                $applicationTransactions = $account->transactions()
                    ->where('type', 'invoice_application')
                    ->get();

                foreach ($applicationTransactions as $trans) {
                    // Check if GL transactions exist for this application
                    $glTransactions = GlTransaction::where('transaction_type', 'student_prepaid_application')
                        ->where('transaction_id', $trans->id)
                        ->get();

                    if ($glTransactions->isEmpty()) {
                        $missingCount++;
                        
                        // Get the invoice
                        $invoice = null;
                        if ($trans->fee_invoice_id) {
                            $invoice = \App\Models\FeeInvoice::with('feeGroup')->find($trans->fee_invoice_id);
                        }

                        if ($invoice && $invoice->feeGroup) {
                            $receivableAccountId = $invoice->feeGroup->receivable_account_id ??
                                                 \App\Models\ChartAccount::where('account_name', 'LIKE', '%Trade Receivable%')
                                                     ->orWhere('account_name', 'LIKE', '%Accounts Receivable%')
                                                     ->value('id') ?? 18;

                            $userId = $trans->created_by ?? auth()->id();
                            $transBranchId = $account->branch_id ?? $branchId;

                            // Create GL transactions
                            // 1. Debit Prepaid Account
                            GlTransaction::create([
                                'chart_account_id' => $prepaidAccountId,
                                'customer_id' => null,
                                'supplier_id' => null,
                                'amount' => $trans->amount,
                                'nature' => 'debit',
                                'transaction_id' => $trans->id,
                                'transaction_type' => 'student_prepaid_application',
                                'date' => $trans->created_at ?? now(),
                                'description' => $trans->notes ?? "Prepaid credit applied to invoice {$invoice->invoice_number}",
                                'branch_id' => $transBranchId,
                                'user_id' => $userId,
                            ]);

                            // 2. Credit Accounts Receivable
                            GlTransaction::create([
                                'chart_account_id' => $receivableAccountId,
                                'customer_id' => null,
                                'supplier_id' => null,
                                'amount' => $trans->amount,
                                'nature' => 'credit',
                                'transaction_id' => $trans->id,
                                'transaction_type' => 'student_prepaid_application',
                                'date' => $trans->created_at ?? now(),
                                'description' => $trans->notes ?? "Prepaid credit applied to invoice {$invoice->invoice_number}",
                                'branch_id' => $transBranchId,
                                'user_id' => $userId,
                            ]);

                            $fixedCount++;
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Synced GL transactions. Found {$missingCount} missing transactions, fixed {$fixedCount}.",
                'missing_count' => $missingCount,
                'fixed_count' => $fixedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync GL transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get students by class for select dropdown.
     */
    public function getStudentsByClass(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        $students = Student::where('class_id', $request->class_id)
            ->where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'admission_number']);

        return response()->json($students);
    }

    /**
     * Bulk create prepaid accounts for all students in a class.
     */
    public function bulkCreate(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Verify class belongs to company/branch
        $class = Classe::where('id', $request->class_id)
            ->where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->firstOrFail();

        // Get current academic year
        $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->where('is_current', true)
            ->first();

        if (!$currentAcademicYear) {
            return response()->json([
                'success' => false,
                'message' => 'No current academic year found. Please set a current academic year first.'
            ], 400);
        }

        // Get all active students in the class for the current academic year
        $students = Student::where('class_id', $request->class_id)
            ->where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->where('status', 'active')
            ->where('academic_year_id', $currentAcademicYear->id)
            ->get();

        if ($students->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active students found in the selected class for the current academic year.'
            ], 400);
        }

        $createdCount = 0;
        $skippedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                try {
                    // Check if account already exists
                    $existingAccount = StudentPrepaidAccount::where('student_id', $student->id)
                        ->where('company_id', $companyId)
                        ->first();

                    if ($existingAccount) {
                        $skippedCount++;
                        continue;
                    }

                    // Create prepaid account with zero balance
                    StudentPrepaidAccount::create([
                        'student_id' => $student->id,
                        'credit_balance' => 0,
                        'total_deposited' => 0,
                        'total_used' => 0,
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                        'created_by' => auth()->id(),
                    ]);

                    $createdCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to create account for {$student->first_name} {$student->last_name}: " . $e->getMessage();
                    \Log::error('Failed to create prepaid account for student', [
                        'student_id' => $student->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            $message = "Created {$createdCount} prepaid account(s)";
            if ($skippedCount > 0) {
                $message .= ", {$skippedCount} account(s) already existed";
            }
            if (!empty($errors)) {
                $message .= ", " . count($errors) . " error(s) occurred";
            }

            return response()->json([
                'success' => true,
                'created_count' => $createdCount,
                'skipped_count' => $skippedCount,
                'error_count' => count($errors),
                'message' => $message,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create accounts: ' . $e->getMessage()
            ], 500);
        }
    }
}

