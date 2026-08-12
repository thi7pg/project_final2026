<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Restaurant>
 */
class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' Restaurant',
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'logo' => null,
            'currency' => 'USD',
            'tax_percentage' => 10,
            'service_charge_percentage' => 5,
            'opening_time' => '08:00',
            'closing_time' => '22:00',
        ];
    }
}
