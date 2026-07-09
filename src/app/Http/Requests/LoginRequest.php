<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * 認可
    */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
    */
    public function rules(): array
    {
        return [
            //Blade側はtype="text"、Laravel側でメール形式を確認
            'email' => ['required', 'email'],

            //パスワード必須
            'password' => ['required'],
        ];
    }

    /**
     * エラーメッセージ
    */
    public function messages(): array
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスは「ユーザー名@ドメイン」形式で入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }
}