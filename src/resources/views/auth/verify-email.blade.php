@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-email">
    <div class="verify-email__inner">
        <p class="verify-email__message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        {{-- 修正: MailHogのメール確認画面へ移動 --}}
        <a
            class="verify-email__button"
            href="http://localhost:8025"
            target="_blank">
            認証はこちらから
        </a>

        {{-- 修正: 認証メール再送 --}}
        <form class="verify-email__resend-form" action="/email/verification-notification" method="post">
            @csrf

            <button class="verify-email__resend-button" type="submit">
                認証メールを再送する
            </button>
        </form>
    </div>
</div>
@endsection