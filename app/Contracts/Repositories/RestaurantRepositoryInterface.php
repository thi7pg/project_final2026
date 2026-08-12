<?php

namespace App\Contracts\Repositories;

use App\Models\Restaurant;

interface RestaurantRepositoryInterface
{
    public function getSettings(): Restaurant;

    public function updateSettings(array $data): Restaurant;
}
