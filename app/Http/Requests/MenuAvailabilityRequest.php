<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        app()->setLocale(in_array($this->input('locale'), ['uk', 'en'], true) ? $this->input('locale') : 'pl');
    }

    public function rules(): array
    {
        return [
            'locale' => ['nullable', 'in:pl,uk,en'],
            'day' => ['nullable', 'required_with:time', 'date_format:Y-m-d'],
            'time' => ['nullable', 'required_with:day', 'date_format:H:i'],
            'type' => ['nullable', 'in:PICK_UP,DELIVERY'],
        ];
    }
}
