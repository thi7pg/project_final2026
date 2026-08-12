<?php

namespace Database\Seeders;

use App\Models\DiningTable;
use App\Services\TableService;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $tableService = app(TableService::class);

        foreach (range(1, 8) as $number) {
            $tableNumber = 'T'.str_pad((string) $number, 2, '0', STR_PAD_LEFT);

            if (DiningTable::query()->where('table_number', $tableNumber)->exists()) {
                continue;
            }

            $tableService->create([
                'table_number' => $tableNumber,
                'capacity' => $number <= 4 ? 2 : 6,
            ]);
        }
    }
}
