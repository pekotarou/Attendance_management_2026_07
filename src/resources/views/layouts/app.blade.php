<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理アプリ</title>

    {{--共通CSS --}}
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">

    {{--各画面専用CSS --}}
    @yield('css')
</head>

<body>
    <div class="app">
        <header class="header">
            <div class="header__inner">
                @php
                    // ログイン状態・権限によってロゴのリンク先を切り替える
                    $logoUrl = '/login';

                    if (auth()->check()) {
                        $logoUrl = auth()->user()->admin
                            ? '/admin/attendance/list'
                            : '/attendance';
                    }
                @endphp

                <a href="{{ $logoUrl }}" class="header__logo">
                    {{-- COACHTECHロゴ画像を表示 --}}
                    <img
                        class="header__logo-image"
                        src="{{ asset('images/logo.png') }}"
                        alt="COACHTECH">
                </a>
                

                {{-- 修正: ログイン中だけヘッダーメニューを表示 --}}
                @auth
                    @unless (request()->is('email/verify'))
                        <nav class="header-nav">
                            @if (auth()->user()->admin)
                                {{-- 修正: 管理者用メニュー --}}
                                <a class="header-nav__link" href="/admin/attendance/list">
                                    勤怠一覧
                                </a>

                                <a class="header-nav__link" href="/admin/staff/list">
                                    スタッフ一覧
                                </a>

                                <a class="header-nav__link" href="/admin/stamp_correction_request/list">
                                    申請一覧
                                </a>
                            @else
                                {{-- 修正: 一般ユーザー用メニュー --}}
                                <a class="header-nav__link" href="/attendance">
                                    勤怠
                                </a>

                                <a class="header-nav__link" href="/attendance/list">
                                    勤怠一覧
                                </a>

                                <a class="header-nav__link" href="/stamp_correction_request/list">
                                    申請
                                </a>
                                <a class="header-nav__link" href="/attendance/report">
                                    レポート
                                </a>
                            @endif

                            <form class="header-logout" action="/logout" method="post">
                                @csrf
                                <button class="header-logout__button" type="submit">
                                    ログアウト
                                </button>
                            </form>
                        </nav>
                    @endunless
                @endauth
            </div>
        </header>

        <main class="main">
            @yield('content')
        </main>
    </div>
</body>

</html>