<?php

namespace App\Services\Hospital;

use App\Models\Hospital\Patient;
use App\Models\Hospital\Visit;
use App\Models\Inventory\Item;
use App\Models\Sales\SalesInvoice;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AudiologyReportService
{
    /**
     * @return array{
     *     rows: array<int, array<string, mixed>>,
     *     audiometryItems: Collection,
     *     deviceItems: Collection,
     *     startDate: Carbon,
     *     endDate: Carbon,
     *     periodLabel: string,
     *     totals: array{service_totals: array<int, float>, product_totals: array<int, float>, grand_total: float, visit_count: int}
     * }
     */
    public function build(int $companyId, ?int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        $audiometryItems = Item::query()
            ->where('company_id', $companyId)
            ->where('item_type', 'service')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'item_type']);

        $deviceItems = Item::query()
            ->where('company_id', $companyId)
            ->where('item_type', 'product')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'item_type']);

        $visits = Visit::query()
            ->with([
                'patient.insuranceType',
                'visitDepartments.department',
                'audiologyResults',
            ])
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->where(function ($q) use ($companyId, $branchId, $startDate, $endDate) {
                $q->whereHas('visitDepartments', function ($vd) {
                    $vd->whereHas('department', fn ($d) => $d->where('type', 'audiology'));
                })
                    ->orWhereHas('audiologyResults')
                    ->orWhereIn('id', $this->visitIdsFromAudiologyInvoices($companyId, $branchId, $startDate, $endDate));
            })
            ->orderBy('visit_date')
            ->orderBy('visit_number')
            ->get();

        $rows = [];
        $no = 1;

        foreach ($visits as $visit) {
            $patient = $visit->patient;
            if (!$patient) {
                continue;
            }

            $invoices = $this->audiologyInvoicesForVisit($visit, $companyId, $branchId);
            $amounts = $this->aggregateLineAmounts($invoices, $audiometryItems, $deviceItems);
            $payment = $this->resolvePayment($patient, $invoices);

            $rows[] = [
                'no' => $no++,
                'patient_name' => $patient->full_name,
                'ip_no' => $patient->mrn,
                'payment_cash' => $payment['cash'],
                'payment_insurance' => $payment['insurance'],
                'service_amounts' => $amounts['services'],
                'product_amounts' => $amounts['products'],
                'contact' => $patient->phone ?? '',
                'visit_date' => $visit->visit_date,
            ];
        }

        $periodLabel = $startDate->format('F Y');
        if (!$startDate->isSameMonth($endDate) || !$startDate->isSameYear($endDate)) {
            $periodLabel = $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y');
        }

        return [
            'rows' => $rows,
            'audiometryItems' => $audiometryItems,
            'deviceItems' => $deviceItems,
            'totals' => $this->computeTotals($rows, $audiometryItems, $deviceItems),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodLabel' => strtoupper($periodLabel),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  Collection<int, Item>  $audiometryItems
     * @param  Collection<int, Item>  $deviceItems
     * @return array{service_totals: array<int, float>, product_totals: array<int, float>, grand_total: float, visit_count: int}
     */
    private function computeTotals(array $rows, Collection $audiometryItems, Collection $deviceItems): array
    {
        $serviceTotals = $audiometryItems->mapWithKeys(fn (Item $item) => [$item->id => 0.0])->all();
        $productTotals = $deviceItems->mapWithKeys(fn (Item $item) => [$item->id => 0.0])->all();

        foreach ($rows as $row) {
            foreach ($row['service_amounts'] as $itemId => $amount) {
                if ($amount !== null && $amount > 0) {
                    $serviceTotals[$itemId] = ($serviceTotals[$itemId] ?? 0) + (float) $amount;
                }
            }
            foreach ($row['product_amounts'] as $itemId => $amount) {
                if ($amount !== null && $amount > 0) {
                    $productTotals[$itemId] = ($productTotals[$itemId] ?? 0) + (float) $amount;
                }
            }
        }

        return [
            'service_totals' => $serviceTotals,
            'product_totals' => $productTotals,
            'grand_total' => (float) array_sum($serviceTotals) + (float) array_sum($productTotals),
            'visit_count' => count($rows),
        ];
    }

    /** @return array<int, int> */
    private function visitIdsFromAudiologyInvoices(int $companyId, ?int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        $invoices = SalesInvoice::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('notes', 'like', '%Audiology test bill%')
            ->get(['id', 'notes']);

        $ids = [];
        foreach ($invoices as $invoice) {
            if (!preg_match('/Visit\s*#([A-Za-z0-9-]+)/i', (string) $invoice->notes, $matches)) {
                continue;
            }
            $visit = Visit::query()
                ->where('company_id', $companyId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('visit_number', $matches[1])
                ->whereBetween('visit_date', [$startDate, $endDate])
                ->value('id');

            if ($visit) {
                $ids[] = $visit;
            }
        }

        return array_values(array_unique($ids));
    }

    /** @return Collection<int, SalesInvoice> */
    private function audiologyInvoicesForVisit(Visit $visit, int $companyId, ?int $branchId): Collection
    {
        $pattern = '%Visit #' . $visit->visit_number . '%';

        return SalesInvoice::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('notes', 'like', '%Audiology%')
            ->where('notes', 'like', $pattern)
            ->with(['items', 'insurancePayments.insuranceType'])
            ->get();
    }

    /**
     * @param  Collection<int, SalesInvoice>  $invoices
     * @param  Collection<int, Item>  $audiometryItems
     * @param  Collection<int, Item>  $deviceItems
     * @return array{services: array<int, float|null>, products: array<int, float|null>}
     */
    private function aggregateLineAmounts(Collection $invoices, Collection $audiometryItems, Collection $deviceItems): array
    {
        $services = $audiometryItems->mapWithKeys(fn (Item $item) => [$item->id => null])->all();
        $products = $deviceItems->mapWithKeys(fn (Item $item) => [$item->id => null])->all();

        $serviceIds = array_fill_keys(array_keys($services), true);
        $productIds = array_fill_keys(array_keys($products), true);

        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $line) {
                $itemId = (int) $line->inventory_item_id;
                if ($itemId <= 0) {
                    continue;
                }

                $lineTotal = (float) $line->line_total;

                if (isset($serviceIds[$itemId])) {
                    $services[$itemId] = ($services[$itemId] ?? 0) + $lineTotal;
                } elseif (isset($productIds[$itemId])) {
                    $products[$itemId] = ($products[$itemId] ?? 0) + $lineTotal;
                }
            }
        }

        return ['services' => $services, 'products' => $products];
    }

    /**
     * @param  Collection<int, SalesInvoice>  $invoices
     * @return array{cash: string, insurance: string}
     */
    private function resolvePayment(Patient $patient, Collection $invoices): array
    {
        foreach ($invoices as $invoice) {
            $insurancePayment = $invoice->insurancePayments->first();
            if ($insurancePayment?->insuranceType) {
                return [
                    'cash' => '',
                    'insurance' => $insurancePayment->insuranceType->name,
                ];
            }
        }

        $insuranceType = PatientCustomerResolver::resolveInsuranceTypeForPatient($patient);
        if ($insuranceType) {
            return [
                'cash' => '',
                'insurance' => $insuranceType->name,
            ];
        }

        return [
            'cash' => 'CASH',
            'insurance' => '',
        ];
    }

    public static function formatAmount(?float $amount): string
    {
        if ($amount === null || $amount <= 0) {
            return '';
        }

        return number_format($amount, 0, '.', ',');
    }

    /** @param array<int, float|null> $amounts */
    public static function amountForItem(array $amounts, int $itemId): string
    {
        return self::formatAmount($amounts[$itemId] ?? null);
    }

    public static function formatTotalAmount(float $amount): string
    {
        return number_format($amount, 0, '.', ',');
    }
}
