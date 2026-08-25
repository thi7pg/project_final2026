<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::query()->with(['order.table', 'receivedByUser']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Payment
    {
        return Payment::query()->create($data);
    }

    public function findByOrder(Order $order): ?Payment
    {
        return Payment::query()->where('order_id', $order->id)->first();
    }

    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);

        return $payment->refresh();
    }

    public function todayRevenue(): float
    {
        return (float) Payment::query()
            ->where('status', Payment::STATUS_PAID)
            ->whereDate('paid_at', today())
            ->sum('amount');
    }

    public function revenueSeries(Carbon $from, Carbon $to, string $groupBy): Collection
    {
        $format = match ($groupBy) {
            'month' => '%Y-%m',
            'week' => '%x-W%v',
            default => '%Y-%m-%d',
        };

        return Payment::query()
            ->selectRaw("DATE_FORMAT(paid_at, '{$format}') as period, SUM(amount) as revenue, COUNT(*) as orders_count")
            ->where('status', Payment::STATUS_PAID)
            ->whereBetween('paid_at', [$from, $to])
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }
}
