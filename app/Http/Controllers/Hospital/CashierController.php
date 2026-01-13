<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitBill;
use App\Models\Hospital\VisitBillItem;
use App\Models\Hospital\VisitPayment;
use App\Models\Hospital\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashierController extends Controller
{
    /**
     * Display cashier dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Get pending bills
        $pendingBills = VisitBill::with(['patient', 'visit'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('payment_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get statistics
        $stats = [
            'pending_bills' => $pendingBills->count(),
            'pending_amount' => $pendingBills->sum('total'),
            'paid_today' => VisitBill::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('payment_status', 'paid')
                ->whereDate('created_at', today())
                ->count(),
            'revenue_today' => VisitPayment::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->whereDate('payment_date', today())
                ->sum('amount'),
        ];

        return view('hospital.cashier.index', compact('pendingBills', 'stats'));
    }

    /**
     * Show bill details
     */
    public function showBill($id)
    {
        $bill = VisitBill::with(['patient', 'visit', 'items', 'payments'])
            ->findOrFail($id);

        // Verify access
        if ($bill->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to bill.');
        }

        return view('hospital.cashier.bills.show', compact('bill'));
    }

    /**
     * Show payment form
     */
    public function createPayment($billId)
    {
        $bill = VisitBill::with(['patient', 'visit'])->findOrFail($billId);

        // Verify access
        if ($bill->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to bill.');
        }

        return view('hospital.cashier.payments.create', compact('bill'));
    }

    /**
     * Process payment
     */
    public function storePayment(Request $request, $billId)
    {
        $bill = VisitBill::with(['patient', 'visit'])->findOrFail($billId);

        // Verify access
        if ($bill->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to bill.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $bill->balance,
            'payment_method' => 'required|in:cash,nhif,chf,jubilee,strategy,mobile_payment',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $companyId = $user->company_id;
            $branchId = session('branch_id') ?? $user->branch_id;

            // Generate payment number
            $paymentNumber = 'PAY-' . now()->format('Ymd') . '-' . str_pad(VisitPayment::whereDate('payment_date', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Create payment
            $payment = VisitPayment::create([
                'payment_number' => $paymentNumber,
                'bill_id' => $bill->id,
                'visit_id' => $bill->visit_id,
                'patient_id' => $bill->patient_id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'received_by' => $user->id,
                'payment_date' => now(),
            ]);

            // Update bill
            $bill->paid += $validated['amount'];
            $bill->balance = $bill->total - $bill->paid;
            
            // Update payment status
            if ($bill->balance <= 0) {
                $bill->payment_status = 'paid';
            } elseif ($bill->paid > 0) {
                $bill->payment_status = 'partial';
            }
            
            $bill->save();

            DB::commit();

            return redirect()->route('hospital.cashier.bills.show', $bill->id)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to process payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Clear bill (make patient visible to departments)
     */
    public function clearBill($billId)
    {
        $bill = VisitBill::with(['visit'])->findOrFail($billId);

        // Verify access
        if ($bill->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to bill.');
        }

        // Check if bill is fully paid
        if ($bill->payment_status !== 'paid') {
            return back()->withErrors(['error' => 'Bill must be fully paid before clearing.']);
        }

        try {
            $bill->clear();

            return redirect()->route('hospital.cashier.bills.show', $bill->id)
                ->with('success', 'Bill cleared successfully. Patient can now proceed to departments.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to clear bill: ' . $e->getMessage()]);
        }
    }

    /**
     * Search patients/bills
     */
    public function search(Request $request)
    {
        $term = $request->get('term', '');
        $user = Auth::user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?? $user->branch_id;

        // Search bills
        $bills = VisitBill::with(['patient', 'visit'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where(function ($q) use ($term) {
                $q->where('bill_number', 'like', "%{$term}%")
                    ->orWhereHas('patient', function ($query) use ($term) {
                        $query->where('mrn', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%");
                    });
            })
            ->limit(20)
            ->get();

        return response()->json($bills);
    }
}
