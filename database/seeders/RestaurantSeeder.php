<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        Restaurant::query()->firstOrCreate([], [
            'name' => 'The QR Bistro',
            'address' => '123 Riverside Street, Phnom Penh',
            'phone' => '+855 12 345 678',
            'email' => 'contact@qrbistro.test',
            'currency' => 'USD',
            'tax_percentage' => 10,
            'service_charge_percentage' => 5,
            'opening_time' => '08:00',
            'closing_time' => '22:00',
        ]);
    }
}
