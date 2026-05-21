<?php

namespace App\Services\Hospital;

use App\Models\Customer;
use App\Models\Hospital\Patient;

class PatientCustomerResolver
{
    /**
     * Find a patient linked to a sales customer (same matching rules as reception billing).
     */
    public static function findPatientForCustomer(Customer $customer): ?Patient
    {
        $query = Patient::query()
            ->where('company_id', $customer->company_id)
            ->with('insuranceType');

        if ($customer->phone) {
            $patient = (clone $query)->where('phone', $customer->phone)->first();
            if ($patient) {
                return $patient;
            }
        }

        if ($customer->email) {
            $patient = (clone $query)->where('email', $customer->email)->first();
            if ($patient) {
                return $patient;
            }
        }

        return (clone $query)->whereRaw("CONCAT(first_name, ' ', last_name) = ?", [$customer->name])->first();
    }
}
