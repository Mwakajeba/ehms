<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class OtherIncome extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_date',
        'income_type',
        'student_id',
        'other_party',
        'description',
        'received_in',
        'income_account_id',
        'amount',
        'reference_number',
        'notes',
        'status',
        'company_id',
        'branch_id',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the student that owns the other income.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Student::class);
    }

    /**
     * Get the income account that owns the other income.
     */
    public function incomeAccount(): BelongsTo
    {
        return $this->belongsTo(ChartAccount::class, 'income_account_id');
    }

    /**
     * Get the bank account that owns the other income.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'received_in');
    }

    /**
     * Get the display name for received_in.
     */
    public function getReceivedInDisplayAttribute()
    {
        if ($this->bankAccount) {
            return $this->bankAccount->name;
        }
        return $this->received_in; // Fallback to the raw value
    }

    /**
     * Get the creator that owns the other income.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the approver that owns the other income.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the company that owns the other income.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the other income.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
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
     * Scope to filter approved records.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to filter pending records.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKey()
    {
        return Hashids::encode($this->getKey());
    }

    /**
     * Create GL transactions for this other income.
     */
    public function createGlTransactions()
    {
        // Credit the income account (income increases credit side)
        GlTransaction::create([
            'chart_account_id' => $this->income_account_id,
            'amount' => $this->amount,
            'nature' => 'credit',
            'transaction_id' => $this->id,
            'transaction_type' => 'other_income',
            'date' => $this->transaction_date,
            'description' => $this->description,
            'branch_id' => $this->branch_id,
            'user_id' => $this->created_by,
        ]);

        // Debit the bank account (assuming received_in is bank account ID)
        if (is_numeric($this->received_in)) {
            $bankAccount = BankAccount::find($this->received_in);
            if ($bankAccount && $bankAccount->chart_account_id) {
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'amount' => $this->amount,
                    'nature' => 'debit',
                    'transaction_id' => $this->id,
                    'transaction_type' => 'other_income',
                    'date' => $this->transaction_date,
                    'description' => $this->description,
                    'branch_id' => $this->branch_id,
                    'user_id' => $this->created_by,
                ]);
            }
        }
    }
}