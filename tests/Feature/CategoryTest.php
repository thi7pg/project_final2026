<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function adminHeaders(): array
    {
        $admin = User::factory()->admin()->create();

        return ['Authorization' => 'Bearer '.$admin->createToken('test')->plainTextToken];
    }

    public function test_admin_can_create_category_with_auto_generated_slug(): void
    {
        $response = $this->withHeaders($this->adminHeaders())->postJson('/api/v1/admin/categories', [
            'name' => 'Hot Beverages',
        ]);

        $response->assertCreated()->assertJsonPath('data.slug', 'hot-beverages');
    }

    public function test_admin_can_update_and_delete_category(): void
    {
        $category = Category::factory()->create();
        $headers = $this->adminHeaders();

        $this->withHeaders($headers)
            ->putJson("/api/v1/admin/categories/{$category->id}", ['name' => 'Updated Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');

        $this->withHeaders($headers)
            ->deleteJson("/api/v1/admin/categories/{$category->id}")
            ->assertOk();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
