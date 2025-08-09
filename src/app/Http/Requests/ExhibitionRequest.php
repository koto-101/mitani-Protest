<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'images.*'     => 'required|image|max:2048',
            'categories'   => 'required|array|min:1',
            'condition'    => 'required|string',
            'title'        => 'required|string|max:255',
            'brand'        => 'nullable|string|max:255',
            'description'  => 'required|string',
            'price'        => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'images.*.required' => '商品画像は必須です。',
            'images.*.image'    => '有効な画像ファイルをアップロードしてください。',
            'categories.required' => 'カテゴリーを1つ以上選択してください。',
            'condition.required'  => '商品の状態を選択してください。',
            'title.required'      => '商品名を入力してください。',
            'description.required' => '商品の説明を入力してください。',
            'price.required'      => '価格を入力してください。',
            // 必要に応じてメッセージ追加
        ];
    }
}
