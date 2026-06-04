<?php

namespace App\Services\Hospital;

use App\Models\Hospital\Visit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VisitBillingClearance
{
    /**
     * Whether this visit may proceed (cleared visit bill or paid pre-bill for session/visit branch).
     */
    public static function visitHasClearance(Visit $visit, int $companyId, int $branchId): bool
    {
        if ($visit->bills()->where('clearance_status', 'cleared')->exists()) {
            return true;
        }

        return static::visitHasPaidPrebill($visit, $companyId, $branchId);
    }

    /**
     * Paid sales invoice linked to this visit at the given branch.
     */
    public static function visitHasPaidPrebill(Visit $visit, int $companyId, int $branchId): bool
    {
        $visit->loadMissing('patient');
        $patient = $visit->patient;
        if (!$patient) {
            return false;
        }

        return DB::table('sales_invoices')
            ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
            ->where('sales_invoices.company_id', $companyId)
            ->where('sales_invoices.branch_id', $branchId)
            ->where('sales_invoices.status', 'paid')
            ->where(function ($paid) use ($visit, $patient) {
                $paid->where('sales_invoices.notes', 'like', '%Visit #' . $visit->visit_number . '%');

                if ($patient->phone) {
                    $paid->orWhere('customers.phone', $patient->phone);
                }
                if ($patient->email) {
                    $paid->orWhere('customers.email', $patient->email);
                }
                $paid->orWhereRaw(
                    'LOWER(TRIM(customers.name)) = LOWER(TRIM(?))',
                    [trim($patient->first_name . ' ' . $patient->last_name)]
                );
            })
            ->exists();
    }

    /** Departments scoped to a branch (or company-wide when branch_id is null). */
    public static function scopeDepartmentsForBranch(Builder $query, ?int $branchId): Builder
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)->orWhereNull('branch_id');
        });
    }
    /**
     * Visits with cleared visit bills OR a paid sales invoice for this visit / patient.
     */
    public static function applyClearedBillOrPaidInvoice(Builder $query, int $companyId, int $branchId): Builder
    {
        return $query->where(function ($outer) use ($companyId, $branchId) {
            $outer->whereHas('bills', function ($q) {
                $q->where('clearance_status', 'cleared');
            })
            ->orWhereExists(function ($subQuery) use ($companyId, $branchId) {
                $subQuery->select(DB::raw(1))
                    ->from('sales_invoices')
                    ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
                    ->join('patients', 'patients.id', '=', 'visits.patient_id')
                    ->where('sales_invoices.company_id', $companyId)
                    ->where('sales_invoices.branch_id', $branchId)
                    ->where('sales_invoices.status', 'paid')
                    ->where(function ($paid) {
                        $paid->whereRaw("sales_invoices.notes LIKE CONCAT('%Visit #', visits.visit_number, '%')")
                            ->orWhere(function ($match) {
                                $match->whereColumn('customers.phone', 'patients.phone')
                                    ->whereNotNull('patients.phone')
                                    ->where('patients.phone', '!=', '')
                                    ->orWhereColumn('customers.email', 'patients.email')
                                    ->orWhereRaw(
                                        'LOWER(TRIM(customers.name)) = LOWER(TRIM(CONCAT(patients.first_name, \' \', patients.last_name)))'
                                    );
                            });
                    });
            });
        });
    }
}
