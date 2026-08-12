<?php

namespace App\Services;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Order;

class DashboardService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected PaymentRepositoryInterface $payments,
        protected ProductRepositoryInterface $products,
    ) {}

    public function summary(): array
    {
        return [
            'today_orders' => $this->orders->todayOrderCount(),
            'today_revenue' => $this->payments->todayRevenue(),
            'pending_orders' => $this->orders->countByStatusToday(Order::STATUS_PENDING),
            'preparing_orders' => $this->orders->countByStatusToday(Order::STATUS_PREPARING),
            'completed_orders' => $this->orders->countByStatusToday(Order::STATUS_COMPLETED),
            'popular_menu' => $this->products->popular(5, 30),
            'recent_orders' => $this->orders->recent(10),
        ];
    }
}
