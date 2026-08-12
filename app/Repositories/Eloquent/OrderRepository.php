<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::query()->with(['table', 'customer', 'items', 'payment']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['table_id'])) {
            $query->where('table_id', $filters['table_id']);
        }

        if (! empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): ?Order
    {
        return Order::query()->with(['table', 'customer', 'items.product', 'payment'])->find($id);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::query()
            ->with(['table', 'items.product', 'payment'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function create(array $data): Order
    {
        return Order::query()->create($data);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order->refresh();
    }

    public function countByStatusToday(string $status): int
    {
        return Order::query()
            ->where('status', $status)
            ->whereDate('created_at', today())
            ->count();
    }

    public function todayOrderCount(): int
    {
        return Order::query()->whereDate('created_at', today())->count();
    }

    public function recent(int $limit = 10): Collection
    {
        return Order::query()
            ->with(['table', 'customer'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
