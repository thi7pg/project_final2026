<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:confirmed,preparing,ready,completed,cancelled'],
            'cancelled_reason' => ['required_if:status,cancelled', 'nullable', 'string', 'max:255'],
        ];
    }
}
