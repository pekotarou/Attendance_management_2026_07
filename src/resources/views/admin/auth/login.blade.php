@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth">
    <div class="auth__inner">
        <h1 class="auth__heading">管理者ログイン</h1>

        <form class="auth-form" action="/admin/login" method="post">
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
                管理者ログインする
            </button>
        </form>
    </div>
</div>
@endsection