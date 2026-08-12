<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $users,
        protected ActivityLogService $activityLog,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->users->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->users->all();
    }

    public function find(int $id): ?User
    {
        return $this->users->find($id);
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        $user = $this->users->create($data);

        $this->activityLog->log('user.created', "Staff account {$user->name} ({$user->role}) created", $user);

        return $user;
    }

    public function update(User $user, array $data): User
    {
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user = $this->users->update($user, $data);

        $this->activityLog->log('user.updated', "Staff account {$user->name} updated", $user, properties: array_diff_key($data, ['password' => '']));

        return $user;
    }

    public function delete(User $user): void
    {
        $this->activityLog->log('user.deleted', "Staff account {$user->name} deleted", $user);

        $this->users->delete($user);
    }
}
