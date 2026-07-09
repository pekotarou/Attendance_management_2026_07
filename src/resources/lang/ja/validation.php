<?php

return [
    //必須項目の共通メッセージ
    'required' => ':attributeを入力してください',

    //メール形式の共通メッセージ
    'email' => ':attributeは「ユーザー名@ドメイン」形式で入力してください',

    //最小文字数
    'min' => [
        'string' => ':attributeは:min文字以上で入力してください',
    ],

    //確認用パスワードとの一致
    'confirmed' => ':attributeと一致しません',

    //一意制約
    'unique' => 'この:attributeは既に登録されています',

    //項目名を日本語化
    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => '確認用パスワード',
    ],
];