<?php

namespace App\Services;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;

class KitchenService
{
    public function __construct(protected OrderRepositoryInterface $orders) {}

    public function dashboard(): array
    {
        return [
            'pending' => $this->orders->paginate(['status' => Order::STATUS_PENDING], 50)->items(),
            'confirmed' => $this->orders->paginate(['status' => Order::STATUS_CONFIRMED], 50)->items(),
            'preparing' => $this->orders->paginate(['status' => Order::STATUS_PREPARING], 50)->items(),
            'ready' => $this->orders->paginate(['status' => Order::STATUS_READY], 50)->items(),
            'completed_today' => $this->orders->paginate(['status' => Order::STATUS_COMPLETED, 'date' => today()->toDateString()], 50)->items(),
        ];
    }
}
