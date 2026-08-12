<?php

namespace App\Services;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Contracts\Repositories\TableRepositoryInterface;
use App\Events\OrderCancelled;
use App\Events\OrderPlaced;
use App\Events\OrderReady;
use App\Exceptions\ApiException;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Allowed status transitions for the order lifecycle state machine.
     */
    protected const TRANSITIONS = [
        Order::STATUS_PENDING => [Order::STATUS_CONFIRMED, Order::STATUS_CANCELLED],
        Order::STATUS_CONFIRMED => [Order::STATUS_PREPARING, Order::STATUS_CANCELLED],
        Order::STATUS_PREPARING => [Order::STATUS_READY, Order::STATUS_CANCELLED],
        Order::STATUS_READY => [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED],
        Order::STATUS_COMPLETED => [],
        Order::STATUS_CANCELLED => [],
    ];

    protected const STATUS_TIMESTAMP_COLUMN = [
        Order::STATUS_CONFIRMED => 'confirmed_at',
        Order::STATUS_PREPARING => 'preparing_at',
        Order::STATUS_READY => 'ready_at',
        Order::STATUS_COMPLETED => 'completed_at',
        Order::STATUS_CANCELLED => 'cancelled_at',
    ];

    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected PaymentRepositoryInterface $payments,
        protected TableRepositoryInterface $tables,
        protected CustomerRepositoryInterface $customers,
        protected CartService $cartService,
        protected ActivityLogService $activityLog,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->orders->paginate($filters, $perPage);
    }

    public function find(int $id): Order
    {
        $order = $this->orders->find($id);

        if (! $order) {
            throw new ApiException('Order not found.', 404);
        }

        return $order;
    }

    public function findByOrderNumber(string $orderNumber): Order
    {
        $order = $this->orders->findByOrderNumber($orderNumber);

        if (! $order) {
            throw new ApiException('Order not found.', 404);
        }

        return $order;
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int, notes?: string|null}>  $items
     */
    public function placeOrder(string $qrToken, string $customerName, ?string $customerPhone, array $items, ?string $notes): Order
    {
        $table = $this->cartService->resolveTable($qrToken);
        $cart = $this->cartService->buildCart($items);

        return DB::transaction(function () use ($table, $customerName, $customerPhone, $cart, $notes) {
            $customer = $this->customers->firstOrCreate($customerName, $customerPhone);

            $order = $this->orders->create([
                'order_number' => $this->generateOrderNumber(),
                'table_id' => $table->id,
                'customer_id' => $customer->id,
                'status' => Order::STATUS_PENDING,
                'subtotal' => $cart['subtotal'],
                'tax_amount' => $cart['tax_amount'],
                'service_charge_amount' => $cart['service_charge_amount'],
                'total_amount' => $cart['total_amount'],
                'notes' => $notes,
            ]);

            foreach ($cart['items'] as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'subtotal' => $line['subtotal'],
                    'notes' => $line['notes'],
                ]);
            }

            $this->payments->create([
                'order_id' => $order->id,
                'amount' => $cart['total_amount'],
                'method' => 'cash',
                'status' => Payment::STATUS_PENDING,
            ]);

            if ($table->status === DiningTable::STATUS_AVAILABLE) {
                $this->tables->update($table, ['status' => DiningTable::STATUS_OCCUPIED]);
            }

            $order = $this->orders->find($order->id);

            $this->activityLog->log('order.placed', "Order {$order->order_number} placed", $order);

            OrderPlaced::dispatch($order);

            return $order;
        });
    }

    public function updateStatus(Order $order, string $newStatus, ?string $cancelReason = null): Order
    {
        $allowed = self::TRANSITIONS[$order->status] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            throw new ApiException(
                "Cannot transition order from '{$order->status}' to '{$newStatus}'.",
                422,
                ['status' => ["Invalid status transition from {$order->status} to {$newStatus}."]]
            );
        }

        if ($newStatus === Order::STATUS_CANCELLED && ! $cancelReason) {
            throw new ApiException(
                'A cancellation reason is required.',
                422,
                ['cancelled_reason' => ['The cancelled reason field is required.']]
            );
        }

        $data = ['status' => $newStatus];

        if (isset(self::STATUS_TIMESTAMP_COLUMN[$newStatus])) {
            $data[self::STATUS_TIMESTAMP_COLUMN[$newStatus]] = now();
        }

        if ($newStatus === Order::STATUS_CANCELLED) {
            $data['cancelled_reason'] = $cancelReason;
        }

        $order = $this->orders->update($order, $data);

        $this->syncTableStatus($order);

        $this->activityLog->log('order.status_updated', "Order {$order->order_number} status changed to {$newStatus}", $order, properties: $data);

        match ($newStatus) {
            Order::STATUS_READY => OrderReady::dispatch($order),
            Order::STATUS_CANCELLED => OrderCancelled::dispatch($order),
            default => null,
        };

        return $order;
    }

    protected function syncTableStatus(Order $order): void
    {
        if (! in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED], true)) {
            return;
        }

        $table = $order->table;

        if ($table && $table->activeOrders()->doesntExist() && $table->status === DiningTable::STATUS_OCCUPIED) {
            $this->tables->update($table, ['status' => DiningTable::STATUS_AVAILABLE]);
        }
    }

    protected function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
