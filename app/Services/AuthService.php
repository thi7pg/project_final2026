<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $users,
        protected ActivityLogService $activityLog,
    ) {}

    public function login(string $username, string $password): array
    {
        $user = $this->users->findActiveByUsername($username);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new ApiException('Invalid credentials.', 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $this->activityLog->log('auth.login', "{$user->name} logged in", $user, $user);

        return ['user' => $user, 'token' => $token];
    }

    public function logout(User $user): void
    {
        $this->activityLog->log('auth.logout', "{$user->name} logged out", $user, $user);

        $user->currentAccessToken()->delete();
    }
}
