<?php

namespace App\Http\Requests\Table;

use Illuminate\Foundation\Http\FormRequest;

class StoreTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_number' => ['required', 'string', 'max:20', 'unique:dining_tables,table_number'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['sometimes', 'in:available,occupied,reserved,inactive'],
        ];
    }
}
