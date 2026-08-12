<?php

namespace Tests\Feature;

use App\Models\DiningTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TableTest extends TestCase
{
    use RefreshDatabase;

    protected function adminHeaders(): array
    {
        $admin = User::factory()->admin()->create();

        return ['Authorization' => 'Bearer '.$admin->createToken('test')->plainTextToken];
    }

    public function test_admin_can_create_a_table_with_generated_qr_code(): void
    {
        Storage::fake('public');

        $response = $this->withHeaders($this->adminHeaders())->postJson('/api/v1/admin/tables', [
            'table_number' => 'T99',
            'capacity' => 4,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.table_number', 'T99')
            ->assertJsonPath('data.status', 'available');

        $this->assertNotNull($response->json('data.qr_token'));
        $this->assertNotNull($response->json('data.qr_code_image'));

        $table = DiningTable::query()->where('table_number', 'T99')->firstOrFail();
        Storage::disk('public')->assertExists($table->qr_code_image);
    }

    public function test_table_number_must_be_unique(): void
    {
        DiningTable::factory()->create(['table_number' => 'T01']);

        $response = $this->withHeaders($this->adminHeaders())->postJson('/api/v1/admin/tables', [
            'table_number' => 'T01',
            'capacity' => 4,
        ]);

        $response->assertStatus(422)->assertJsonPath('message', 'Validation Error');
    }

    public function test_admin_can_regenerate_qr_code(): void
    {
        Storage::fake('public');

        $table = DiningTable::factory()->create(['qr_code_image' => 'qrcodes/old.svg']);
        Storage::disk('public')->put('qrcodes/old.svg', 'dummy');

        $response = $this->withHeaders($this->adminHeaders())
            ->postJson("/api/v1/admin/tables/{$table->id}/regenerate-qr");

        $response->assertOk();
        $this->assertNotEquals($table->qr_token, $response->json('data.qr_token'));
    }

    public function test_non_admin_cannot_manage_tables(): void
    {
        $kitchen = User::factory()->kitchen()->create();
        $token = $kitchen->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/admin/tables')
            ->assertStatus(403);
    }
}
