<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'logo',
        'currency',
        'tax_percentage',
        'service_charge_percentage',
        'opening_time',
        'closing_time',
    ];

    protected function casts(): array
    {
        return [
            'tax_percentage' => 'decimal:2',
            'service_charge_percentage' => 'decimal:2',
        ];
    }
}
