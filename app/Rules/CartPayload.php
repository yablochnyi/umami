<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class CartPayload implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cart = json_decode($value, true);
        if (! is_array($cart) || ! array_is_list($cart) || count($cart) > 100) {
            $fail('checkout.invalidCart')->translate();

            return;
        }

        $validator = Validator::make(['items' => $cart], [
            'items.*' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'min:1', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.modifiers' => ['sometimes', 'array'],
        ]);

        if ($validator->fails()) {
            $fail('checkout.invalidCart')->translate();
        }
    }
}
