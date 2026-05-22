<?php

namespace App\Services\Hospital;

use App\Models\Customer;
use App\Models\Hospital\Patient;
use App\Models\Hospital\Visit;
use App\Models\Sales\SalesInvoice;

class PatientCustomerResolver
{
    /**
     * Find a patient linked to a sales customer (same matching rules as reception billing).
     */
    public static function findPatientForCustomer(Customer $customer): ?Patient
    {
        $baseQuery = Patient::query()
            ->where('company_id', $customer->company_id)
            ->with('insuranceType');

        $customerName = trim((string) $customer->name);

        // Prefer exact name match (customer name is set when created from reception billing).
        if ($customerName !== '') {
            $byName = (clone $baseQuery)
                ->whereRaw('LOWER(TRIM(CONCAT(first_name, \' \', last_name))) = ?', [strtolower($customerName)])
                ->first();
            if ($byName) {
                return $byName;
            }
        }

        if ($customer->phone) {
            $byPhone = (clone $baseQuery)->where('phone', $customer->phone)->get();
            if ($byPhone->count() === 1) {
                return $byPhone->first();
            }
            if ($byPhone->count() > 1 && $customerName !== '') {
                $named = $byPhone->first(
                    fn (Patient $patient) => strcasecmp(trim($patient->full_name), $customerName) === 0
                );
                if ($named) {
                    return $named;
                }
            }
            if ($byPhone->isNotEmpty()) {
                return $byPhone->first();
            }
        }

        if ($customer->email) {
            $byEmail = (clone $baseQuery)->where('email', $customer->email)->get();
            if ($byEmail->count() === 1) {
                return $byEmail->first();
            }
            if ($byEmail->count() > 1 && $customerName !== '') {
                $named = $byEmail->first(
                    fn (Patient $patient) => strcasecmp(trim($patient->full_name), $customerName) === 0
                );
                if ($named) {
                    return $named;
                }
            }
            if ($byEmail->isNotEmpty()) {
                return $byEmail->first();
            }
        }

        if ($customerName !== '') {
            return (clone $baseQuery)
                ->whereRaw('LOWER(TRIM(CONCAT(first_name, \' \', last_name))) = ?', [strtolower($customerName)])
                ->first();
        }

        return null;
    }

    /**
     * Effective insurance type for billing (FK, or legacy name match).
     */
    public static function resolveInsuranceTypeForPatient(Patient $patient): ?\App\Models\Hospital\HospitalInsuranceType
    {
        if ($patient->insurance_type_id) {
            $type = $patient->insuranceType;
            if ($type && !$type->is_none) {
                return $type;
            }
            if ($type?->is_none) {
                return null;
            }
        }

        $label = trim((string) ($patient->insurance_type ?? ''));
        if ($label === '' || strcasecmp($label, 'none') === 0) {
            return null;
        }

        return \App\Models\Hospital\HospitalInsuranceType::forCompany($patient->company_id)
            ->active()
            ->where('is_none', false)
            ->where(function ($q) use ($label) {
                $q->where('name', $label)->orWhere('code', $label);
            })
            ->first();
    }

    /**
     * Hospital navigation context for a sales invoice (patient / visit from notes).
     *
     * @return array{url: string, hint: string}|null
     */
    public static function hospitalLinkForInvoice(SalesInvoice $invoice): ?array
    {
        $patient = null;
        $visit = null;

        if ($invoice->relationLoaded('customer') ? $invoice->customer : $invoice->customer()->first()) {
            $customer = $invoice->customer;
            $patient = self::findPatientForCustomer($customer);
        }

        if (!empty($invoice->notes) && preg_match('/Visit\s*#([A-Za-z0-9-]+)/i', $invoice->notes, $matches)) {
            $visit = Visit::where('company_id', $invoice->company_id)
                ->where('visit_number', $matches[1])
                ->first();
            if ($visit && !$patient) {
                $patient = $visit->patient;
            }
        }

        if (!$patient && !$visit) {
            return null;
        }

        if ($visit) {
            return [
                'url' => route('hospital.reception.visits.show', $visit->id),
                'hint' => "Visit {$visit->visit_number}" . ($patient ? " — {$patient->full_name}" : ''),
            ];
        }

        return [
            'url' => route('hospital.reception.patients.show', $patient->id),
            'hint' => $patient->full_name,
        ];
    }
}
