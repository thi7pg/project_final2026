<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogService
{
    public function log(string $action, ?string $description = null, ?Model $subject = null, ?User $causer = null, array $properties = []): ActivityLog
    {
        return ActivityLog::query()->create([
            'user_id' => $causer?->id ?? auth('sanctum')->id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()?->ip(),
        ]);
    }

    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return ActivityLog::query()->with('user')->latest()->paginate($perPage);
    }
}
