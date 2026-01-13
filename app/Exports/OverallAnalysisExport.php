<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class OverallAnalysisExport implements FromView, WithStyles, WithColumnWidths, WithTitle
{
    protected $analysisData;
    protected $request;

    public function __construct($analysisData, $request)
    {
        $this->analysisData = $analysisData;
        $this->request = $request;
    }

    public function view(): View
    {
        $company = auth()->user()->company;
        $generatedAt = now()->format('d/m/Y H:i:s');

        $filters = [];
        $academicYears = \App\Models\School\AcademicYear::where('company_id', auth()->user()->company_id)->get();
        $examTypes = \App\Models\School\ExamType::where('company_id', auth()->user()->company_id)->get();

        if ($this->request->filled('academic_year_id')) {
            $year = $academicYears->find($this->request->academic_year_id);
            $filters['academic_year'] = $year ? $year->year_name : 'Unknown';
        }
        if ($this->request->filled('exam_type_id')) {
            $examType = $examTypes->find($this->request->exam_type_id);
            $filters['exam_type'] = $examType ? $examType->name : 'Unknown';
        }

        return view('school.reports.exports.overall-analysis-excel', [
            'analysis' => $this->analysisData['analysis'],
            'subtotals' => $this->analysisData['subtotals'],
            'grandTotal' => $this->analysisData['grandTotal'],
            'company' => $company,
            'generatedAt' => $generatedAt,
            'filters' => $filters,
        ]);
    }

    public function title(): string
    {
        return 'Overall Analysis Report';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // S/N
            'B' => 20,  // Class
            'C' => 20,  // Stream
            'D' => 15,  // No. of Students
            'E' => 8,   // A
            'F' => 8,   // B
            'G' => 8,   // C
            'H' => 8,   // D
            'I' => 8,   // E
            'J' => 12,  // Class Mean
            'K' => 8,   // Grade
            'L' => 25,  // Class Teacher
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->analysisData['analysis']) + count($this->analysisData['subtotals']) + 10;

        // Header styling
        $sheet->getStyle('A1:L3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Merge header cells
        $sheet->mergeCells('A1:L1'); // Company name
        $sheet->mergeCells('A2:L2'); // Report title
        $sheet->mergeCells('E4:I4'); // Grade Distribution header

        // Table headers
        $sheet->getStyle('A5:L6')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Data rows
        $dataStartRow = 7;
        $dataEndRow = $dataStartRow + count($this->analysisData['analysis']) - 1;

        $sheet->getStyle('A' . $dataStartRow . ':L' . $dataEndRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Left align class and teacher columns
        $sheet->getStyle('B' . $dataStartRow . ':C' . $dataEndRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        $sheet->getStyle('L' . $dataStartRow . ':L' . $dataEndRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        // Subtotal rows styling
        $subtotalStartRow = $dataEndRow + 1;
        $subtotalEndRow = $subtotalStartRow + count($this->analysisData['subtotals']) - 1;

        if ($subtotalEndRow >= $subtotalStartRow) {
            $sheet->getStyle('A' . $subtotalStartRow . ':L' . $subtotalEndRow)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF3CD'],
                ],
                'font' => [
                    'bold' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Grand total row styling
        $grandTotalRow = $subtotalEndRow + 1;
        $sheet->getStyle('A' . $grandTotalRow . ':L' . $grandTotalRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'CCE5FF'],
            ],
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Summary section
        $summaryStartRow = $grandTotalRow + 3;
        $sheet->getStyle('A' . $summaryStartRow . ':L' . ($summaryStartRow + 10))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        return [];
    }
}