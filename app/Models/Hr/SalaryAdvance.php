<?php

namespace App\Models\Hr;

use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\ChartAccount;
use App\Models\Company;
use App\Models\Hr\Employee;
use App\Models\GlTransaction;
use App\Models\Payment;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vinkla\Hashids\Facades\Hashids;

class SalaryAdvance extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'hr_salary_advances';

    protected $fillable = [
        'company_id',
        'employee_id',
        'bank_account_id',
        'user_id',
        'branch_id',
        'reference',
        'date',
        'amount',
        'monthly_deduction',
        'reason',
        'is_active',
        'payment_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }


    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }


    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // Accessors
    public function getEncodedIdAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedMonthlyDeductionAttribute()
    {
        return number_format($this->monthly_deduction, 2);
    }

    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('M d, Y') : 'N/A';
    }


    public function getEmployeeNameAttribute()
    {
        return $this->employee ? $this->employee->full_name : 'N/A';
    }

    public function getBankAccountNameAttribute()
    {
        return $this->bankAccount ? $this->bankAccount->name : 'N/A';
    }

    /**
     * Get total deductions made from payroll
     */
    public function getTotalDeductionsAttribute()
    {
        return \App\Models\PayrollEmployee::whereHas('payroll', function ($q) {
                $q->where('company_id', $this->company_id);
            })
            ->whereHas('employee', function ($q) {
                $q->where('id', $this->employee_id);
            })
            ->where('salary_advance', '>', 0)
            ->get()
            ->sum(function ($payrollEmployee) {
                // Check if this advance was included in the deduction
                // We'll need to track this better, but for now, we'll estimate
                // based on monthly_deduction matching
                $payrollDate = \Carbon\Carbon::create(
                    $payrollEmployee->payroll->year,
                    $payrollEmployee->payroll->month,
                    1
                )->endOfMonth();

                if ($this->date <= $payrollDate && $this->is_active) {
                    return min($this->monthly_deduction, $payrollEmployee->salary_advance);
                }
                return 0;
            });
    }

    /**
     * Get remaining balance
     */
    public function getRemainingBalanceAttribute()
    {
        $totalDeductions = $this->getTotalDeductionsAttribute();
        return max(0, $this->amount - $totalDeductions);
    }

    /**
     * Check if advance is fully repaid
     */
    public function isFullyRepaid(): bool
    {
        return $this->remaining_balance <= 0;
    }

    /**
     * Record a repayment from payroll
     */
    public function recordRepayment($amount, $repaymentDate, $payrollId = null, $payrollEmployeeId = null)
    {
        $remainingBalance = $this->remaining_balance;
        $actualRepayment = min($amount, $remainingBalance);

        // Auto-deactivate if fully repaid
        if ($remainingBalance - $actualRepayment <= 0) {
            $this->is_active = false;
            $this->save();
        }

        return $actualRepayment;
    }
}
