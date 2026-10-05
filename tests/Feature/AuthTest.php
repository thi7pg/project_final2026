<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->admin()->create(['password' => 'password']);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.username', $user->username)
            ->assertJsonStructure(['data' => ['user', 'token']]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->admin()->create(['password' => 'password']);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => $user->username,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->admin()->inactive()->create(['password' => 'password']);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_profile_and_logout(): void
    {
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout')
            ->assertOk();
    }

    public function test_guest_cannot_access_protected_routes(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_email_only_login_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertUnprocessable()->assertJsonValidationErrors('username');
    }

    public function test_unknown_username_cannot_login(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'username' => 'unknown-user',
            'password' => 'password',
        ])->assertUnauthorized();
    }

    public function test_login_requires_password(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'username' => 'admin',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_admin_can_create_staff_with_a_unique_username(): void
    {
        $admin = User::factory()->admin()->create();
        $headers = ['Authorization' => 'Bearer '.$admin->createToken('test')->plainTextToken];
        $payload = [
            'name' => 'New Kitchen Staff',
            'username' => 'new-kitchen',
            'email' => 'new-kitchen@example.test',
            'password' => 'secure-password',
            'role' => 'kitchen',
        ];

        $this->withHeaders($headers)->postJson('/api/v1/admin/users', $payload)
            ->assertCreated()->assertJsonPath('data.username', 'new-kitchen');

        $payload['email'] = 'another@example.test';
        $this->withHeaders($headers)->postJson('/api/v1/admin/users', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('username');
    }
}
