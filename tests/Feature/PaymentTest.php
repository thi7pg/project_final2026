<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function cashierHeaders(): array
    {
        $user = User::factory()->cashier()->create();

        return ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
    }

    public function test_cashier_can_mark_an_order_as_paid(): void
    {
        $order = Order::factory()->status(Order::STATUS_COMPLETED)->create(['total_amount' => 25]);
        Payment::factory()->create(['order_id' => $order->id, 'amount' => 25, 'status' => Payment::STATUS_PENDING]);

        $response = $this->withHeaders($this->cashierHeaders())
            ->patchJson("/api/v1/cashier/payments/{$order->id}/pay");

        $response->assertOk()->assertJsonPath('data.status', 'paid');

        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'paid']);
    }

    public function test_cannot_pay_an_already_paid_order(): void
    {
        $order = Order::factory()->create();
        Payment::factory()->paid()->create(['order_id' => $order->id]);

        $this->withHeaders($this->cashierHeaders())
            ->patchJson("/api/v1/cashier/payments/{$order->id}/pay")
            ->assertStatus(422);
    }

    public function test_kitchen_cannot_access_payments(): void
    {
        $user = User::factory()->kitchen()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/cashier/payments')
            ->assertStatus(403);
    }
}
