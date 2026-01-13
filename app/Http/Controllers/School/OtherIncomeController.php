<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\OtherIncome;
use App\Models\School\Student as SchoolStudent;
use App\Models\School\Classe;
use App\Models\ChartAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class OtherIncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        // Get statistics with better performance
        $statsQuery = OtherIncome::forCompany($companyId);

        // Only apply branch filter if branchId is not null
        if ($branchId) {
            $statsQuery->forBranch($branchId);
        }

        $totalIncome = (clone $statsQuery)->sum('amount');
        $approvedIncome = (clone $statsQuery)->approved()->sum('amount');
        $pendingIncome = (clone $statsQuery)->pending()->sum('amount');
        $todayIncome = (clone $statsQuery)->whereDate('transaction_date', today())->sum('amount');
        $thisMonthIncome = (clone $statsQuery)->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        // Get filter data
        $classes = \App\Models\School\Classe::forCompany($companyId)
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->orderBy('name')
            ->get();

        $incomeAccounts = ChartAccount::whereHas('accountClassGroup', function($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->whereBetween('account_code', [4000, 4999])
            ->orderBy('account_code')
            ->orderBy('account_name')
            ->get();

        return view('school.fee-management.other-income.index', compact(
            'totalIncome',
            'approvedIncome',
            'pendingIncome',
            'todayIncome',
            'thisMonthIncome',
            'classes',
            'incomeAccounts'
        ));
    }

    /**
     * Get data for DataTables
     */
    public function data(Request $request)
    {
        try {
            $companyId = auth()->user()->company_id;
            $branchId = session('branch_id') ?: auth()->user()->branch_id;

            $query = OtherIncome::with(['student.class', 'student.stream', 'incomeAccount', 'bankAccount', 'creator', 'approver'])
                ->forCompany($companyId);

            // Only apply branch filter if branchId is not null
            if ($branchId) {
                $query->forBranch($branchId);
            }

            // Apply filters with better validation
            if ($request->has('status') && $request->status !== '' && in_array($request->status, ['pending', 'approved', 'rejected'])) {
                $query->where('status', $request->status);
            }

            if ($request->has('income_type') && $request->income_type !== '' && in_array($request->income_type, ['student', 'other'])) {
                $query->where('income_type', $request->income_type);
            }

            if ($request->has('date_from') && !empty($request->date_from) && strtotime($request->date_from)) {
                $query->where('transaction_date', '>=', $request->date_from);
            }

            if ($request->has('date_to') && !empty($request->date_to) && strtotime($request->date_to)) {
                $query->where('transaction_date', '<=', $request->date_to);
            }

            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->whereHas('student', function($q) use ($request) {
                    $q->where('class_id', $request->class_id);
                });
            }

            if ($request->has('income_account_id') && !empty($request->income_account_id)) {
                $query->where('income_account_id', $request->income_account_id);
            }

            return DataTables::of($query)
                ->addColumn('student_name', function ($income) {
                    try {
                        return $income->income_type === 'student'
                            ? ($income->student->name ?? 'N/A')
                            : ($income->other_party ?? 'N/A');
                    } catch (\Exception $e) {
                        return 'Error loading data';
                    }
                })
                ->addColumn('student_class_stream', function ($income) {
                    try {
                        if ($income->income_type === 'student' && $income->student) {
                            $className = $income->student->class->name ?? 'N/A';
                            $streamName = $income->student->stream->name ?? '';
                            return $streamName ? $className . ' - ' . $streamName : $className;
                        }
                        return '-';
                    } catch (\Exception $e) {
                        return 'N/A';
                    }
                })
                ->addColumn('account_name', function ($income) {
                    try {
                        return $income->incomeAccount->account_name ?? 'N/A';
                    } catch (\Exception $e) {
                        return 'Error loading data';
                    }
                })
                ->addColumn('received_in_display', function ($income) {
                    try {
                        return $income->received_in_display;
                    } catch (\Exception $e) {
                        return 'N/A';
                    }
                })
                ->addColumn('creator_name', function ($income) {
                    try {
                        return $income->creator->name ?? 'N/A';
                    } catch (\Exception $e) {
                        return 'Error loading data';
                    }
                })
                ->addColumn('approver_name', function ($income) {
                    try {
                        return $income->approver->name ?? 'N/A';
                    } catch (\Exception $e) {
                        return 'Error loading data';
                    }
                })
                ->addColumn('formatted_amount', function ($income) {
                    return number_format($income->amount ?? 0, 2);
                })
                ->addColumn('actions', function ($income) {
                    $actions = '<div class="btn-group" role="group">';

                    $actions .= '<a href="' . route('school.other-income.show', $income) . '" class="btn btn-info btn-action btn-sm" title="View Details"><i class="bx bx-show"></i></a>';

                    $actions .= '<a href="' . route('school.other-income.edit', $income) . '" class="btn btn-primary btn-action btn-sm" title="Edit"><i class="bx bx-edit"></i></a>';

                    $actions .= '<a href="' . route('school.other-income.export-pdf', $income) . '" class="btn btn-secondary btn-action btn-sm" target="_blank" title="Export PDF"><i class="bx bx-file"></i></a>';

                    $actions .= '<a href="' . route('school.other-income.show', $income) . '?print=1" class="btn btn-warning btn-action btn-sm" target="_blank" title="Print"><i class="bx bx-printer"></i></a>';

                    if (auth()->user()->can('delete-other-income')) {
                        $actions .= '<button type="button" class="btn btn-danger btn-action btn-sm delete-income-btn" 
                                        data-id="' . $income->id . '" 
                                        data-url="' . route('school.other-income.destroy', $income) . '"
                                        title="Delete">
                                        <i class="bx bx-trash"></i>
                                    </button>';
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['actions'])
                ->make(true);
        } catch (\Exception $e) {
            \Log::error('OtherIncome DataTable error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);

            return response()->json([
                'error' => 'An error occurred while loading data. Please try again.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = SchoolStudent::forCompany(auth()->user()->company_id)
            ->forBranch(session('branch_id') ?: auth()->user()->branch_id)
            ->active()
            ->with(['class', 'stream'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $incomeAccounts = ChartAccount::whereHas('accountClassGroup', function($query) {
            $query->where('company_id', auth()->user()->company_id);
        })->whereBetween('account_code', [4000, 4999])
            ->orderBy('account_code')
            ->orderBy('account_name')
            ->get();

        $bankAccounts = BankAccount::orderBy('name')
            ->get();

        return view('school.fee-management.other-income.create', compact('students', 'incomeAccounts', 'bankAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Get valid bank account IDs for the company
        $validBankAccountIds = BankAccount::pluck('id')->toArray();

        $request->validate([
            'income_lines' => 'required|array|min:1',
            'income_lines.0.transaction_date' => 'required|date',
            'income_lines.0.income_type' => 'required|in:student,other',
            'income_lines.0.student_id' => 'required_if:income_lines.0.income_type,student|nullable|exists:students,id',
            'income_lines.0.other_party' => 'required_if:income_lines.0.income_type,other|nullable|string|max:255',
            'income_lines.0.description' => 'required|string|max:500',
            'income_lines.0.received_in' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($validBankAccountIds) {
                    if (!in_array((int) $value, $validBankAccountIds)) {
                        $fail('The selected bank account is invalid.');
                    }
                },
            ],
            'income_lines.*.income_account_id' => 'required|exists:chart_accounts,id',
            'income_lines.*.amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $createdRecords = [];

            // Get common values from first line
            $firstLine = $request->income_lines[0];
            $commonData = [
                'transaction_date' => $firstLine['transaction_date'],
                'income_type' => $firstLine['income_type'],
                'student_id' => $firstLine['income_type'] === 'student' ? ($firstLine['student_id'] ?? null) : null,
                'other_party' => $firstLine['income_type'] === 'other' ? ($firstLine['other_party'] ?? null) : null,
                'description' => $firstLine['description'],
                'received_in' => $firstLine['received_in'],
                'company_id' => auth()->user()->company_id,
                'branch_id' => session('branch_id') ?: auth()->user()->branch_id,
                'created_by' => auth()->id(),
            ];

            foreach ($request->income_lines as $line) {
                $income = OtherIncome::create(array_merge($commonData, [
                    'income_account_id' => $line['income_account_id'],
                    'amount' => $line['amount'],
                ]));

                $createdRecords[] = $income;
            }

            // Create GL transactions for all created income records
            foreach ($createdRecords as $income) {
                $income->createGlTransactions();
            }

            DB::commit();

            $count = count($createdRecords);
            return redirect()->route('school.other-income.index')
                ->with('success', "{$count} other income record(s) created successfully.");

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to create income records: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(OtherIncome $encodedId)
    {
        $this->authorize('view', $encodedId);

        $encodedId->load(['student.class', 'student.stream', 'incomeAccount', 'bankAccount', 'creator', 'approver', 'company', 'branch']);

        return view('school.fee-management.other-income.show', ['otherIncome' => $encodedId]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OtherIncome $encodedId)
    {
        $this->authorize('update', $encodedId);

        $students = SchoolStudent::forCompany(auth()->user()->company_id)
            ->forBranch(session('branch_id') ?: auth()->user()->branch_id)
            ->active()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $incomeAccounts = ChartAccount::whereHas('accountClassGroup', function($query) {
            $query->where('company_id', auth()->user()->company_id);
        })->whereBetween('account_code', [4000, 4999])
            ->orderBy('account_code')
            ->orderBy('account_name')
            ->get();

        $bankAccounts = BankAccount::orderBy('name')
            ->get();

        return view('school.fee-management.other-income.edit', ['otherIncome' => $encodedId, 'students' => $students, 'incomeAccounts' => $incomeAccounts, 'bankAccounts' => $bankAccounts]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OtherIncome $encodedId)
    {
        $this->authorize('update', $encodedId);

        // Get valid bank account IDs for the company
        $validBankAccountIds = BankAccount::pluck('id')->toArray();

        $validator = Validator::make($request->all(), [
            'transaction_date' => 'required|date',
            'income_type' => 'required|in:student,other',
            'student_id' => 'required_if:income_type,student|nullable|exists:students,id',
            'other_party' => 'required_if:income_type,other|nullable|string|max:255',
            'description' => 'required|string|max:500',
            'received_in' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($validBankAccountIds) {
                    if (!in_array((int) $value, $validBankAccountIds)) {
                        $fail('The selected bank account is invalid.');
                    }
                },
            ],
            'income_account_id' => 'required|exists:chart_accounts,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $encodedId->update([
                'transaction_date' => $request->transaction_date,
                'income_type' => $request->income_type,
                'student_id' => $request->income_type === 'student' ? $request->student_id : null,
                'other_party' => $request->income_type === 'other' ? $request->other_party : null,
                'description' => $request->description,
                'received_in' => $request->received_in,
                'income_account_id' => $request->income_account_id,
                'amount' => $request->amount,
            ]);

            // Recreate GL transactions
            $encodedId->glTransactions()->delete();
            $encodedId->createGlTransactions();

            DB::commit();

            return redirect()->route('school.other-income.index')
                ->with('success', 'Other income record updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to update income record: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OtherIncome $encodedId)
    {
        $this->authorize('delete', $encodedId);

        try {
            // Delete GL transactions first
            $encodedId->glTransactions()->delete();

            $encodedId->delete();

            return redirect()->route('school.other-income.index')
                ->with('success', 'Other income record deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete income record: ' . $e->getMessage());
        }
    }

    /**
     * Export the specified income record as PDF.
     */
    public function exportPdf(OtherIncome $encodedId)
    {
        $this->authorize('view', $encodedId);

        $encodedId->load(['student', 'incomeAccount', 'bankAccount', 'creator', 'approver', 'company', 'branch']);

        // Get GL transactions for this income record
        $glTransactions = $encodedId->glTransactions()->with('chartAccount')->get();

        // Calculate totals
        $totalDebit = $glTransactions->sum('debit');
        $totalCredit = $glTransactions->sum('credit');

        // Get company info
        $company = $encodedId->company;

        $data = [
            'otherIncome' => $encodedId,
            'glTransactions' => $glTransactions,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'company' => $company,
            'generatedAt' => now(),
        ];

        $pdf = \PDF::loadView('school.fee-management.other-income.pdf', $data);

        $filename = 'other_income_' . $encodedId->id . '_' . now()->format('Y_m_d_H_i_s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export filtered Other Income data to PDF
     */
    public function exportListPdf(Request $request)
    {
        $this->authorize('viewAny', OtherIncome::class);
        
        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        $query = OtherIncome::with(['student', 'incomeAccount', 'bankAccount', 'creator', 'approver', 'company', 'branch'])
            ->forCompany($companyId);

        // Apply same filters as in data method
        if ($branchId) {
            $query->forBranch($branchId);
        }

        if ($request->has('status') && $request->status !== '' && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        if ($request->has('income_type') && $request->income_type !== '' && in_array($request->income_type, ['student', 'other'])) {
            $query->where('income_type', $request->income_type);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->where('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        if ($request->has('class_id') && !empty($request->class_id)) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->has('income_account_id') && !empty($request->income_account_id)) {
            $query->where('income_account_id', $request->income_account_id);
        }

        $otherIncomes = $query->orderBy('transaction_date', 'desc')->get();
        $company = auth()->user()->company;

        $data = [
            'otherIncomes' => $otherIncomes,
            'company' => $company,
            'generatedAt' => now(),
            'filters' => $request->all()
        ];

        $pdf = \PDF::loadView('school.fee-management.other-income.list-pdf', $data);
        $filename = 'other_income_list_' . now()->format('Y_m_d_H_i_s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export filtered Other Income data to Excel
     */
    public function exportListExcel(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        $query = OtherIncome::with(['student.class', 'student.stream', 'incomeAccount', 'bankAccount', 'creator', 'approver'])
            ->forCompany($companyId);

        // Apply same filters as in data method
        if ($branchId) {
            $query->forBranch($branchId);
        }

        if ($request->has('status') && $request->status !== '' && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        if ($request->has('income_type') && $request->income_type !== '' && in_array($request->income_type, ['student', 'other'])) {
            $query->where('income_type', $request->income_type);
        }

        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->where('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        if ($request->has('class_id') && !empty($request->class_id)) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->has('income_account_id') && !empty($request->income_account_id)) {
            $query->where('income_account_id', $request->income_account_id);
        }

        $otherIncomes = $query->orderBy('transaction_date', 'desc')->get();

        // Create Excel file
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'Type');
        $sheet->setCellValue('C1', 'Student/Party');
        $sheet->setCellValue('D1', 'Class/Stream');
        $sheet->setCellValue('E1', 'Description');
        $sheet->setCellValue('F1', 'Received In');
        $sheet->setCellValue('G1', 'Income Account');
        $sheet->setCellValue('H1', 'Amount');
        $sheet->setCellValue('I1', 'Status');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD']
            ]
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

        // Add data
        $row = 2;
        foreach ($otherIncomes as $income) {
            $sheet->setCellValue('A' . $row, $income->transaction_date ? $income->transaction_date->format('d/m/Y') : 'N/A');
            $sheet->setCellValue('B' . $row, ucfirst($income->income_type));
            $sheet->setCellValue('C' . $row, $income->income_type === 'student' ? ($income->student->name ?? 'N/A') : ($income->other_party ?? 'N/A'));
            $sheet->setCellValue('D' . $row, $income->income_type === 'student' && $income->student
                ? (($income->student->class->name ?? 'N/A') . ($income->student->stream ? ' - ' . $income->student->stream->name : ''))
                : '-');
            $sheet->setCellValue('E' . $row, $income->description);
            $sheet->setCellValue('F' . $row, $income->receivedInDisplay);
            $sheet->setCellValue('G' . $row, $income->incomeAccount->account_name ?? 'N/A');
            $sheet->setCellValue('H' . $row, $income->amount);
            $sheet->setCellValue('I' . $row, ucfirst($income->status));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'other_income_list_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}