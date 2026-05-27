<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PatientRegistrationExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /** @var \Illuminate\Support\Collection<int, mixed> */
    private Collection $patients;

    public function __construct($patients)
    {
        $this->patients = collect($patients);
    }

    public function collection()
    {
        return $this->patients;
    }

    public function headings(): array
    {
        return [
            'Registered At',
            'MRN',
            'Full Name',
            'Gender',
            'Age',
            'Phone',
            'Email',
            'Insurance',
            'Insurance No.',
            'Admitted Date',
            'Status',
            'Registered By',
        ];
    }

    public function map($patient): array
    {
        return [
            optional($patient->created_at)->format('Y-m-d H:i'),
            $patient->mrn,
            $patient->full_name,
            $patient->gender ? ucfirst($patient->gender) : '',
            $patient->age ?? '',
            $patient->phone ?? '',
            $patient->email ?? '',
            $patient->insurance_type_name,
            $patient->insurance_number ?? '',
            $patient->admitted_date ? $patient->admitted_date->format('Y-m-d') : '',
            $patient->is_active ? 'Active' : 'Inactive',
            $patient->creator?->name ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0D6EFD'],
            ],
        ]);

        return [];
    }
}

