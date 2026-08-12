<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function index(): JsonResponse
    {
        return $this->success(UserResource::collection($this->userService->paginate()));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return $this->success(new UserResource($user), 'Staff account created successfully.', 201);
    }

    public function show(User $user): JsonResponse
    {
        return $this->success(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->update($user, $request->validated());

        return $this->success(new UserResource($user), 'Staff account updated successfully.');
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);

        return $this->success(null, 'Staff account deleted successfully.');
    }
}
