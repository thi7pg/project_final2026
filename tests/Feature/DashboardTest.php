<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_returns_summary(): void
    {
        $order = Order::factory()->status(Order::STATUS_COMPLETED)->create(['total_amount' => 30]);
        Payment::factory()->paid()->create(['order_id' => $order->id, 'amount' => 30]);
        Order::factory()->status(Order::STATUS_PENDING)->create();

        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/admin/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.today_orders', 2)
            ->assertJsonPath('data.today_revenue', 30)
            ->assertJsonPath('data.pending_orders', 1)
            ->assertJsonPath('data.completed_orders', 1)
            ->assertJsonStructure(['data' => ['popular_menu', 'recent_orders']]);
    }
}
