<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            'Appetizers' => [
                ['name' => 'Spring Rolls', 'price' => 4.50, 'prep' => 10],
                ['name' => 'Chicken Satay', 'price' => 5.50, 'prep' => 15],
                ['name' => 'Fresh Salad', 'price' => 3.75, 'prep' => 8],
            ],
            'Main Course' => [
                ['name' => 'Grilled Beef Steak', 'price' => 12.00, 'prep' => 25],
                ['name' => 'Roasted Chicken', 'price' => 9.50, 'prep' => 20],
                ['name' => 'Grilled Salmon', 'price' => 14.00, 'prep' => 22],
            ],
            'Noodles & Rice' => [
                ['name' => 'Fried Rice with Chicken', 'price' => 6.50, 'prep' => 15],
                ['name' => 'Khmer Noodle Soup', 'price' => 5.00, 'prep' => 12],
                ['name' => 'Stir-fried Noodles', 'price' => 6.00, 'prep' => 15],
            ],
            'Beverages' => [
                ['name' => 'Iced Lemon Tea', 'price' => 2.00, 'prep' => 5],
                ['name' => 'Fresh Orange Juice', 'price' => 2.50, 'prep' => 5],
                ['name' => 'Coconut Shake', 'price' => 3.00, 'prep' => 7],
            ],
            'Desserts' => [
                ['name' => 'Mango Sticky Rice', 'price' => 4.00, 'prep' => 10],
                ['name' => 'Chocolate Lava Cake', 'price' => 4.50, 'prep' => 12],
            ],
        ];

        foreach ($products as $categoryName => $items) {
            $category = Category::query()->where('slug', Str::slug($categoryName))->first();

            if (! $category) {
                continue;
            }

            foreach ($items as $item) {
                Product::query()->firstOrCreate(
                    ['slug' => Str::slug($item['name'])],
                    [
                        'category_id' => $category->id,
                        'name' => $item['name'],
                        'description' => "Delicious {$item['name']}, freshly prepared.",
                        'price' => $item['price'],
                        'available' => true,
                        'preparation_time' => $item['prep'],
                    ]
                );
            }
        }
    }
}
