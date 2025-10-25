<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'max_chars:400'],
            'image'   => ['nullable', 'file', 'mimes:jpeg,png'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => '本文を入力してください。',
            'message.max'      => '本文は400文字以内で入力してください。',
            'image.mimes'      => '「.png」または「.jpeg」形式でアップロードしてください。',
        ];
    }

    public function attributes(): array
    {
        return [
            'message' => '本文',
            'image' => '画像',
        ];
    }
}
