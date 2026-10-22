<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city' => ['nullable', 'string', Rule::in(config('delivery.cities'))],
            'street' => ['nullable', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:50'],
        ];
    }
}
