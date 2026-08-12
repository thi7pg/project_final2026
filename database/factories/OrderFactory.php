<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10, 100);

        return [
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'table_id' => DiningTable::factory(),
            'customer_id' => Customer::factory(),
            'status' => Order::STATUS_PENDING,
            'subtotal' => $subtotal,
            'tax_amount' => 0,
            'service_charge_amount' => 0,
            'total_amount' => $subtotal,
            'notes' => null,
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }
}
