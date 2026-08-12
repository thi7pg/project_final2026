<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function firstOrCreate(string $name, ?string $phone): Customer
    {
        if (! $phone) {
            return Customer::query()->create(['name' => $name, 'phone' => null]);
        }

        $customer = Customer::query()->where('phone', $phone)->first();

        if ($customer) {
            if ($customer->name !== $name) {
                $customer->update(['name' => $name]);
            }

            return $customer;
        }

        return Customer::query()->create(['name' => $name, 'phone' => $phone]);
    }
}
