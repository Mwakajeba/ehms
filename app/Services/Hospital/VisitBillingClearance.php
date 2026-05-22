<?php

namespace App\Services\Hospital;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VisitBillingClearance
{
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
