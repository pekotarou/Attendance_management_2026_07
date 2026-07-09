@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth">
    <div class="auth__inner">
        <h1 class="auth__heading">会員登録</h1>

        {{--Fortifyの会員登録URLに送信 --}}
        <form class="auth-form" action="/register" method="post">
            @csrf

            <div class="auth-form__group">
                <label class="auth-form__label" for="name">名前</label>
                <input
                    class="auth-form__input"
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}">
                @error('name')
                    <p class="auth-form__error">{{ $message }}</p>
                @enderror
            </div>

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

            <div class="auth-form__group">
                <label class="auth-form__label" for="password_confirmation">確認用パスワード</label>
                <input
                    class="auth-form__input"
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation">
            </div>

            <button class="auth-form__button" type="submit">
                登録する
            </button>
        </form>

        <div class="auth__link-area">
            <a class="auth__link" href="/login">ログインはこちら</a>
        </div>
    </div>
</div>
@endsection