<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'description' => fake()->sentence(12),
            'price' => fake()->randomFloat(2, 2, 40),
            'image' => null,
            'available' => true,
            'preparation_time' => fake()->numberBetween(5, 30),
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => ['available' => false]);
    }
}
