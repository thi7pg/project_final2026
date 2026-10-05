<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CashierPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_view_dashboard_and_manage_categories_and_products(): void
    {
        $user = User::factory()->cashier()->create();
        $this->withHeader('Authorization', 'Bearer '.$user->createToken('test')->plainTextToken);

        $this->getJson('/api/v1/admin/dashboard')->assertOk();
        $this->getJson('/api/v1/admin/categories')->assertOk();
        $this->getJson('/api/v1/admin/products')->assertOk();

        $categoryId = $this->postJson('/api/v1/admin/categories', ['name' => 'Cashier Category'])
            ->assertCreated()->json('data.id');
        $this->getJson("/api/v1/admin/categories/{$categoryId}")->assertOk();
        $this->putJson("/api/v1/admin/categories/{$categoryId}", ['name' => 'Updated Category'])->assertOk();

        $productId = $this->postJson('/api/v1/admin/products', [
            'name' => 'Cashier Product', 'category_id' => $categoryId, 'price' => 10,
        ])->assertCreated()->json('data.id');
        $this->getJson("/api/v1/admin/products/{$productId}")->assertOk();
        $this->putJson("/api/v1/admin/products/{$productId}", ['price' => 12])->assertOk();
        $this->deleteJson("/api/v1/admin/products/{$productId}")->assertOk();
        $this->deleteJson("/api/v1/admin/categories/{$categoryId}")->assertOk();
    }

    public function test_cashier_remains_blocked_from_other_admin_features(): void
    {
        $user = User::factory()->cashier()->create();
        $this->withHeader('Authorization', 'Bearer '.$user->createToken('test')->plainTextToken);

        foreach (['users', 'settings', 'activity-logs', 'reports/revenue'] as $path) {
            $this->getJson('/api/v1/admin/'.$path)->assertForbidden();
        }
    }

    public function test_kitchen_and_guests_cannot_access_shared_management_features(): void
    {
        foreach (['dashboard', 'tables', 'categories', 'products'] as $path) {
            $this->getJson('/api/v1/admin/'.$path)->assertUnauthorized();
        }

        $user = User::factory()->kitchen()->create();
        $this->withHeader('Authorization', 'Bearer '.$user->createToken('test')->plainTextToken);
        foreach (['dashboard', 'tables', 'categories', 'products'] as $path) {
            $this->getJson('/api/v1/admin/'.$path)->assertForbidden();
        }
        $this->postJson('/api/v1/admin/categories', ['name' => 'Forbidden'])->assertForbidden();
        $this->postJson('/api/v1/admin/products', [])->assertForbidden();
    }

    public function test_cashier_can_manage_tables_and_regenerate_qr_codes(): void
    {
        Storage::fake('public');
        $user = User::factory()->cashier()->create();
        $this->withHeader('Authorization', 'Bearer '.$user->createToken('test')->plainTextToken);

        $this->getJson('/api/v1/admin/tables')->assertOk();
        $response = $this->postJson('/api/v1/admin/tables', [
            'table_number' => 'C01', 'capacity' => 4,
        ])->assertCreated();
        $id = $response->json('data.id');
        $token = $response->json('data.qr_token');

        $this->getJson("/api/v1/admin/tables/{$id}")->assertOk();
        $this->putJson("/api/v1/admin/tables/{$id}", ['capacity' => 6])
            ->assertOk()->assertJsonPath('data.capacity', 6);
        $regenerated = $this->postJson("/api/v1/admin/tables/{$id}/regenerate-qr")->assertOk();
        $this->assertNotSame($token, $regenerated->json('data.qr_token'));
        $this->deleteJson("/api/v1/admin/tables/{$id}")->assertOk();
        $this->assertDatabaseMissing('dining_tables', ['id' => $id]);
    }
}
