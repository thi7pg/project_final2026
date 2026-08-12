<?php

namespace App\Http\Requests\Table;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tableId = $this->route('table')?->id;

        return [
            'table_number' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('dining_tables', 'table_number')->ignore($tableId)],
            'capacity' => ['sometimes', 'required', 'integer', 'min:1', 'max:50'],
            'status' => ['sometimes', 'in:available,occupied,reserved,inactive'],
        ];
    }
}
