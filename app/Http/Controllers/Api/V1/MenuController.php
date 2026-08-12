<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function __construct(protected MenuService $menuService) {}

    public function show(string $qrToken): JsonResponse
    {
        return $this->success(new MenuResource($this->menuService->getMenu($qrToken)));
    }
}
