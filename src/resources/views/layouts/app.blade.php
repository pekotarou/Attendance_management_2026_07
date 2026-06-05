<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理アプリ</title>

    {{-- 修正: 共通CSS --}}
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">

    {{-- 修正: 各画面専用CSS --}}
    @yield('css')
</head>

<body>
    <div class="app">
        <header class="header">
            <div class="header__inner">
                <a href="/" class="header__logo">
                    {{-- 修正: COACHTECHロゴ画像を表示 --}}
                    <img
                        class="header__logo-image"
                        src="{{ asset('images/logo.png') }}"
                        alt="COACHTECH">
                </a>

                {{-- 修正: ログイン中だけログアウトボタンを表示 --}}
                @auth
                    <form class="header-logout" action="/logout" method="post">
                        @csrf
                        <button class="header-logout__button" type="submit">
                            ログアウト
                        </button>
                    </form>
                @endauth
            </div>
        </header>

        <main class="main">
            @yield('content')
        </main>
    </div>
</body>

</html>