<?php

namespace Tests\Feature;

use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function actingHeaders(string $role): array
    {
        $user = User::factory()->{$role}()->create();

        return ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
    }

    public function test_customer_can_place_an_order_and_table_becomes_occupied(): void
    {
        Restaurant::factory()->create(['tax_percentage' => 0, 'service_charge_percentage' => 0]);
        $table = DiningTable::factory()->create(['qr_token' => 'order-token']);
        $product = Product::factory()->create(['price' => 5, 'available' => true]);

        $response = $this->postJson('/api/v1/orders', [
            'qr_token' => 'order-token',
            'customer_name' => 'Jane Doe',
            'customer_phone' => '099887766',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_amount', 15);

        $this->assertEquals(DiningTable::STATUS_OCCUPIED, $table->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $response->json('data.id'), 'status' => 'pending']);
    }

    public function test_order_cannot_be_placed_with_empty_items(): void
    {
        DiningTable::factory()->create(['qr_token' => 'empty-token']);

        $this->postJson('/api/v1/orders', [
            'qr_token' => 'empty-token',
            'customer_name' => 'Jane Doe',
            'items' => [],
        ])->assertStatus(422);
    }

    public function test_kitchen_can_progress_order_through_valid_transitions(): void
    {
        Event::fake();
        $order = Order::factory()->status(Order::STATUS_PENDING)->create();
        $headers = $this->actingHeaders('kitchen');

        $this->withHeaders($headers)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        $this->withHeaders($headers)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'preparing'])
            ->assertOk();

        $this->withHeaders($headers)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'ready'])
            ->assertOk();
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $order = Order::factory()->status(Order::STATUS_PENDING)->create();
        $headers = $this->actingHeaders('kitchen');

        $this->withHeaders($headers)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'completed'])
            ->assertStatus(422);
    }

    public function test_cancelling_an_order_requires_a_reason(): void
    {
        $order = Order::factory()->status(Order::STATUS_PENDING)->create();
        $headers = $this->actingHeaders('kitchen');

        $this->withHeaders($headers)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertStatus(422);

        $this->withHeaders($headers)
            ->patchJson("/api/v1/orders/{$order->id}/status", [
                'status' => 'cancelled',
                'cancelled_reason' => 'Customer changed their mind',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_table_becomes_available_again_once_order_completes(): void
    {
        $table = DiningTable::factory()->create(['status' => DiningTable::STATUS_OCCUPIED]);
        $order = Order::factory()->status(Order::STATUS_READY)->create(['table_id' => $table->id]);
        $headers = $this->actingHeaders('kitchen');

        $this->withHeaders($headers)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'completed'])
            ->assertOk();

        $this->assertEquals(DiningTable::STATUS_AVAILABLE, $table->fresh()->status);
    }

    public function test_guest_can_track_order_by_order_number_without_auth(): void
    {
        $order = Order::factory()->create(['order_number' => 'ORD-TRACK-001']);

        $this->getJson('/api/v1/orders/track/ORD-TRACK-001')
            ->assertOk()
            ->assertJsonPath('data.order_number', 'ORD-TRACK-001')
            ->assertJsonMissing(['customer']);
    }

    public function test_cashier_cannot_update_order_status(): void
    {
        $order = Order::factory()->status(Order::STATUS_PENDING)->create();
        $headers = $this->actingHeaders('cashier');

        $this->withHeaders($headers)
            ->patchJson("/api/v1/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertStatus(403);
    }
}
