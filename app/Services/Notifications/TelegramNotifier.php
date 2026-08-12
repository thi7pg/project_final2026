<?php

namespace App\Services\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    public function send(string $message): void
    {
        if (! config('telegram.enabled')) {
            return;
        }

        try {
            Http::timeout(5)->post(
                sprintf('https://api.telegram.org/bot%s/sendMessage', config('telegram.bot_token')),
                [
                    'chat_id' => config('telegram.chat_id'),
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]
            )->throw();
        } catch (\Throwable $e) {
            Log::warning('Telegram notification failed: '.$e->getMessage());
        }
    }

    public function newOrder(Order $order): void
    {
        $this->send(sprintf(
            "🔔 <b>New Order</b>\nOrder #%s\nTable %s\n%d Item(s)\nTotal: %s%.2f",
            $order->order_number,
            $order->table?->table_number,
            $order->items->sum('quantity'),
            $this->currencySymbol(),
            $order->total_amount
        ));
    }

    public function orderCancelled(Order $order): void
    {
        $this->send(sprintf(
            "❌ <b>Order Cancelled</b>\nOrder #%s\nTable %s\nReason: %s",
            $order->order_number,
            $order->table?->table_number,
            $order->cancelled_reason ?? 'N/A'
        ));
    }

    public function orderReady(Order $order): void
    {
        $this->send(sprintf(
            "✅ <b>Order Ready</b>\nOrder #%s\nTable %s",
            $order->order_number,
            $order->table?->table_number
        ));
    }

    public function paymentCompleted(Payment $payment): void
    {
        $this->send(sprintf(
            "💰 <b>Payment Completed</b>\nOrder #%s\nAmount: %s%.2f",
            $payment->order?->order_number,
            $this->currencySymbol(),
            $payment->amount
        ));
    }

    protected function currencySymbol(): string
    {
        return '$';
    }
}
