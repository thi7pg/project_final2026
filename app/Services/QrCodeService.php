<?php

namespace App\Services;

use App\Models\DiningTable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    public function generateToken(): string
    {
        do {
            $token = Str::random(40);
        } while (DiningTable::query()->where('qr_token', $token)->exists());

        return $token;
    }

    public function menuUrl(string $qrToken): string
    {
        return rtrim(config('app.frontend_menu_url'), '/').'/'.$qrToken;
    }

    public function generateImage(DiningTable $table): string
    {
        $svg = QrCode::format('svg')->size(300)->generate($this->menuUrl($table->qr_token));

        $path = 'qrcodes/'.Str::slug($table->table_number).'.svg';

        Storage::disk('public')->put($path, $svg);

        return $path;
    }
}
