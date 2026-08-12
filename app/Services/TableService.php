<?php

namespace App\Services;

use App\Contracts\Repositories\TableRepositoryInterface;
use App\Models\DiningTable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class TableService
{
    public function __construct(
        protected TableRepositoryInterface $tables,
        protected QrCodeService $qrCodeService,
        protected ActivityLogService $activityLog,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->tables->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->tables->all();
    }

    public function find(int $id): ?DiningTable
    {
        return $this->tables->find($id);
    }

    public function create(array $data): DiningTable
    {
        $data['qr_token'] = $this->qrCodeService->generateToken();

        $table = $this->tables->create($data);

        $table = $this->tables->update($table, [
            'qr_code_image' => $this->qrCodeService->generateImage($table),
        ]);

        $this->activityLog->log('table.created', "Table {$table->table_number} created", $table);

        return $table;
    }

    public function update(DiningTable $table, array $data): DiningTable
    {
        $table = $this->tables->update($table, $data);

        $this->activityLog->log('table.updated', "Table {$table->table_number} updated", $table, properties: $data);

        return $table;
    }

    public function delete(DiningTable $table): void
    {
        if ($table->qr_code_image) {
            Storage::disk('public')->delete($table->qr_code_image);
        }

        $this->activityLog->log('table.deleted', "Table {$table->table_number} deleted", $table);

        $this->tables->delete($table);
    }

    public function regenerateQrCode(DiningTable $table): DiningTable
    {
        if ($table->qr_code_image) {
            Storage::disk('public')->delete($table->qr_code_image);
        }

        $table = $this->tables->update($table, [
            'qr_token' => $this->qrCodeService->generateToken(),
        ]);

        $table = $this->tables->update($table, [
            'qr_code_image' => $this->qrCodeService->generateImage($table),
        ]);

        $this->activityLog->log('table.qr_regenerated', "QR code regenerated for table {$table->table_number}", $table);

        return $table;
    }
}
