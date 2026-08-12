<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Events\OrderPlaced;
use App\Events\OrderReady;
use App\Events\PaymentCompleted;
use App\Services\Notifications\TelegramNotifier;

class SendTelegramNotification
{
    public function __construct(protected TelegramNotifier $telegram) {}

    public function handleOrderPlaced(OrderPlaced $event): void
    {
        $this->telegram->newOrder($event->order);
    }

    public function handleOrderCancelled(OrderCancelled $event): void
    {
        $this->telegram->orderCancelled($event->order);
    }

    public function handleOrderReady(OrderReady $event): void
    {
        $this->telegram->orderReady($event->order);
    }

    public function handlePaymentCompleted(PaymentCompleted $event): void
    {
        $this->telegram->paymentCompleted($event->payment);
    }
}
