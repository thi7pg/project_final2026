<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\UpdateRestaurantSettingRequest;
use App\Http\Resources\RestaurantResource;
use App\Services\RestaurantSettingService;
use Illuminate\Http\JsonResponse;

class RestaurantSettingController extends Controller
{
    public function __construct(protected RestaurantSettingService $restaurantSettingService) {}

    public function show(): JsonResponse
    {
        return $this->success(new RestaurantResource($this->restaurantSettingService->get()));
    }

    public function update(UpdateRestaurantSettingRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        } else {
            unset($data['logo']);
        }

        $restaurant = $this->restaurantSettingService->update($data);

        return $this->success(new RestaurantResource($restaurant), 'Restaurant settings updated.');
    }
}
