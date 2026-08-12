<?php

namespace App\Services;

use App\Contracts\Repositories\RestaurantRepositoryInterface;
use App\Models\Restaurant;

class RestaurantSettingService
{
    public function __construct(
        protected RestaurantRepositoryInterface $restaurants,
        protected ActivityLogService $activityLog,
    ) {}

    public function get(): Restaurant
    {
        return $this->restaurants->getSettings();
    }

    public function update(array $data): Restaurant
    {
        $restaurant = $this->restaurants->updateSettings($data);

        $this->activityLog->log('restaurant.settings_updated', 'Restaurant settings updated', $restaurant);

        return $restaurant;
    }
}
