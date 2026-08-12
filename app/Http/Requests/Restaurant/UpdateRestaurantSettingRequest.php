<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRestaurantSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'logo' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'currency' => ['sometimes', 'required', 'string', 'size:3'],
            'tax_percentage' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'service_charge_percentage' => ['sometimes', 'required', 'numeric', 'min:0', 'max:100'],
            'opening_time' => ['sometimes', 'nullable', 'date_format:H:i'],
            'closing_time' => ['sometimes', 'nullable', 'date_format:H:i'],
        ];
    }
}
