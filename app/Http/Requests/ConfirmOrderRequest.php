<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmOrderRequest extends FormRequest
{
    private Order $order;

    public function authorize(): bool
    {
        $this->order = Order::query()->where('tracking_token', $this->route('token'))->firstOrFail();
        app()->setLocale($this->order->locale);

        return filled($this->session()->get('checkout_submission_key'))
            && hash_equals((string) $this->session()->get('checkout_submission_key'), (string) $this->order->submission_key);
    }

    public function order(): Order
    {
        return $this->order;
    }

    public function rules(): array
    {
        return ['quote_hash' => ['required', 'string', 'size:64']];
    }
}
