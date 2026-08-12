<?php

namespace App\Contracts\Repositories;

use App\Models\DiningTable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TableRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function all(): Collection;

    public function find(int $id): ?DiningTable;

    public function findByQrToken(string $qrToken): ?DiningTable;

    public function create(array $data): DiningTable;

    public function update(DiningTable $table, array $data): DiningTable;

    public function delete(DiningTable $table): void;
}
