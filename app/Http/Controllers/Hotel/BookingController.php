<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Hotel\Booking;
use App\Models\Hotel\Room;
use App\Models\Hotel\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view bookings');
        $branch_id = session('branch_id') ?? user()->branch_id ?? 1;
        if ($request->ajax()) {
            $bookings = Booking::with(['room', 'guest', 'createdBy'])
                ->where('branch_id', $branch_id)
                ->select('bookings.*');

            return DataTables::of($bookings)
                ->addColumn('guest_name', function ($booking) {
                    return $booking->guest_name;
                })
                ->addColumn('room_info', function ($booking) {
                    return $booking->room_info;
                })
                ->addColumn('check_in_formatted', function ($booking) {
                    return $booking->check_in ? $booking->check_in->format('M d, Y') : 'N/A';
                })
                ->addColumn('check_out_formatted', function ($booking) {
                    return $booking->check_out ? $booking->check_out->format('M d, Y') : 'N/A';
                })
                ->addColumn('status_badge', function ($booking) {
                    return $booking->status_badge;
                })
                ->addColumn('payment_status_badge', function ($booking) {
                    return $booking->payment_status_badge;
                })
                ->addColumn('total_amount_formatted', function ($booking) {
                    return 'TSh ' . number_format($booking->total_amount ?? 0, 0);
                })
                ->addColumn('actions', function ($booking) {
                    return $booking->actions;
                })
                ->rawColumns(['guest_name', 'room_info', 'status_badge', 'payment_status_badge', 'actions'])
                ->make(true);
        }

        $totalBookings = Booking::where('branch_id', Auth::user()->branch_id)->count();
        $confirmedBookings = Booking::where('branch_id', Auth::user()->branch_id)->where('status', 'confirmed')->count();
        $checkedInBookings = Booking::where('branch_id', Auth::user()->branch_id)->where('status', 'checked_in')->count();
        $pendingBookings = Booking::where('branch_id', Auth::user()->branch_id)->where('status', 'pending')->count();
        $cancelledBookings = Booking::where('branch_id', Auth::user()->branch_id)->where('status', 'cancelled')->count();

        return view('hotel.bookings.index', compact(
            'totalBookings',
            'confirmedBookings',
            'checkedInBookings',
            'pendingBookings',
            'cancelledBookings'
        ));
    }

    public function create()
    {
        $this->authorize('create booking');
        
        $branch_id = session('branch_id') ?? Auth::user()->branch_id ?? 1;
        
        // List rooms for the current branch; availability will be validated against selected dates
        $rooms = Room::with('property')
            ->where('branch_id', $branch_id)
            ->orderBy('room_number')
            ->get();
            
        $guests = Guest::active()->orderBy('first_name')->get();
        $bankAccounts = \App\Models\BankAccount::orderBy('name')->get();
        
        return view('hotel.bookings.create', compact('rooms', 'guests', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $this->authorize('create booking');
        
        $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1|max:10',
            'children' => 'nullable|integer|min:0|max:10',
            'room_rate' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:pending,confirmed,checked_in,checked_out,cancelled',
            'booking_source' => 'nullable|string|in:walk_in,phone,online,agent,other',
            'special_requests' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'paid_amount' => 'nullable|numeric|min:0',
            'bank_account_id' => 'nullable|exists:bank_accounts,id'
        ]);

        DB::beginTransaction();
        
        try {
            Log::info('Booking: store request received', [
                'user_id' => Auth::id(),
                'guest_id' => $request->guest_id,
                'room_id' => $request->room_id,
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
            ]);
            // Check room availability
            $room = Room::findOrFail($request->room_id);
            $checkIn = Carbon::parse($request->check_in);
            $checkOut = Carbon::parse($request->check_out);
            
            if (!$room->isAvailableForDates($checkIn, $checkOut)) {
                // Find overlapping booking to show precise range
                $conflict = $room->bookings()
                    ->whereIn('status', ['confirmed', 'checked_in', 'pending'])
                    ->where(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<', $checkOut)
                          ->where('check_out', '>', $checkIn);
                    })
                    ->orderBy('check_in')
                    ->first();

                $conflictMsg = 'Room is not available for the selected dates.';
                if ($conflict) {
                    $conflictMsg = sprintf(
                        'Your new range %s 																				→ %s overlaps an existing booking %s → %s. Please choose dates outside this range.',
                        $checkIn->format('Y-m-d'),
                        $checkOut->format('Y-m-d'),
                        $conflict->check_in->format('Y-m-d'),
                        $conflict->check_out->format('Y-m-d')
                    );
                }

                Log::warning('Booking: room unavailable for selected dates', [
                    'room_id' => $room->id,
                    'check_in' => $checkIn->toDateString(),
                    'check_out' => $checkOut->toDateString(),
                    'conflict_booking_id' => $conflict->id ?? null
                ]);
                return redirect()->back()
                    ->withInput()
                    ->with('error', $conflictMsg)
                    ->with('error_overlap', true);
            }

            // Calculate nights and total amount
            $nights = $checkIn->diffInDays($checkOut);
            $grossAmount = $request->room_rate * $nights;
            $discountAmount = $request->discount_amount ?? 0;
            $totalAmount = max(0, $grossAmount - $discountAmount);
            $paidAmount = $request->paid_amount ?? 0;
            $balanceDue = $totalAmount - $paidAmount;
            
            // Calculate payment status automatically
            $paymentStatus = 'pending';
            if ($paidAmount > 0 && $paidAmount < $totalAmount) {
                $paymentStatus = 'partial';
            } elseif ($paidAmount >= $totalAmount) {
                $paymentStatus = 'paid';
            }

            // Generate booking number
            $bookingNumber = 'BK' . now()->format('Ymd') . str_pad(Booking::count() + 1, 4, '0', STR_PAD_LEFT);
            $branch_id = session('branch_id') ?? user()->branch_id ?? 1;

            $booking = Booking::create([
                'booking_number' => $bookingNumber,
                'room_id' => $request->room_id,
                'guest_id' => $request->guest_id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'adults' => $request->adults,
                'children' => $request->children ?? 0,
                'nights' => $nights,
                'room_rate' => $request->room_rate,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'paid_amount' => $paidAmount,
                'balance_due' => $balanceDue,
                'status' => $request->status,
                'payment_status' => $paymentStatus,
                'booking_source' => $request->booking_source,
                'special_requests' => $request->special_requests,
                'notes' => $request->notes,
                'branch_id' => $branch_id,
                'company_id' => Auth::user()->company_id,
                'created_by' => Auth::id()
            ]);

            // Update room status if booking is confirmed or checked in AND is for current dates
            if (in_array($request->status, ['confirmed', 'checked_in']) && $checkIn <= now()) {
                $room->update(['status' => 'occupied']);
            }

            // Create receipt for any paid amount
            if ($booking->paid_amount > 0) {
                $this->createBookingReceipt($booking, $request);
            }
            
            // Create GL transactions for the booking
            $this->createBookingGLTransactions($booking);

            DB::commit();
            
            Log::info('Booking: created successfully', ['booking_id' => $booking->id]);
            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Booking created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking: failed to create', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create booking: ' . $e->getMessage());
        }
    }

    public function show(Booking $booking)
    {
        $this->authorize('view booking details');
        
        $booking->load([
            'room.property', 
            'guest', 
            'createdBy',
            'glTransactions.chartAccount',
            'paymentGlTransactions.chartAccount',
            'receipts.receiptItems.chartAccount',
            'receipts.bankAccount'
        ]);
        
        return view('hotel.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        $this->authorize('edit booking');
        
        // Prevent editing checked-out bookings
        if ($booking->status === 'checked_out') {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Cannot edit checked-out bookings. This booking has been completed.');
        }
        
        $branch_id = session('branch_id') ?? Auth::user()->branch_id ?? 1;
        
        $rooms = Room::with('property')
            ->where('branch_id', $branch_id)
            ->orderBy('room_number')
            ->get();
        $guests = Guest::active()->orderBy('first_name')->get();
        $bankAccounts = \App\Models\BankAccount::orderBy('name')->get();
        
        return view('hotel.bookings.edit', compact('booking', 'rooms', 'guests', 'bankAccounts'));
    }

    public function update(Request $request, Booking $booking)
    {
        $this->authorize('edit booking');
        
        // Prevent updating checked-out bookings
        if ($booking->status === 'checked_out') {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Cannot update checked-out bookings. This booking has been completed.');
        }
        
        $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1|max:10',
            'children' => 'nullable|integer|min:0|max:10',
            'room_rate' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:pending,confirmed,checked_in,checked_out,cancelled',
            'booking_source' => 'nullable|string|in:walk_in,phone,online,agent,other',
            'special_requests' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'paid_amount' => 'nullable|numeric|min:0',
            'bank_account_id' => 'nullable|exists:bank_accounts,id'
        ]);

        DB::beginTransaction();
        
        try {
            $checkIn = Carbon::parse($request->check_in);
            $checkOut = Carbon::parse($request->check_out);
            $nights = $checkIn->diffInDays($checkOut);
            $grossAmount = $request->room_rate * $nights;
            $discountAmount = $request->discount_amount ?? ($booking->discount_amount ?? 0);
            $totalAmount = max(0, $grossAmount - $discountAmount);
            
            // Handle payment changes
            $oldPaidAmount = $booking->paid_amount;
            $newPaidAmount = $request->paid_amount ?? 0;
            $paymentDifference = $newPaidAmount - $oldPaidAmount;
            
            $balanceDue = $totalAmount - $newPaidAmount;
            
            // Calculate payment status automatically
            $paymentStatus = 'pending';
            if ($newPaidAmount > 0 && $newPaidAmount < $totalAmount) {
                $paymentStatus = 'partial';
            } elseif ($newPaidAmount >= $totalAmount) {
                $paymentStatus = 'paid';
            }

            $booking->update([
                'room_id' => $request->room_id,
                'guest_id' => $request->guest_id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'adults' => $request->adults,
                'children' => $request->children ?? 0,
                'nights' => $nights,
                'room_rate' => $request->room_rate,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $newPaidAmount,
                'balance_due' => $balanceDue,
                'status' => $request->status,
                'payment_status' => $paymentStatus,
                'booking_source' => $request->booking_source,
                'special_requests' => $request->special_requests,
                'notes' => $request->notes
            ]);

            // Recreate booking GL transactions to reflect changes (revenue/discount/AR/Cash)
            $booking->glTransactions()->delete();
            $this->createBookingGLTransactions($booking);

            // Create receipt for additional payment
            if ($paymentDifference > 0) {
                $this->createBookingReceipt($booking, $request, $paymentDifference);
                
                // Create GL transactions for the additional payment
                $this->createPaymentGLTransactions($booking, $paymentDifference, $request);
            }

            DB::commit();
            
            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Booking updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update booking: ' . $e->getMessage());
        }
    }

    public function destroy(Booking $booking)
    {
        $this->authorize('delete booking');
        
        try {
            $booking->delete();
            
            return redirect()->route('bookings.index')
                ->with('success', 'Booking deleted successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete booking: ' . $e->getMessage());
        }
    }

    public function checkIn(Booking $booking)
    {
        $this->authorize('edit booking');
        
        if ($booking->checkIn()) {
            return redirect()->back()
                ->with('success', 'Guest checked in successfully!');
        }
        
        return redirect()->back()
            ->with('error', 'Cannot check in guest at this time.');
    }

    public function checkOut(Booking $booking)
    {
        $this->authorize('edit booking');
        
        if ($booking->checkOut()) {
            return redirect()->back()
                ->with('success', 'Guest checked out successfully!');
        }
        
        return redirect()->back()
            ->with('error', 'Cannot check out guest at this time.');
    }

    public function confirm(Booking $booking)
    {
        $this->authorize('edit booking');

        // Only allow confirming from pending status
        if ($booking->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending bookings can be confirmed.');
        }

        // Basic room availability guard (optional): ensure check-in date not in the past for unavailable rooms
        if ($booking->check_in && $booking->check_in < now() && optional($booking->room)->status === 'occupied') {
            return redirect()->back()->with('error', 'Room is currently occupied. Cannot confirm.');
        }

        $booking->update(['status' => 'confirmed']);

        return redirect()->back()->with('success', 'Booking confirmed successfully!');
    }

    public function cancel(Booking $booking, Request $request)
    {
        $this->authorize('edit booking');
        
        $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
            'cancellation_fee' => 'nullable|numeric|min:0'
        ]);
        
        if ($booking->cancel($request->cancellation_reason, $request->cancellation_fee ?? 0)) {
            return redirect()->back()
                ->with('success', 'Booking cancelled successfully!');
        }
        
        return redirect()->back()
            ->with('error', 'Cannot cancel booking at this time.');
    }

    public function recordPayment(Request $request, Booking $booking)
    {
        $this->authorize('edit booking');
        
        // Validate the request
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $booking->balance_due,
            'payment_date' => 'required|date',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payment_description' => 'nullable|string|max:500'
        ]);
        
        try {
            DB::beginTransaction();
            
            $paymentAmount = $request->payment_amount;
            $newPaidAmount = $booking->paid_amount + $paymentAmount;
            $newBalanceDue = $booking->total_amount - $newPaidAmount;
            
            // Calculate new payment status
            $paymentStatus = 'pending';
            if ($newPaidAmount > 0 && $newPaidAmount < $booking->total_amount) {
                $paymentStatus = 'partial';
            } elseif ($newPaidAmount >= $booking->total_amount) {
                $paymentStatus = 'paid';
            }
            
            // Update booking with new payment information
            $booking->update([
                'paid_amount' => $newPaidAmount,
                'balance_due' => $newBalanceDue,
                'payment_status' => $paymentStatus
            ]);
            
            // Create receipt for the payment
            $receipt = \App\Models\Receipt::create([
                'reference' => 'hotel_booking',
                'reference_type' => 'hotel_booking',
                'reference_number' => $booking->booking_number,
                'amount' => $paymentAmount,
                'date' => $request->payment_date,
                'description' => $request->payment_description ?: "Additional payment for booking #{$booking->booking_number}",
                'user_id' => auth()->id(),
                'bank_account_id' => $request->bank_account_id,
                'payee_type' => 'guest',
                'payee_id' => $booking->guest_id,
                'payee_name' => $booking->guest->first_name . ' ' . $booking->guest->last_name,
                'branch_id' => $booking->branch_id,
                'approved' => true,
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            // Create receipt item for the hotel booking payment
            $receipt->receiptItems()->create([
                'chart_account_id' => $this->getHotelRevenueAccountId(),
                'amount' => $paymentAmount,
                'description' => "Additional payment - Room {$booking->room->room_number} ({$booking->nights} nights)"
            ]);
            
            // Create GL transactions for the additional payment
            $this->createPaymentGLTransactions($booking, $paymentAmount, $request);
            
            DB::commit();
            
            return redirect()->route('bookings.show', $booking)
                ->with('success', "Payment of TSh " . number_format($paymentAmount, 0) . " recorded successfully!");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }


    /**
     * Create receipt for paid booking
     */
    private function createBookingReceipt(Booking $booking, Request $request, $amount = null)
    {
        $receiptAmount = $amount ?? $booking->paid_amount;
        
        // Create a receipt for the payment
        $receipt = \App\Models\Receipt::create([
            'reference' => 'hotel_booking',
            'reference_type' => 'hotel_booking',
            'reference_number' => $booking->booking_number,
            'amount' => $receiptAmount,
            'date' => now(),
            'description' => "Payment received for hotel booking #{$booking->booking_number}",
            'user_id' => auth()->id(),
            'bank_account_id' => $request->bank_account_id,
            'payee_type' => 'guest',
            'payee_id' => $booking->guest_id,
            'payee_name' => $booking->guest->first_name . ' ' . $booking->guest->last_name,
            'branch_id' => $booking->branch_id,
            'approved' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        // Create receipt item for the hotel booking payment
        $receipt->receiptItems()->create([
            'chart_account_id' => $this->getHotelRevenueAccountId(),
            'amount' => $receiptAmount,
            'description' => "Hotel room payment - Room {$booking->room->room_number} ({$booking->nights} nights)"
        ]);

        return $receipt;
    }

    /**
     * Get the hotel revenue account ID
     */
    private function getHotelRevenueAccountId()
    {
        // Try to find hotel revenue account, create a default one if not found
        $account = \App\Models\ChartAccount::where('account_name', 'Hotel Room Revenue')->first();
        
        if (!$account) {
            // Create a default hotel revenue account
            $account = \App\Models\ChartAccount::create([
                'account_name' => 'Hotel Room Revenue',
                'account_code' => '4001',
            ]);
        }
        
        return $account->id;
    }

    /**
     * Get the hotel discount expense account ID
     */
    private function getHotelDiscountExpenseAccountId()
    {
        $fromSetting = \App\Models\SystemSetting::where('key', 'hotel_discount_expense_account_id')->value('value');
        if ($fromSetting) {
            return (int) $fromSetting;
        }
        // Fallback to seeded Discount Expense account by ID (172)
        $byId = \App\Models\ChartAccount::where('id', 172)->value('id');
        if ($byId) return (int) $byId;
        // Last resort: create (without forcing a specific ID)
        return \App\Models\ChartAccount::create([
            'account_name' => 'Discount Expense',
            'account_code' => '5307',
        ])->id;
    }

    /**
     * Create GL transactions for booking
     */
    private function createBookingGLTransactions(Booking $booking)
    {
        // Calculate gross and discount
        $grossAmount = $booking->room_rate * $booking->nights;
        $discountAmount = $booking->discount_amount ?? 0;
        $netAmount = max(0, $grossAmount - $discountAmount);

        // 1. Credit: Hotel Room Revenue (gross)
        \App\Models\GlTransaction::create([
            'chart_account_id' => $this->getHotelRevenueAccountId(),
            'amount' => $grossAmount,
            'nature' => 'credit',
            'transaction_type' => 'hotel_booking',
            'transaction_id' => $booking->id,
            'description' => "Room revenue for booking #{$booking->booking_number}",
            'date' => $booking->check_in,
            'branch_id' => $booking->branch_id,
            'user_id' => auth()->id()
        ]);
        
        // 2. Debit: Discount Expense (if any)
        if ($discountAmount > 0) {
            \App\Models\GlTransaction::create([
                'chart_account_id' => $this->getHotelDiscountExpenseAccountId(),
                'amount' => $discountAmount,
                'nature' => 'debit',
                'transaction_type' => 'hotel_booking',
                'transaction_id' => $booking->id,
                'description' => "Discount given for booking #{$booking->booking_number}",
                'date' => $booking->check_in,
                'branch_id' => $booking->branch_id,
                'user_id' => auth()->id()
            ]);
        }

        // 3. Debit: Accounts Receivable (for net unpaid amount)
        if ($booking->balance_due > 0) {
            \App\Models\GlTransaction::create([
                'chart_account_id' => $this->getAccountsReceivableAccountId(),
                'amount' => $booking->balance_due,
                'nature' => 'debit',
                'transaction_type' => 'hotel_booking',
                'transaction_id' => $booking->id,
                'description' => "Receivable (net) for booking #{$booking->booking_number}",
                'date' => $booking->check_in,
                'branch_id' => $booking->branch_id,
                'user_id' => auth()->id()
            ]);
        }
        
        // 4. Debit: Cash/Bank (for paid amount)
        if ($booking->paid_amount > 0) {
            \App\Models\GlTransaction::create([
                'chart_account_id' => $this->getCashAccountId($booking),
                'amount' => $booking->paid_amount,
                'nature' => 'debit',
                'transaction_type' => 'hotel_booking',
                'transaction_id' => $booking->id,
                'description' => "Payment received (net) for booking #{$booking->booking_number}",
                'date' => $booking->check_in,
                'branch_id' => $booking->branch_id,
                'user_id' => auth()->id()
            ]);
        }
    }

    /**
     * Get the accounts receivable account ID
     */
    private function getAccountsReceivableAccountId()
    {
        // Try to find accounts receivable account, create a default one if not found
        $account = \App\Models\ChartAccount::where('account_name', 'Trade Receivables')->first();
        
        if (!$account) {
            // Create a default accounts receivable account
            $account = \App\Models\ChartAccount::create([
                'account_name' => 'Accounts Receivable',
                'account_code' => '1200',
            ]);
        }
        
        return $account->id;
    }

    /**
     * Get the cash account ID for the booking
     */
    private function getCashAccountId(Booking $booking)
    {
        // If there's a receipt with bank account, use that bank's GL account
        $receipt = \App\Models\Receipt::where('reference_type', 'hotel_booking')
            ->where('reference_number', $booking->booking_number)
            ->first();
            
        if ($receipt && $receipt->bank_account_id) {
            // Use the bank account's GL account
            $bankAccount = \App\Models\BankAccount::find($receipt->bank_account_id);
            if ($bankAccount && $bankAccount->chart_account_id) {
                return $bankAccount->chart_account_id;
            }
        }
        
        // Default to cash account
        $account = \App\Models\ChartAccount::where('account_name', 'Cash on Hand')->first();
        
        if (!$account) {
            // Create a default cash account
            $account = \App\Models\ChartAccount::create([
                'account_name' => 'Cash',
                'account_code' => '1000',
            ]);
        }
        
        return $account->id;
    }

    /**
     * Create GL transactions for additional payment
     */
    private function createPaymentGLTransactions(Booking $booking, $paymentAmount, Request $request)
    {
        // 1. Debit: Accounts Receivable (reduce receivable by payment amount)
        \App\Models\GlTransaction::create([
            'chart_account_id' => $this->getAccountsReceivableAccountId(),
            'amount' => $paymentAmount,
            'nature' => 'credit', // Credit to reduce receivable
            'transaction_type' => 'hotel_payment',
            'transaction_id' => $booking->id,
            'description' => "Payment received for booking #{$booking->booking_number}",
            'date' => now(),
            'branch_id' => $booking->branch_id,
            'user_id' => auth()->id()
        ]);
        
        // 2. Debit: Cash/Bank (increase cash/bank by payment amount)
        \App\Models\GlTransaction::create([
            'chart_account_id' => $this->getCashAccountId($booking),
            'amount' => $paymentAmount,
            'nature' => 'debit', // Debit to increase cash/bank
            'transaction_type' => 'hotel_payment',
            'transaction_id' => $booking->id,
            'description' => "Payment received for booking #{$booking->booking_number}",
            'date' => now(),
            'branch_id' => $booking->branch_id,
            'user_id' => auth()->id()
        ]);
    }

    /**
     * Edit receipt for booking
     */
    public function editReceipt($encodedId)
    {
        try {
            // Decode the Hashids-encoded receipt ID
            $decodedId = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

            if (!$decodedId) {
                abort(404, 'Invalid receipt ID.');
            }

            // Find the receipt
            $receipt = \App\Models\Receipt::with(['bankAccount', 'customer'])->findOrFail($decodedId);

            // Check if this receipt is related to a booking
            $booking = \App\Models\Hotel\Booking::where('booking_number', $receipt->reference_number)->first();

            if (!$booking) {
                abort(404, 'Receipt not related to any booking.');
            }

            $bankAccounts = \App\Models\BankAccount::orderBy('name')->get();

            return view('hotel.bookings.edit-receipt', compact('receipt', 'booking', 'bankAccounts'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to load receipt: ' . $e->getMessage()]);
        }
    }

    /**
     * Update receipt for booking
     */
    public function updateReceipt(Request $request, $encodedId)
    {
        try {
            // Decode the Hashids-encoded receipt ID
            $decodedId = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

            if (!$decodedId) {
                abort(404, 'Invalid receipt ID.');
            }

            $request->validate([
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
                'bank_account_id' => 'required|exists:bank_accounts,id',
                'description' => 'nullable|string|max:1000'
            ]);

            $receipt = \App\Models\Receipt::findOrFail($decodedId);

            // Check if this receipt is related to a booking
            $booking = \App\Models\Hotel\Booking::where('booking_number', $receipt->reference_number)->first();

            if (!$booking) {
                abort(404, 'Receipt not related to any booking.');
            }

            \DB::transaction(function () use ($request, $receipt, $booking) {
                $oldAmount = $receipt->amount;
                $newAmount = $request->amount;

                // Update receipt
                $receipt->update([
                    'amount' => $newAmount,
                    'date' => $request->date,
                    'description' => $request->description,
                    'bank_account_id' => $request->bank_account_id,
                ]);

                // Update receipt items
                $receipt->receiptItems()->update([
                    'amount' => $newAmount,
                    'description' => $request->description,
                ]);

                // Remove old GL transactions
                $receipt->glTransactions()->delete();

                $user = \Auth::user();

                // Create new GL transactions
                if ($newAmount > 0) {
                    // Credit: Hotel Room Revenue
                    \App\Models\GlTransaction::create([
                        'chart_account_id' => $this->getHotelRevenueAccountId(),
                        'amount' => $newAmount,
                        'nature' => 'credit',
                        'transaction_type' => 'hotel_booking',
                        'transaction_id' => $booking->id,
                        'description' => "Room revenue for booking #{$booking->booking_number}",
                        'date' => $request->date,
                        'branch_id' => $booking->branch_id,
                        'user_id' => $user->id
                    ]);

                    // Debit: Cash/Bank
                    \App\Models\GlTransaction::create([
                        'chart_account_id' => $this->getCashAccountId($booking),
                        'amount' => $newAmount,
                        'nature' => 'debit',
                        'transaction_type' => 'hotel_booking',
                        'transaction_id' => $booking->id,
                        'description' => "Payment received for booking #{$booking->booking_number}",
                        'date' => $request->date,
                        'branch_id' => $booking->branch_id,
                        'user_id' => $user->id
                    ]);
                }

                // Update booking paid amount
                $booking->update(['paid_amount' => $newAmount]);
            });

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Receipt updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update receipt: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Print receipt for booking
     */
    public function printReceipt($encodedId)
    {
        try {
            // Decode the Hashids-encoded receipt ID
            $decodedId = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

            if (!$decodedId) {
                abort(404, 'Invalid receipt ID.');
            }

            // Find the receipt with related data
            $receipt = \App\Models\Receipt::with([
                'bankAccount', 
                'customer', 
                'receiptItems.chartAccount',
                'user',
                'branch'
            ])->findOrFail($decodedId);

            // Check if this receipt is related to a booking
            $booking = \App\Models\Hotel\Booking::where('booking_number', $receipt->reference_number)->first();

            if (!$booking) {
                abort(404, 'Receipt not related to any booking.');
            }

            // Generate PDF using DomPDF
            $pdf = \PDF::loadView('receipts.print', compact('receipt', 'booking'));
            
            return $pdf->stream("booking-receipt-{$receipt->reference_number}.pdf");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to print receipt: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX: Check availability for a room date range
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'exclude_booking_id' => 'nullable|integer'
        ]);

        $room = Room::findOrFail($request->room_id);
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $excludeId = $request->exclude_booking_id;

        $available = $excludeId
            ? $room->isAvailableForDateRange($checkIn, $checkOut, $excludeId)
            : $room->isAvailableForDates($checkIn, $checkOut);

        if ($available) {
            return response()->json(['available' => true]);
        }

        $conflict = $room->bookings()
            ->whereIn('status', ['confirmed', 'checked_in', 'pending'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->where('check_in', '<', $checkOut)
                  ->where('check_out', '>', $checkIn);
            })
            ->orderBy('check_in')
            ->first();

        return response()->json([
            'available' => false,
            'message' => $conflict ? sprintf(
                'Selected range %s → %s overlaps existing booking %s → %s.',
                $checkIn->format('Y-m-d'),
                $checkOut->format('Y-m-d'),
                $conflict->check_in->format('Y-m-d'),
                $conflict->check_out->format('Y-m-d')
            ) : 'Room is not available for the selected dates.'
        ]);
    }

    /**
     * AJAX: Get available rooms for selected date range
     */
    public function availableRooms(Request $request)
    {
        $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
        ]);

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $branch_id = session('branch_id') ?? Auth::user()->branch_id ?? 1;

        $rooms = Room::with('property')
            ->where('branch_id', $branch_id)
            ->whereNotIn('status', ['maintenance', 'out_of_order'])
            ->orderBy('room_number')
            ->get()
            ->filter(function ($room) use ($checkIn, $checkOut) {
                return $room->isAvailableForDates($checkIn, $checkOut);
        })->map(function ($room) {
            return [
                'id' => $room->id,
                'label' => ($room->full_room_name) . ' - ' . ($room->property->name ?? 'No Property'),
                'rate' => $room->rate_per_night,
                'capacity' => $room->capacity,
                'type' => $room->room_type,
            ];
        })->values();

        return response()->json(['rooms' => $rooms]);
    }

    /**
     * Delete receipt for booking
     */
    public function deleteReceipt($encodedId)
    {
        try {
            // Decode the Hashids-encoded receipt ID
            $decodedId = \Vinkla\Hashids\Facades\Hashids::decode($encodedId)[0] ?? null;

            if (!$decodedId) {
                abort(404, 'Invalid receipt ID.');
            }

            $receipt = \App\Models\Receipt::findOrFail($decodedId);

            // Check if this receipt is related to a booking
            $booking = \App\Models\Hotel\Booking::where('booking_number', $receipt->reference_number)->first();

            if (!$booking) {
                abort(404, 'Receipt not related to any booking.');
            }

            \DB::transaction(function () use ($receipt, $booking) {
                // Remove GL transactions
                $receipt->glTransactions()->delete();

                // Delete receipt items
                $receipt->receiptItems()->delete();

                // Delete receipt
                $receipt->delete();

                // Update booking paid amount
                $booking->update(['paid_amount' => 0]);
            });

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Receipt deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete receipt: ' . $e->getMessage()]);
        }
    }

    /**
     * Export booking details as PDF
     */
    public function exportPdf(Booking $booking)
    {
        $this->authorize('view booking details');
        
        $booking->load([
            'room.property', 
            'guest', 
            'createdBy',
            'glTransactions.chartAccount',
            'paymentGlTransactions.chartAccount',
            'receipts.receiptItems.chartAccount',
            'receipts.bankAccount'
        ]);
        
        // Generate PDF using DomPDF
        $pdf = \PDF::loadView('hotel.bookings.export-pdf', compact('booking'));
        
        return $pdf->stream("booking-{$booking->booking_number}.pdf");
    }

}
