<?php

namespace App\Services;

use App\Contracts\Repositories\PaymentRepositoryInterface;
use Carbon\Carbon;

class ReportService
{
    public function __construct(protected PaymentRepositoryInterface $payments) {}

    public function revenue(string $from, string $to, string $groupBy): array
    {
        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate = Carbon::parse($to)->endOfDay();

        $series = $this->payments->revenueSeries($fromDate, $toDate, $groupBy);

        return [
            'group_by' => $groupBy,
            'from' => $fromDate->toDateString(),
            'to' => $toDate->toDateString(),
            'series' => $series->map(fn ($row) => [
                'period' => $row->period,
                'revenue' => (float) $row->revenue,
                'orders_count' => (int) $row->orders_count,
            ])->values()->all(),
            'total_revenue' => (float) $series->sum('revenue'),
            'total_orders' => (int) $series->sum('orders_count'),
        ];
    }
}
