<?php

namespace App\Exports;

use App\Models\School\Classe;
use App\Models\School\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PrepaidAccountTemplateExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $class;

    public function __construct(Classe $class)
    {
        $this->class = $class;
    }

    public function collection()
    {
        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id') ?: auth()->user()->branch_id;

        return Student::where('class_id', $this->class->id)
            ->where('company_id', $companyId)
            ->when($branchId, fn($q) => $q->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            }))
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'admission_number', 'first_name', 'last_name']);
    }

    public function headings(): array
    {
        return [
            'Admission Number',
            'Student Name',
            'Amount',
            'Reference',
            'Notes'
        ];
    }

    public function map($student): array
    {
        return [
            $student->admission_number ?? '',
            ($student->first_name ?? '') . ' ' . ($student->last_name ?? ''),
            '', // Amount - to be filled by user
            '', // Reference - optional
            '', // Notes - optional
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ]);

        // Set text color for header
        $sheet->getStyle('A1:E1')->getFont()->getColor()->setRGB('FFFFFF');

        // Add borders to all cells
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:E' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Freeze first row
        $sheet->freezePane('A2');

        return [];
    }
}

