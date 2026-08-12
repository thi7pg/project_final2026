<?php

namespace App\Contracts\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Order;

    public function findByOrderNumber(string $orderNumber): ?Order;

    public function create(array $data): Order;

    public function update(Order $order, array $data): Order;

    public function countByStatusToday(string $status): int;

    public function todayOrderCount(): int;

    public function recent(int $limit = 10): Collection;
}
