<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@restaurant.test'],
            [
                'name' => 'Restaurant Admin',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'kitchen@restaurant.test'],
            [
                'name' => 'Kitchen Staff',
                'password' => 'password',
                'role' => User::ROLE_KITCHEN,
                'is_active' => true,
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'cashier@restaurant.test'],
            [
                'name' => 'Cashier Staff',
                'password' => 'password',
                'role' => User::ROLE_CASHIER,
                'is_active' => true,
            ]
        );
    }
}
