<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_menu_via_valid_qr_token(): void
    {
        $table = DiningTable::factory()->create(['qr_token' => 'valid-token-123']);
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'available' => true]);
        Product::factory()->unavailable()->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/v1/menu/valid-token-123');

        $response->assertOk()
            ->assertJsonPath('data.table.table_number', $table->table_number)
            ->assertJsonCount(1, 'data.categories.0.products');
    }

    public function test_invalid_qr_token_returns_404(): void
    {
        $this->getJson('/api/v1/menu/does-not-exist')
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_inactive_table_returns_404(): void
    {
        DiningTable::factory()->create([
            'qr_token' => 'inactive-token',
            'status' => DiningTable::STATUS_INACTIVE,
        ]);

        $this->getJson('/api/v1/menu/inactive-token')->assertStatus(404);
    }
}
