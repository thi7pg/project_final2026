<?php

namespace Tests\Feature;

use App\Models\DiningTable;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_computes_server_authoritative_totals(): void
    {
        Restaurant::factory()->create(['tax_percentage' => 10, 'service_charge_percentage' => 5]);
        $table = DiningTable::factory()->create(['qr_token' => 'cart-token']);
        $product = Product::factory()->create(['price' => 10, 'available' => true]);

        $response = $this->postJson('/api/v1/cart/validate', [
            'qr_token' => 'cart-token',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.subtotal', 20)
            ->assertJsonPath('data.tax_amount', 2)
            ->assertJsonPath('data.service_charge_amount', 1)
            ->assertJsonPath('data.total_amount', 23);
    }

    public function test_cart_rejects_unavailable_product(): void
    {
        DiningTable::factory()->create(['qr_token' => 'cart-token-2']);
        $product = Product::factory()->unavailable()->create();

        $response = $this->postJson('/api/v1/cart/validate', [
            'qr_token' => 'cart-token-2',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cart_rejects_invalid_qr_token(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/v1/cart/validate', [
            'qr_token' => 'no-such-token',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(404);
    }
}
