<?php

namespace App\Services;

use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Events\PaymentCompleted;
use App\Exceptions\ApiException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentService
{
    public function __construct(
        protected PaymentRepositoryInterface $payments,
        protected ActivityLogService $activityLog,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->payments->paginate($filters, $perPage);
    }

    public function markPaid(Order $order, User $cashier): Payment
    {
        $payment = $this->payments->findByOrder($order);

        if (! $payment) {
            throw new ApiException('Payment record not found for this order.', 404);
        }

        if ($payment->status === Payment::STATUS_PAID) {
            throw new ApiException('This order has already been paid.', 422);
        }

        $payment = $this->payments->update($payment, [
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
            'received_by' => $cashier->id,
        ]);

        $this->activityLog->log('payment.completed', "Payment received for order {$order->order_number}", $payment, $cashier);

        PaymentCompleted::dispatch($payment);

        return $payment;
    }
}
