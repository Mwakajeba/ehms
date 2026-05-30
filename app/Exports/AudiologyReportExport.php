<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class AudiologyReportExport implements FromView, WithTitle, ShouldAutoSize
{
    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function __construct(
        private array $rows,
        private $audiometryItems,
        private $deviceItems,
        private Carbon $startDate,
        private Carbon $endDate,
        private string $periodLabel,
    ) {}

    public function view(): View
    {
        return view('hospital.reports.exports.audiology-excel', [
            'rows' => $this->rows,
            'audiometryItems' => $this->audiometryItems,
            'deviceItems' => $this->deviceItems,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'periodLabel' => $this->periodLabel,
        ]);
    }

    public function title(): string
    {
        return 'Audiology Report';
    }
}
