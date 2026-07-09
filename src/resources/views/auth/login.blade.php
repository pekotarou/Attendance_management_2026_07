@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth">
    <div class="auth__inner">
        <h1 class="auth__heading">ログイン</h1>

        {{--FortifyのログインURLに送信 --}}
        <form class="auth-form" action="/login" method="post">
            @csrf

            <div class="auth-form__group">
                <label class="auth-form__label" for="email">メールアドレス</label>
                <input
                    class="auth-form__input"
                    type="text"
                    name="email"
                    id="email"
                    value="{{ old('email') }}">
                @error('email')
                    <p class="auth-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-form__group">
                <label class="auth-form__label" for="password">パスワード</label>
                <input
                    class="auth-form__input"
                    type="password"
                    name="password"
                    id="password">
                @error('password')
                    <p class="auth-form__error">{{ $message }}</p>
                @enderror
            </div>

            <button class="auth-form__button" type="submit">
                ログインする
            </button>
        </form>

        <div class="auth__link-area">
            <a class="auth__link" href="/register">会員登録はこちら</a>
        </div>
    </div>
</div>
@endsection