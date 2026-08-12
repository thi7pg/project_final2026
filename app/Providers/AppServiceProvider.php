<?php

namespace App\Providers;

use App\Events\OrderCancelled;
use App\Events\OrderPlaced;
use App\Events\OrderReady;
use App\Events\PaymentCompleted;
use App\Listeners\SendTelegramNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(OrderPlaced::class, [SendTelegramNotification::class, 'handleOrderPlaced']);
        Event::listen(OrderCancelled::class, [SendTelegramNotification::class, 'handleOrderCancelled']);
        Event::listen(OrderReady::class, [SendTelegramNotification::class, 'handleOrderReady']);
        Event::listen(PaymentCompleted::class, [SendTelegramNotification::class, 'handlePaymentCompleted']);
    }
}
