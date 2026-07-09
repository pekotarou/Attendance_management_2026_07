@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-detail.css') }}">
@endsection

@section('content')
<div class="admin-attendance-detail">
    <div class="admin-attendance-detail__inner">
        <h1 class="admin-attendance-detail__heading">
            勤怠詳細
        </h1>

        <form
            class="admin-attendance-detail-form" 
            action="/admin/attendance/{{ $attendance->id }}/correction" 
            method="post">
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

            <table class="admin-attendance-detail-table">
                <tr class="admin-attendance-detail-table__row">
                    <th class="admin-attendance-detail-table__heading">名前</th>
                    <td class="admin-attendance-detail-table__data">
                        {{ $attendance->user->name }}
                    </td>
                </tr>

                <tr class="admin-attendance-detail-table__row">
                    <th class="admin-attendance-detail-table__heading">日付</th>
                    <td class="admin-attendance-detail-table__data">
                        {{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}
                        <span class="admin-attendance-detail-table__date-month">
                            {{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}
                        </span>
                    </td>
                </tr>

                <tr class="admin-attendance-detail-table__row">
                    <th class="admin-attendance-detail-table__heading">出勤・退勤</th>
                    <td class="admin-attendance-detail-table__data">
                        <input
                            class="admin-attendance-detail-form__time-input"
                            type="text"
                            name="clock_in_time"
                            id="clock_in_time"
                            value="{{ $clockInTime ? \Carbon\Carbon::parse($clockInTime)->format('H:i') : '' }}">

                        <span class="admin-attendance-detail-form__separator">〜</span>

                        <input
                            class="admin-attendance-detail-form__time-input"
                            type="text"
                            name="clock_out_time"
                            id="clock_out_time"
                            value="{{ $clockOutTime ? \Carbon\Carbon::parse($clockOutTime)->format('H:i') : '' }}">
                        @error('clock_in_time')
                            <p class="admin-attendance-detail-form__error">{{ $message }}</p>
                        @enderror

                        @error('clock_out_time')
                            <p class="admin-attendance-detail-form__error">{{ $message }}</p>
                        @enderror
                    </td>
                    
                </tr>

               

                @for ($i = 0; $i < $breakRowCount; $i++)
                    @php
                        $break = $breaks->get($i);
                        $breakLabel = $i === 0 ? '休憩' : '休憩' . ($i + 1);

                        // 修正: 承認待ち・承認済みはbreak_edits、申請なしはbreaksから時刻を取得
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

                    <tr class="admin-attendance-detail-table__row">
                        <th class="admin-attendance-detail-table__heading">
                            {{ $breakLabel }}
                        </th>

                        <td class="admin-attendance-detail-table__data">
                           <input
                                type="hidden"
                                name="break_id[]"
                                value="{{ $breakId }}">

                            <input
                                class="admin-attendance-detail-form__time-input"
                                type="text"
                                name="break_in_time[]"
                                value="{{ $breakInTime ? \Carbon\Carbon::parse($breakInTime)->format('H:i') : '' }}">

                            <span class="admin-attendance-detail-form__separator">〜</span>

                            <input
                                class="admin-attendance-detail-form__time-input"
                                type="text"
                                name="break_out_time[]"
                                value="{{ $breakOutTime ? \Carbon\Carbon::parse($breakOutTime)->format('H:i') : '' }}">

                        </td>
                    </tr>
                @endfor

                <tr class="admin-attendance-detail-table__row">
                    <th class="admin-attendance-detail-table__heading">備考</th>
                    <td class="admin-attendance-detail-table__data admin-attendance-detail-table__data--note">
                        <textarea
                            class="admin-attendance-detail-form__textarea"
                            name="note"
                            id="note">{{ old('note', $displayAttendanceEdit ? $displayAttendanceEdit->note : $attendance->note) }}</textarea>
                        @error('note')
                            <p class="admin-attendance-detail-form__error">{{ $message }}</p>
                        @enderror
                    </td>
                    
                </tr>
            </table>

            <div class="admin-attendance-detail-form__button-area">
                <button class="admin-attendance-detail-form__button" type="submit">
                    修正
                </button>
            </div>
        </form>
    </div>
</div>
@endsection