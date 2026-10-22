<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Rules\CartPayload;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SubmitCheckoutRequest extends FormRequest
{
    private ?Order $existingOrder = null;

    protected function prepareForValidation(): void
    {
        app()->setLocale(in_array($this->route('locale'), ['uk', 'en'], true) ? $this->route('locale') : 'pl');
    }

    public function authorize(): bool
    {
        if (config('goorder.enabled')) {
            $data = Validator::make($this->only('submission_key'), ['submission_key' => ['required', 'uuid']])->validate();
            abort_unless(hash_equals((string) $this->session()->get('checkout_submission_key'), $data['submission_key']), 419);
            $this->existingOrder = Order::query()->where('submission_key', $data['submission_key'])->first();
        }

        return true;
    }

    public function existingOrder(): ?Order
    {
        return $this->existingOrder;
    }

    public function rules(): array
    {
        // A replay resumes the original checkout without requiring its form again.
        if ($this->existingOrder) {
            return ['submission_key' => ['required', 'uuid']];
        }

        return [
            'submission_key' => [Rule::excludeIf(! config('goorder.enabled')), 'required', 'uuid'],
            'cart_json' => ['bail', 'required', 'string', 'max:65536', new CartPayload],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'wants_invoice' => ['nullable', 'boolean'],
            'nip' => ['nullable', Rule::requiredIf($this->boolean('wants_invoice')), 'string', 'max:32'],
            'delivery_type' => ['required', 'in:pickup,delivery'],
            'fulfillment_type' => ['required', 'in:asap,scheduled'],
            'scheduled_day' => ['nullable', 'required_if:fulfillment_type,scheduled', 'date_format:Y-m-d'],
            'scheduled_time' => ['nullable', 'required_if:fulfillment_type,scheduled', 'date_format:H:i'],
            'payment_type' => ['required', 'in:card,cash'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'required_if:delivery_type,delivery', 'string', Rule::in(config('delivery.cities'))],
            'street' => ['nullable', 'required_if:delivery_type,delivery', 'string', 'max:255'],
            'building_number' => ['nullable', 'required_if:delivery_type,delivery', 'string', 'max:50'],
            'apartment_number' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('checkout.name'), 'email' => __('checkout.email'), 'phone' => __('checkout.phone'),
            'scheduled_day' => __('checkout.day'), 'scheduled_time' => __('checkout.time'),
            'city' => __('checkout.city'), 'street' => __('checkout.street'),
            'building_number' => __('checkout.building'), 'apartment_number' => __('checkout.apartment'),
            'delivery_type' => __('checkout.deliveryType'), 'fulfillment_type' => __('checkout.time'),
            'payment_type' => __('checkout.payment'), 'nip' => __('checkout.nip'),
            'comment' => __('checkout.comment'), 'cart_json' => __('checkout.items'),
        ];
    }

    public function cart(): array
    {
        return json_decode($this->validated('cart_json'), true, flags: JSON_THROW_ON_ERROR);
    }

    protected function failedValidation(ValidatorContract $validator): void
    {
        if (! $this->expectsJson() && $validator->errors()->has('cart_json')) {
            $this->session()->flash('checkout_error', __('checkout.invalidCart'));
        }

        parent::failedValidation($validator);
    }
}
