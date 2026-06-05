<?php

return [
    // 修正: 必須項目の共通メッセージ
    'required' => ':attributeを入力してください',

    // 修正: メール形式の共通メッセージ
    'email' => ':attributeは「ユーザー名@ドメイン」形式で入力してください',

    // 修正: 最小文字数
    'min' => [
        'string' => ':attributeは:min文字以上で入力してください',
    ],

    // 修正: 確認用パスワードとの一致
    'confirmed' => ':attributeと一致しません',

    // 修正: 一意制約
    'unique' => 'この:attributeは既に登録されています',

    // 修正: 項目名を日本語化
    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => '確認用パスワード',
    ],
];