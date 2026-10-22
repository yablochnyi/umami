<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StreetAutocompleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['q' => ['nullable', 'string', 'max:255'], 'city' => ['nullable', 'string', 'max:255']];
    }
}
