<?php

namespace App\Services\Hospital;

use App\Models\Hospital\AudiologyResult;
use App\Models\Hospital\Consultation;
use App\Models\Hospital\DiagnosisExplanation;
use App\Models\Hospital\DentalRecord;
use App\Models\Hospital\FamilyPlanningRecord;
use App\Models\Hospital\InjectionRecord;
use App\Models\Hospital\InsuranceInvoicePayment;
use App\Models\Hospital\LabResult;
use App\Models\Hospital\Patient;
use App\Models\Hospital\PharmacyDispensation;
use App\Models\Hospital\RCHRecord;
use App\Models\Hospital\TriageVital;
use App\Models\Hospital\UltrasoundResult;
use App\Models\Hospital\VaccinationRecord;
use App\Models\Hospital\Visit;
use App\Models\Hospital\VisitBill;
use App\Models\Hospital\VisitPayment;

class PatientDeletionGuard
{
    /**
     * Record types linked to patient_id that block admin delete when present.
     *
     * @return array<string, int> Label => count
     */
    public static function attachedRecords(Patient $patient): array
    {
        $checks = [
            'Visits' => Visit::where('patient_id', $patient->id)->count(),
            'Visit bills' => VisitBill::where('patient_id', $patient->id)->count(),
            'Visit payments' => VisitPayment::where('patient_id', $patient->id)->count(),
            'Triage vitals' => TriageVital::where('patient_id', $patient->id)->count(),
            'Lab results' => LabResult::where('patient_id', $patient->id)->count(),
            'Ultrasound results' => UltrasoundResult::where('patient_id', $patient->id)->count(),
            'Audiology results' => AudiologyResult::where('patient_id', $patient->id)->count(),
            'Consultations' => Consultation::where('patient_id', $patient->id)->count(),
            'Pharmacy dispensations' => PharmacyDispensation::where('patient_id', $patient->id)->count(),
            'Dental records' => DentalRecord::where('patient_id', $patient->id)->count(),
            'RCH records' => RCHRecord::where('patient_id', $patient->id)->count(),
            'Vaccination records' => VaccinationRecord::where('patient_id', $patient->id)->count(),
            'Injection records' => InjectionRecord::where('patient_id', $patient->id)->count(),
            'Family planning records' => FamilyPlanningRecord::where('patient_id', $patient->id)->count(),
            'Diagnosis notes' => DiagnosisExplanation::where('patient_id', $patient->id)->count(),
            'Insurance invoice payments' => InsuranceInvoicePayment::where('patient_id', $patient->id)->count(),
        ];

        return array_filter($checks, fn (int $count) => $count > 0);
    }

    public static function canDelete(Patient $patient): bool
    {
        return self::attachedRecords($patient) === [];
    }

    public static function blockingMessage(Patient $patient): string
    {
        $records = self::attachedRecords($patient);
        if ($records === []) {
            return '';
        }

        $parts = [];
        foreach ($records as $label => $count) {
            $parts[] = "{$label} ({$count})";
        }

        return 'Cannot delete: patient has linked records — ' . implode(', ', $parts) . '.';
    }
}
