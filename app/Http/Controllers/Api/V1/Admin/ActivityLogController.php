<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    public function __construct(protected ActivityLogService $activityLogService) {}

    public function index(): JsonResponse
    {
        return $this->success(ActivityLogResource::collection($this->activityLogService->paginate()));
    }
}
