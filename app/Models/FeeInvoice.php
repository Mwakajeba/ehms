<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class FeeInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'lipisha_control_number',
        'student_id',
        'class_id',
        'academic_year_id',
        'fee_group_id',
        'period',
        'subtotal',
        'transport_fare',
        'discount_type',
        'discount_value',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'due_date',
        'issue_date',
        'status',
        'company_id',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'transport_fare' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'issue_date' => 'date',
    ];

    /**
     * Get the student that owns the fee invoice.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Student::class);
    }

    /**
     * Get the class that owns the fee invoice.
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Classe::class, 'class_id');
    }

    /**
     * Get the academic year that owns the fee invoice.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\AcademicYear::class);
    }

    /**
     * Get the fee group that owns the fee invoice.
     */
    public function feeGroup(): BelongsTo
    {
        return $this->belongsTo(FeeGroup::class);
    }

    /**
     * Get the company that owns the fee invoice.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the fee invoice.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created the fee invoice.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the fee invoice items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(FeeInvoiceItem::class);
    }

    /**
     * Get the payments for this invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'reference', 'invoice_number')
                    ->where('reference_type', 'fee_invoice');
    }

    /**
     * Scope to filter by company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to filter by branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKey()
    {
        return Hashids::encode($this->getKey());
    }

    /**
     * Get the route key name for route model binding.
     */
    public function getRouteKeyName()
    {
        return 'fee_invoice';
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $decoded = Hashids::decode($value);
        $id = $decoded[0] ?? null;

        return $this->where('id', $id)->firstOrFail();
    }

    /**
     * Get the hashid attribute.
     */
    public function getHashidAttribute()
    {
        return Hashids::encode($this->getKey());
    }

    /**
     * Find by hashid.
     */
    public static function findByHashid($hashid)
    {
        $decoded = Hashids::decode($hashid);
        return self::where('id', $decoded[0] ?? null)->first();
    }

    /**
     * Create double entry transactions for the invoice.
     */
    public function createDoubleEntryTransactions()
    {
        // Debit: Student Receivable Account
        // Credit: Fee Income Account

        $user = auth()->user();
        $userId = $user ? $user->id : 1;

        $transactions = [];

        // 1. Debit Student Receivable (Asset) - reduced by discount amount
        $receivableAccountId = $this->feeGroup->receivable_account_id ??
                              \App\Models\ChartAccount::where('account_name', 'Trade Receivables')->value('id') ??
                              18; // Default fallback
        $receivableAmount = $this->total_amount; // This already includes discount subtraction
        $transactions[] = [
            'chart_account_id' => $receivableAccountId,
            'customer_id' => null, // Students don't have customer IDs
            'amount' => $receivableAmount,
            'nature' => 'debit',
            'transaction_id' => $this->id,
            'transaction_type' => 'fee_invoice',
            'date' => $this->issue_date,
            'description' => "Fee Invoice #{$this->invoice_number} - {$this->student->first_name} {$this->student->last_name}",
            'branch_id' => $this->branch_id,
            'user_id' => $userId,
        ];

        // 2. Credit Fee Income Account (full amount before discount)
        $incomeAccountId = $this->feeGroup->income_account_id ??
                          \App\Models\ChartAccount::where('account_name', 'Sales Revenue')->value('id') ??
                          53; // Default fallback
        $incomeAmount = $this->subtotal; // Full fee income before discount
        $transactions[] = [
            'chart_account_id' => $incomeAccountId,
            'customer_id' => null,
            'amount' => $incomeAmount,
            'nature' => 'credit',
            'transaction_id' => $this->id,
            'transaction_type' => 'fee_invoice',
            'date' => $this->issue_date,
            'description' => "Fee Income Invoice #{$this->invoice_number} - {$this->student->first_name} {$this->student->last_name}",
            'branch_id' => $this->branch_id,
            'user_id' => $userId,
        ];

        // 3. If transport fare exists, credit Transport Income Account
        if ($this->transport_fare > 0) {
            $transportIncomeAccountId = $this->feeGroup->transport_income_account_id ?? $incomeAccountId;
            $transactions[] = [
                'chart_account_id' => $transportIncomeAccountId,
                'customer_id' => null,
                'amount' => $this->transport_fare,
                'nature' => 'credit',
                'transaction_id' => $this->id,
                'transaction_type' => 'fee_invoice',
                'date' => $this->issue_date,
                'description' => "Transport Fee Invoice #{$this->invoice_number} - {$this->student->first_name} {$this->student->last_name}",
                'branch_id' => $this->branch_id,
                'user_id' => $userId,
            ];
        }

        // 4. If discount exists, debit Discount Expense Account (contra-revenue)
        if ($this->discount_amount > 0) {
            $discountAccountId = $this->feeGroup->discount_account_id ??
                                \App\Models\ChartAccount::where('account_name', 'Discount Allowed')->value('id') ??
                                $incomeAccountId; // Use income account as fallback

            $transactions[] = [
                'chart_account_id' => $discountAccountId,
                'customer_id' => null,
                'amount' => $this->discount_amount,
                'nature' => 'debit', // Changed from credit to debit - discount is an expense
                'transaction_id' => $this->id,
                'transaction_type' => 'fee_invoice',
                'date' => $this->issue_date,
                'description' => "Fee Discount Invoice #{$this->invoice_number} - {$this->student->first_name} {$this->student->last_name}",
                'branch_id' => $this->branch_id,
                'user_id' => $userId,
            ];
        }

        // Create all transactions
        foreach ($transactions as $transaction) {
            GlTransaction::create($transaction);
        }
    }

    /**
     * Delete double entry transactions for the invoice.
     */
    public function deleteDoubleEntryTransactions()
    {
        GlTransaction::where('transaction_id', $this->id)
                    ->where('transaction_type', 'fee_invoice')
                    ->delete();
    }
}
