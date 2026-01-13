<?php

namespace App\Models\Hr;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class ExternalLoan extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'hr_external_loans';

    protected $fillable = [
        'company_id',
        'employee_id',
        'institution_name',
        'total_loan',
        'monthly_deduction',
        'date_end_of_loan',
        'date',
        'is_active',
    ];

    protected $casts = [
        'total_loan' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'date_end_of_loan' => 'date',
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getEncodedIdAttribute(): string
    {
        return Hashids::encode($this->id);
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
            ->where('loans', '>', 0)
            ->get()
            ->sum(function ($payrollEmployee) {
                // Check if this loan was included in the deduction
                $payrollDate = \Carbon\Carbon::create(
                    $payrollEmployee->payroll->year,
                    $payrollEmployee->payroll->month,
                    1
                )->endOfMonth();

                if ($this->date <= $payrollDate
                    && $this->is_active
                    && ($this->date_end_of_loan === null || $this->date_end_of_loan >= $payrollDate)) {
                    return min($this->monthly_deduction, $payrollEmployee->loans);
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
        return max(0, $this->total_loan - $totalDeductions);
    }

    /**
     * Check if loan is fully repaid
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
