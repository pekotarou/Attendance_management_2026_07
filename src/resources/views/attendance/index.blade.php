@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__inner">
        <p class="attendance__status">
            {{ $status }}
        </p>

        <p class="attendance__date">
            {{ $date }}
        </p>

        <p class="attendance__time">
            {{ $time }}
        </p>

        @if ($status === '勤務外')
            <form class="attendance__form" action="/attendance/clock-in" method="post">
                @csrf
                <button class="attendance__button" type="submit">
                    出勤
                </button>
            </form>
        @elseif ($status === '出勤中')
            <div class="attendance__button-area">
                {{--退勤処理 --}}
                <form class="attendance__form" action="/attendance/clock-out" method="post">
                    @csrf
                    <button class="attendance__button" type="submit">
                        退勤
                    </button>
                </form>

                {{--休憩入処理 --}}
                <form class="attendance__form" action="/attendance/break-in" method="post">
                    @csrf
                    <button class="attendance__button attendance__button--secondary" type="submit">
                        休憩入
                    </button>
                </form>
            </div>
        @elseif ($status === '休憩中')
            {{--休憩戻処理 --}}
            <form class="attendance__form" action="/attendance/break-out" method="post">
                @csrf
                <button class="attendance__button attendance__button--secondary" type="submit">
                    休憩戻
                </button>
            </form>
        @elseif ($status === '退勤済')
            <p class="attendance__message">
                お疲れ様でした。
            </p>
        @endif
    </div>
</div>
@endsection