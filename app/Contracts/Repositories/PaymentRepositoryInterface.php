<?php

namespace App\Contracts\Repositories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Payment;

    public function findByOrder(Order $order): ?Payment;

    public function update(Payment $payment, array $data): Payment;

    public function todayRevenue(): float;
}
