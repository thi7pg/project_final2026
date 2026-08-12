<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KitchenTest extends TestCase
{
    use RefreshDatabase;

    public function test_kitchen_dashboard_groups_orders_by_status(): void
    {
        Order::factory()->status(Order::STATUS_PENDING)->count(2)->create();
        Order::factory()->status(Order::STATUS_PREPARING)->count(1)->create();
        Order::factory()->status(Order::STATUS_READY)->count(3)->create();

        $user = User::factory()->kitchen()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/kitchen/dashboard');

        $response->assertOk()
            ->assertJsonCount(2, 'data.pending')
            ->assertJsonCount(1, 'data.preparing')
            ->assertJsonCount(3, 'data.ready');
    }

    public function test_cashier_cannot_access_kitchen_dashboard(): void
    {
        $user = User::factory()->cashier()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/kitchen/dashboard')
            ->assertStatus(403);
    }
}
