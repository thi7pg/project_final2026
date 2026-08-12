<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function adminHeaders(): array
    {
        $admin = User::factory()->admin()->create();

        return ['Authorization' => 'Bearer '.$admin->createToken('test')->plainTextToken];
    }

    public function test_admin_can_create_product(): void
    {
        $category = Category::factory()->create();

        $response = $this->withHeaders($this->adminHeaders())->postJson('/api/v1/admin/products', [
            'category_id' => $category->id,
            'name' => 'Beef Noodle Soup',
            'price' => 6.5,
            'preparation_time' => 15,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Beef Noodle Soup')
            ->assertJsonPath('data.price', 6.5);
    }

    public function test_product_requires_valid_category(): void
    {
        $response = $this->withHeaders($this->adminHeaders())->postJson('/api/v1/admin/products', [
            'category_id' => 999,
            'name' => 'Ghost Item',
            'price' => 5,
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_filter_products_by_availability(): void
    {
        Product::factory()->create(['available' => true]);
        Product::factory()->unavailable()->create();

        $response = $this->withHeaders($this->adminHeaders())
            ->getJson('/api/v1/admin/products?available=1');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }
}
