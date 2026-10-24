<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebsiteEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'uuid'],
            'name' => ['required', 'in:page_view,menu_view,product_view,add_to_cart,begin_checkout'],
            'path' => ['required', 'string', 'max:255', 'regex:~^/(?:[a-z0-9]+(?:[-/][a-z0-9]+)*)?/?$~D'],
            'menu_item_id' => ['nullable', 'integer', 'exists:menu_items,id', 'required_if:name,product_view,add_to_cart'],
            'referrer_host' => ['nullable', 'string', 'max:253', 'regex:/^[a-z0-9.-]+$/iD'],
            'utm_source' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9_-]+$/iD'],
            'utm_campaign' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9_-]+$/iD'],
        ];
    }
}
