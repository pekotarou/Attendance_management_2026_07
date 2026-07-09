@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-correction-request-show.css') }}">
@endsection

@section('content')
<div class="admin-correction-request-show">
    <div class="admin-correction-request-show__inner">
        <h1 class="admin-correction-request-show__heading">
            勤怠詳細
        </h1>

        <form
            class="admin-correction-request-show-form"
            action="/admin/stamp_correction_request/approve/{{ $attendanceEdit->id }}"
            method="post">
            @csrf

            <table class="admin-correction-request-show-table">
                <tr class="admin-correction-request-show-table__row">
                    <th class="admin-correction-request-show-table__heading">名前</th>
                    <td class="admin-correction-request-show-table__data">
                        {{ $attendanceEdit->attendance->user->name }}
                    </td>
                </tr>

                <tr class="admin-correction-request-show-table__row">
                    <th class="admin-correction-request-show-table__heading">日付</th>
                    <td class="admin-correction-request-show-table__data">
                        {{ \Carbon\Carbon::parse($attendanceEdit->attendance->date)->format('Y年') }}
                        <span class="admin-correction-request-show-table__date-month">
                            {{ \Carbon\Carbon::parse($attendanceEdit->attendance->date)->format('n月j日') }}
                        </span>
                    </td>
                </tr>

                <tr class="admin-correction-request-show-table__row">
                    <th class="admin-correction-request-show-table__heading">出勤・退勤</th>
                    <td class="admin-correction-request-show-table__data">
                        <span class="admin-correction-request-show-form__time-text">
                            {{ $attendanceEdit->requested_clock_in_time ? \Carbon\Carbon::parse($attendanceEdit->requested_clock_in_time)->format('H:i') : '' }}
                        </span>

                        <span class="admin-correction-request-show-form__separator">〜</span>

                        <span class="admin-correction-request-show-form__time-text">
                            {{ $attendanceEdit->requested_clock_out_time ? \Carbon\Carbon::parse($attendanceEdit->requested_clock_out_time)->format('H:i') : '' }}
                        </span>
                    </td>
                </tr>

                @php
                    $breakEdits = $attendanceEdit->breakEdits->values();
                    $breakRowCount = max(2, $breakEdits->count());
                @endphp

                @for ($i = 0; $i < $breakRowCount; $i++)
                    @php
                        $breakEdit = $breakEdits->get($i);
                        $breakLabel = $i === 0 ? '休憩' : '休憩' . ($i + 1);
                    @endphp

                    <tr class="admin-correction-request-show-table__row">
                        <th class="admin-correction-request-show-table__heading">
                            {{ $breakLabel }}
                        </th>

                        <td class="admin-correction-request-show-table__data">
                            <span class="admin-correction-request-show-form__time-text">
                                {{ $breakEdit && $breakEdit->requested_break_in_time ? \Carbon\Carbon::parse($breakEdit->requested_break_in_time)->format('H:i') : '' }}
                            </span>

                            <span class="admin-correction-request-show-form__separator">〜</span>

                            <span class="admin-correction-request-show-form__time-text">
                                {{ $breakEdit && $breakEdit->requested_break_out_time ? \Carbon\Carbon::parse($breakEdit->requested_break_out_time)->format('H:i') : '' }}
                            </span>
                        </td>
                    </tr>
                @endfor

                <tr class="admin-correction-request-show-table__row">
                    <th class="admin-correction-request-show-table__heading">備考</th>
                    <td class="admin-correction-request-show-table__data admin-correction-request-show-table__data--note">
                        <p class="admin-correction-request-show-form__note-text">{{ $attendanceEdit->note }}</p>
                    </td>
                </tr>
            </table>

            <div class="admin-correction-request-show-form__button-area">
                @if ($attendanceEdit->status === '承認済み')
                    <button
                        class="admin-correction-request-show-form__button admin-correction-request-show-form__button--approved"
                        type="button"
                        disabled>
                        承認済み
                    </button>
                @else
                    <button class="admin-correction-request-show-form__button" type="submit">
                        承認
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection