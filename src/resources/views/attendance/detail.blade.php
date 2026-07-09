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

            {{-- 修正: この画面で使う表示用データを先にまとめて定義 --}}
            @php
                // 優先順位：承認待ち → 承認済み → 元データ
                $displayAttendanceEdit = $pendingAttendanceEdit ?: $approvedAttendanceEdit;

                // 出勤・退勤の表示用データ
                $clockInTime = $displayAttendanceEdit
                    ? $displayAttendanceEdit->requested_clock_in_time
                    : $attendance->clock_in_time;

                $clockOutTime = $displayAttendanceEdit
                    ? $displayAttendanceEdit->requested_clock_out_time
                    : $attendance->clock_out_time;

                // 休憩の表示用データ
                $breaks = $displayAttendanceEdit
                    ? $displayAttendanceEdit->breakEdits->values()
                    : $attendance->breaks->values();

                // 休憩は最低2行表示する
                $breakRowCount = max(2, $breaks->count());
            @endphp
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
                        @if ($pendingAttendanceEdit)
                            {{-- 修正: 承認待ち中は入力欄ではなく通常表示 --}}
                            <span class="attendance-detail-form__time-text">
                                {{ $clockInTime ? \Carbon\Carbon::parse($clockInTime)->format('H:i') : '' }}
                            </span>

                            <span class="attendance-detail-form__separator">〜</span>

                            <span class="attendance-detail-form__time-text">
                                {{ $clockOutTime ? \Carbon\Carbon::parse($clockOutTime)->format('H:i') : '' }}
                            </span>
                        @else
                            {{-- 通常時は入力欄 --}}
                            <input
                                class="attendance-detail-form__time-input"
                                type="text"
                                name="clock_in_time"
                                id="clock_in_time"
                                value="{{ $clockInTime ? \Carbon\Carbon::parse($clockInTime)->format('H:i') : '' }}">

                            <span class="attendance-detail-form__separator">〜</span>

                            <input
                                class="attendance-detail-form__time-input"
                                type="text"
                                name="clock_out_time"
                                id="clock_out_time"
                                value="{{ $clockOutTime ? \Carbon\Carbon::parse($clockOutTime)->format('H:i') : '' }}">
                        @endif
                    </td>
                </tr>

                @for ($i = 0; $i < $breakRowCount; $i++)
                    @php
                        $break = $breaks->get($i);

                        $breakLabel = $i === 0 ? '休憩' : '休憩' . ($i + 1);

                        // 承認待ち・承認済みはbreak_edits、申請なしはbreaksから時刻を取得
                        if ($displayAttendanceEdit) {
                            $breakId = $break ? $break->break_id : '';
                            $breakInTime = $break ? $break->requested_break_in_time : null;
                            $breakOutTime = $break ? $break->requested_break_out_time : null;
                        } else {
                            $breakId = $break ? $break->id : '';
                            $breakInTime = $break ? $break->break_in_time : null;
                            $breakOutTime = $break ? $break->break_out_time : null;
                        }
                    @endphp

                    <tr class="attendance-detail-table__row">
                        <th class="attendance-detail-table__heading">
                            {{ $breakLabel }}
                        </th>

                        <td class="attendance-detail-table__data">
                            @if ($pendingAttendanceEdit)
                                {{-- 修正: 承認待ち中は入力欄ではなく通常表示 --}}
                                <span class="attendance-detail-form__time-text">
                                    {{ $breakInTime ? \Carbon\Carbon::parse($breakInTime)->format('H:i') : '' }}
                                </span>

                                <span class="attendance-detail-form__separator">〜</span>

                                <span class="attendance-detail-form__time-text">
                                    {{ $breakOutTime ? \Carbon\Carbon::parse($breakOutTime)->format('H:i') : '' }}
                                </span>
                            @else
                                {{-- 通常時は入力欄 --}}
                                <input
                                    type="hidden"
                                    name="break_id[]"
                                    value="{{ $breakId }}">

                                <input
                                    class="attendance-detail-form__time-input"
                                    type="text"
                                    name="break_in_time[]"
                                    value="{{ $breakInTime ? \Carbon\Carbon::parse($breakInTime)->format('H:i') : '' }}">

                                <span class="attendance-detail-form__separator">〜</span>

                                <input
                                    class="attendance-detail-form__time-input"
                                    type="text"
                                    name="break_out_time[]"
                                    value="{{ $breakOutTime ? \Carbon\Carbon::parse($breakOutTime)->format('H:i') : '' }}">
                            @endif
                        </td>
                    </tr>
                @endfor

                <tr class="attendance-detail-table__row">
                    <th class="attendance-detail-table__heading">備考</th>
                    <td class="attendance-detail-table__data attendance-detail-table__data--note">
                        @if ($pendingAttendanceEdit)
                            {{--承認待ち中は入力欄ではなく、申請した備考を通常表示 --}}
                            <p class="attendance-detail-form__note-text">{{ $pendingAttendanceEdit->note }}</p>
                        @else
                            {{--通常時は備考入力欄を表示 --}}
                            <textarea
                                class="attendance-detail-form__textarea"
                                name="note"
                                id="note">{{ old('note', $approvedAttendanceEdit ? $approvedAttendanceEdit->note : $attendance->note) }}</textarea>

                            @error('note')
                                <p class="attendance-detail-form__error">{{ $message }}</p>
                            @enderror
                        @endif
                    </td>
                </tr>
            </table>

            <div class="attendance-detail-form__button-area">
                @if ($pendingAttendanceEdit)
                    {{--承認待ち中は修正ボタンを表示しない --}}
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