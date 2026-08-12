<?php

namespace App\Http\Controllers\Api\V1\Kitchen;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\KitchenService;
use Illuminate\Http\JsonResponse;

class KitchenController extends Controller
{
    public function __construct(protected KitchenService $kitchenService) {}

    public function dashboard(): JsonResponse
    {
        $dashboard = $this->kitchenService->dashboard();

        return $this->success([
            'pending' => OrderResource::collection($dashboard['pending']),
            'confirmed' => OrderResource::collection($dashboard['confirmed']),
            'preparing' => OrderResource::collection($dashboard['preparing']),
            'ready' => OrderResource::collection($dashboard['ready']),
            'completed_today' => OrderResource::collection($dashboard['completed_today']),
        ]);
    }
}
