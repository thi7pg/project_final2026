<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'available' => ['sometimes', 'boolean'],
            'preparation_time' => ['sometimes', 'integer', 'min:1', 'max:180'],
        ];
    }
}
