@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <div class="attendance-detail__inner">
        <h1 class="attendance-detail__heading">
            勤怠詳細
        </h1>

        <form class="attendance-detail-form" action="/attendance/{{ $attendance->id }}/correction" method="post">
            @csrf

            <table class="attendance-detail-table">
                <tr class="attendance-detail-table__row">
                    <th class="attendance-detail-table__heading">名前</th>
                    <td class="attendance-detail-table__data">
                        {{ $attendance->user->name }}
                    </td>
                </tr>

                <tr class="attendance-detail-table__row">
                    <th class="attendance-detail-table__heading">日付</th>
                    <td class="attendance-detail-table__data">
                        {{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}
                        <span class="attendance-detail-table__date-month">
                            {{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}
                        </span>
                    </td>
                </tr>

                <tr class="attendance-detail-table__row">
                    <th class="attendance-detail-table__heading">出勤・退勤</th>
                    <td class="attendance-detail-table__data">
                        <input
                            class="attendance-detail-form__time-input"
                            type="text"
                            name="clock_in_time"
                            id="clock_in_time"
                            value="{{ $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '' }}">

                        <span class="attendance-detail-form__separator">〜</span>

                        <input
                            class="attendance-detail-form__time-input"
                            type="text"
                            name="clock_out_time"
                            id="clock_out_time"
                            value="{{ $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '' }}">
                    </td>
                </tr>

                @php
                    // 修正: 休憩データを番号順に扱いやすくする
                    $breaks = $attendance->breaks->values();

                    // 修正: 休憩は最低2行表示する
                    $breakRowCount = max(2, $breaks->count());
                @endphp

                @for ($i = 0; $i < $breakRowCount; $i++)
                    @php
                        // 修正: 該当する休憩データを取得。なければnull
                        $break = $breaks->get($i);

                        // 修正: 1つ目は「休憩」、2つ目以降は「休憩2」「休憩3」
                        $breakLabel = $i === 0 ? '休憩' : '休憩' . ($i + 1);
                    @endphp

                    <tr class="attendance-detail-table__row">
                        <th class="attendance-detail-table__heading">
                            {{ $breakLabel }}
                        </th>

                        <td class="attendance-detail-table__data">
                            {{-- 修正: 後で修正申請に使うため、休憩IDも送れるようにする --}}
                            <input
                                type="hidden"
                                name="break_id[]"
                                value="{{ $break ? $break->id : '' }}">

                            <input
                                class="attendance-detail-form__time-input"
                                type="text"
                                name="break_in_time[]"
                                value="{{ $break && $break->break_in_time ? \Carbon\Carbon::parse($break->break_in_time)->format('H:i') : '' }}">

                            <span class="attendance-detail-form__separator">〜</span>

                            <input
                                class="attendance-detail-form__time-input"
                                type="text"
                                name="break_out_time[]"
                                value="{{ $break && $break->break_out_time ? \Carbon\Carbon::parse($break->break_out_time)->format('H:i') : '' }}">
                        </td>
                    </tr>
                @endfor

                <tr class="attendance-detail-table__row">
                    <th class="attendance-detail-table__heading">備考</th>
                    <td class="attendance-detail-table__data attendance-detail-table__data--note">
                        @if ($pendingAttendanceEdit)
                            {{-- 修正: 承認待ち中は入力欄ではなく、申請した備考を通常表示 --}}
                            <p class="attendance-detail-form__note-text">{{ $pendingAttendanceEdit->note }}</p>
                        @else
                            {{-- 修正: 通常時は備考入力欄を表示 --}}
                            <textarea
                                class="attendance-detail-form__textarea"
                                name="note"
                                id="note">{{ old('note', $attendance->note) }}</textarea>

                            @error('note')
                                <p class="attendance-detail-form__error">{{ $message }}</p>
                            @enderror
                        @endif
                    </td>
                </tr>
            </table>

            <div class="attendance-detail-form__button-area">
                @if ($pendingAttendanceEdit)
                    {{-- 修正: 承認待ち中は修正ボタンを表示しない --}}
                    <p class="attendance-detail-form__pending-message">
                        *承認待ちのため修正はできません。
                    </p>
                @else
                    <button class="attendance-detail-form__button" type="submit">
                        修正
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection