<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Hotel\Property;
use App\Models\Hotel\Room;

class HotelExpenseController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $expenses = Payment::where('branch_id', $branchId)
            ->where('payee_type', 'hotel')
            ->orderByDesc('id')
            ->paginate(20);

        return view('hotel.expenses.index', compact('expenses'));
    }

    public function create()
    {
        $properties = Property::orderBy('name')->get(['id', 'name']);
        $rooms = Room::orderBy('room_number')->get(['id', 'room_number', 'room_name', 'property_id']);
        $bankAccounts = \App\Models\BankAccount::orderBy('name')->get(['id', 'name']);
        // Expense accounts
        $chartAccounts = \App\Models\ChartAccount::whereHas('accountClassGroup.accountClass', function ($q) {
                $q->where('name', 'like', '%expense%')
                  ->orWhere('name', 'like', '%cost%')
                  ->orWhere('name', 'like', '%expenditure%');
            })
            ->orderBy('account_name')
            ->get(['id','account_name','account_code']);

        return view('hotel.expenses.create', compact('properties', 'rooms', 'bankAccounts', 'chartAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:500'],
            'bank_account_id' => ['required', 'integer', 'exists:bank_accounts,id'],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'line_items' => ['required','array','min:1'],
            'line_items.*.chart_account_id' => ['required','integer','exists:chart_accounts,id'],
            'line_items.*.amount' => ['required','numeric','min:0.01'],
            'line_items.*.description' => ['nullable','string','max:255'],
        ]);

        if (empty($validated['property_id']) && empty($validated['room_id'])) {
            return back()->withErrors(['property_id' => 'Select a Property for general expense or a specific Room.'])->withInput();
        }

        $user = auth()->user();

        DB::beginTransaction();
        try {
            $totalAmount = collect($validated['line_items'])->sum(function($li){ return (float)$li['amount']; });
            $branchId = session('branch_id') ?? Auth::user()->branch_id ?? 1;
            $payment = new Payment();
            $payment->payment_number = $this->generatePaymentNumber();
            $payment->payment_date = $validated['expense_date'];
            $payment->description = $validated['description'];
            $payment->amount = $totalAmount;
            $payment->method = 'bank';
            $payment->bank_account_id = $validated['bank_account_id'];
            $payment->payee_type = 'hotel';
            $payment->payee_id = null; // Not tied to external party
            $payment->branch_id = $branchId;
            $payment->company_id = $user->company_id;
            $payment->created_by = $user->id;
            $payment->save();

            foreach ($validated['line_items'] as $li) {
                $item = new PaymentItem();
                $item->payment_id = $payment->id;
                $item->reference_type = 'hotel_expense';
                $item->reference_id = null;
                $item->chart_account_id = $li['chart_account_id'];
                $item->description = $li['description'] ?? $validated['description'];
                $item->amount = $li['amount'];
                $item->meta = [
                    'property_id' => $validated['property_id'] ?? null,
                    'room_id' => $validated['room_id'] ?? null,
                ];
                $item->save();
            }

            // GL transactions: reuse Payment model hooks/services already established
            if (method_exists($payment, 'createGlEntries')) {
                $payment->createGlEntries();
            }

            DB::commit();

            return redirect()->route('hotel.expenses.index')->with('success', 'Hotel expense recorded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to record hotel expense', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Failed to save expense: ' . $e->getMessage())->withInput();
        }
    }

    private function generatePaymentNumber(): string
    {
        $prefix = 'HEX' . now()->format('Ymd');
        $last = Payment::where('payment_number', 'like', $prefix . '%')->orderByDesc('id')->value('payment_number');
        $seq = 1;
        if ($last && preg_match('/(\d{4})$/', $last, $m)) {
            $seq = intval($m[1]) + 1;
        }
        return sprintf('%s%04d', $prefix, $seq);
    }
}


