<?php

namespace Tests\Unit;

use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\Notifications\TelegramNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramNotifierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['telegram.bot_token' => 'test-token', 'telegram.chat_id' => '12345', 'telegram.enabled' => true]);
    }

    public function test_it_does_not_call_telegram_when_disabled(): void
    {
        config(['telegram.enabled' => false]);
        Http::fake();

        (new TelegramNotifier)->send('hello');

        Http::assertNothingSent();
    }

    public function test_new_order_sends_expected_telegram_payload(): void
    {
        Http::fake();

        $table = DiningTable::factory()->create(['table_number' => 'T05']);
        $order = Order::factory()->create(['table_id' => $table->id, 'order_number' => 'ORD-1001', 'total_amount' => 18.5]);
        $order->items()->create([
            'product_id' => Product::factory()->create()->id,
            'product_name' => 'Test Item',
            'unit_price' => 18.5,
            'quantity' => 3,
            'subtotal' => 18.5,
        ]);

        (new TelegramNotifier)->newOrder($order->fresh('items'));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.telegram.org/bottest-token/sendMessage')
                && $request['chat_id'] === '12345'
                && str_contains($request['text'], 'New Order')
                && str_contains($request['text'], 'ORD-1001')
                && str_contains($request['text'], 'T05');
        });
    }

    public function test_payment_completed_sends_telegram_message(): void
    {
        Http::fake();

        $order = Order::factory()->create(['order_number' => 'ORD-2002']);
        $payment = Payment::factory()->paid()->create(['order_id' => $order->id, 'amount' => 42]);

        (new TelegramNotifier)->paymentCompleted($payment->fresh('order'));

        Http::assertSent(fn ($request) => str_contains($request['text'], 'Payment Completed')
            && str_contains($request['text'], 'ORD-2002'));
    }
}
