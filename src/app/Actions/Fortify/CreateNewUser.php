<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * 会員登録処理
    */
    public function create(array $input): User
    {
        Validator::make($input, [
            //名前は必須
            'name' => ['required', 'string', 'max:255'],

            //emailはBlade側でtype="text"にし、Laravel側でemail形式を検証
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],

            //確認用パスワードと一致させる
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            //日本語エラーメッセージ
            'name.required' => '名前を入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスは「ユーザー名@ドメイン」形式で入力してください',
            'email.unique' => 'このメールアドレスは既に登録されています',
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードと一致しません',
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],

            //パスワードは必ずハッシュ化して保存
            'password' => Hash::make($input['password']),

            //会員登録画面から作成されるユーザーは一般ユーザー
            'admin' => false,
        ]);
    }
}