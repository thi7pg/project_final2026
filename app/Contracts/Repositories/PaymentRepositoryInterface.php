<?php

namespace App\Contracts\Repositories;

use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PaymentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Payment;

    public function findByOrder(Order $order): ?Payment;

    public function update(Payment $payment, array $data): Payment;

    public function todayRevenue(): float;

    public function revenueSeries(Carbon $from, Carbon $to, string $groupBy): Collection;
}
