<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\RestaurantRepositoryInterface;
use App\Models\Restaurant;

class RestaurantRepository implements RestaurantRepositoryInterface
{
    public function getSettings(): Restaurant
    {
        return Restaurant::query()->firstOrCreate([], [
            'name' => 'My Restaurant',
            'currency' => 'USD',
            'tax_percentage' => 0,
            'service_charge_percentage' => 0,
        ]);
    }

    public function updateSettings(array $data): Restaurant
    {
        $restaurant = $this->getSettings();
        $restaurant->update($data);

        return $restaurant->refresh();
    }
}
