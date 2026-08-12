<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Table\StoreTableRequest;
use App\Http\Requests\Table\UpdateTableRequest;
use App\Http\Resources\TableResource;
use App\Models\DiningTable;
use App\Services\TableService;
use Illuminate\Http\JsonResponse;

class TableController extends Controller
{
    public function __construct(protected TableService $tableService) {}

    public function index(): JsonResponse
    {
        return $this->success(TableResource::collection($this->tableService->paginate()));
    }

    public function store(StoreTableRequest $request): JsonResponse
    {
        $table = $this->tableService->create($request->validated());

        return $this->success(new TableResource($table), 'Table created successfully.', 201);
    }

    public function show(DiningTable $table): JsonResponse
    {
        return $this->success(new TableResource($table));
    }

    public function update(UpdateTableRequest $request, DiningTable $table): JsonResponse
    {
        $table = $this->tableService->update($table, $request->validated());

        return $this->success(new TableResource($table), 'Table updated successfully.');
    }

    public function destroy(DiningTable $table): JsonResponse
    {
        $this->tableService->delete($table);

        return $this->success(null, 'Table deleted successfully.');
    }

    public function regenerateQr(DiningTable $table): JsonResponse
    {
        $table = $this->tableService->regenerateQrCode($table);

        return $this->success(new TableResource($table), 'QR code regenerated successfully.');
    }
}
