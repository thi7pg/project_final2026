<?php

namespace Database\Factories;

use App\Models\DiningTable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DiningTable>
 */
class DiningTableFactory extends Factory
{
    protected $model = DiningTable::class;

    public function definition(): array
    {
        return [
            'table_number' => 'T'.str_pad((string) fake()->unique()->numberBetween(1, 999), 2, '0', STR_PAD_LEFT),
            'capacity' => fake()->numberBetween(2, 8),
            'qr_token' => Str::random(40),
            'qr_code_image' => null,
            'status' => DiningTable::STATUS_AVAILABLE,
        ];
    }
}
