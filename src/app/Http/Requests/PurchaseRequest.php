<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'in:card,convenience'], 
            'shipping_address_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value === 'user') {
                        return; // OK（ユーザー登録住所を使う）
                    }

                    if (!\App\Models\ShippingAddress::where('id', $value)->exists()) {
                        $fail('shipping_address_id.exists');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => '支払い方法を選択してください。',
            'payment_method.in' => '有効な支払い方法を選択してください。',
            'shipping_address_id.required' => '配送先を選択してください。',
            'shipping_address_id.exists' => '選択された配送先が無効です。',
        ];
    }
}