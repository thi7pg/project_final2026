<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsernameMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_users_receive_unique_usernames_without_password_changes(): void
    {
        $migration = require database_path('migrations/2026_10_05_000001_add_username_to_users_table.php');
        $migration->down();
        $password = Hash::make('existing-password');

        foreach (['admin@one.test', 'admin@two.test', 'ADMIN@three.test'] as $email) {
            DB::table('users')->insert([
                'name' => 'Existing Staff',
                'email' => $email,
                'password' => $password,
            ]);
        }

        $migration->up();

        $this->assertSame(['admin', 'admin-1', 'admin-2'], DB::table('users')->orderBy('id')->pluck('username')->all());
        $this->assertSame([$password], DB::table('users')->distinct()->pluck('password')->all());
    }
}
