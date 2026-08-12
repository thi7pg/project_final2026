<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\TableRepositoryInterface;
use App\Models\DiningTable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TableRepository implements TableRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return DiningTable::query()->latest()->paginate($perPage);
    }

    public function all(): Collection
    {
        return DiningTable::query()->orderBy('table_number')->get();
    }

    public function find(int $id): ?DiningTable
    {
        return DiningTable::query()->find($id);
    }

    public function findByQrToken(string $qrToken): ?DiningTable
    {
        return DiningTable::query()->where('qr_token', $qrToken)->first();
    }

    public function create(array $data): DiningTable
    {
        return DiningTable::query()->create($data);
    }

    public function update(DiningTable $table, array $data): DiningTable
    {
        $table->update($data);

        return $table->refresh();
    }

    public function delete(DiningTable $table): void
    {
        $table->delete();
    }
}
