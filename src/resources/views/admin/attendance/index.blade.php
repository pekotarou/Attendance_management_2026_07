@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css') }}">
@endsection

@section('content')
<div class="admin-attendance-list">
    <div class="admin-attendance-list__inner">
        <h1 class="admin-attendance-list__heading">
            {{ $currentDate->format('Y年n月j日') }}の勤怠
        </h1>

        <div class="admin-attendance-list__date">
            <a
                class="admin-attendance-list__date-button"
                href="/admin/attendance/list?date={{ $previousDate }}">
                前日
            </a>

            <p class="admin-attendance-list__date-text">
                <img
                    class="admin-attendance-list__calendar-icon"
                    src="{{ asset('images/calendar-icon.png') }}"
                    alt="カレンダー">
                {{ $currentDate->format('Y/m/d') }}
            </p>

            <a
                class="admin-attendance-list__date-button"
                href="/admin/attendance/list?date={{ $nextDate }}">
                翌日
            </a>
        </div>

        <table class="admin-attendance-table">
            <tr class="admin-attendance-table__row admin-attendance-table__row--head">
                <th class="admin-attendance-table__heading">名前</th>
                <th class="admin-attendance-table__heading">出勤</th>
                <th class="admin-attendance-table__heading">退勤</th>
                <th class="admin-attendance-table__heading">休憩</th>
                <th class="admin-attendance-table__heading">合計</th>
                <th class="admin-attendance-table__heading">詳細</th>
            </tr>

            @foreach ($attendances as $attendance)
                @php
                    // 修正: 承認済みの修正申請があれば最新のものを取得
                    $approvedAttendanceEdit = $attendance->attendanceEdits
                        ->where('status', '承認済み')
                        ->sortByDesc('created_at')
                        ->first();

                    // 修正: 承認済み申請があれば、申請後の出勤・退勤を優先表示
                    $clockInTime = $approvedAttendanceEdit
                        ? $approvedAttendanceEdit->requested_clock_in_time
                        : $attendance->clock_in_time;

                    $clockOutTime = $approvedAttendanceEdit
                        ? $approvedAttendanceEdit->requested_clock_out_time
                        : $attendance->clock_out_time;

                    // 修正: 承認済み申請があれば、break_editsから休憩時間を計算
                    $breakMinutes = 0;

                    if ($approvedAttendanceEdit) {
                        foreach ($approvedAttendanceEdit->breakEdits as $breakEdit) {
                            if ($breakEdit->requested_break_in_time && $breakEdit->requested_break_out_time) {
                                $breakMinutes += \Carbon\Carbon::parse($breakEdit->requested_break_in_time)
                                    ->diffInMinutes(\Carbon\Carbon::parse($breakEdit->requested_break_out_time));
                            }
                        }
                    } else {
                        $breakMinutes = $attendance->breaks->sum('break_time');
                    }

                    // 修正: 表示用の出勤・退勤・休憩から合計時間を計算
                    $workMinutes = null;

                    if ($clockInTime && $clockOutTime) {
                        $workMinutes = \Carbon\Carbon::parse($clockInTime)
                            ->diffInMinutes(\Carbon\Carbon::parse($clockOutTime))
                            - $breakMinutes;
                    }
                @endphp

                <tr class="admin-attendance-table__row">
                    <td class="admin-attendance-table__data">
                        {{ $attendance->user->name }}
                    </td>

                    <td class="admin-attendance-table__data">
                        {{ $clockInTime ? \Carbon\Carbon::parse($clockInTime)->format('H:i') : '' }}
                    </td>

                    <td class="admin-attendance-table__data">
                        {{ $clockOutTime ? \Carbon\Carbon::parse($clockOutTime)->format('H:i') : '' }}
                    </td>

                    <td class="admin-attendance-table__data">
                        {{ $breakMinutes ? sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60) : '' }}
                    </td>

                    <td class="admin-attendance-table__data">
                        {{ $workMinutes ? sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60) : '' }}
                    </td>

                    <td class="admin-attendance-table__data">
                        <a
                            class="admin-attendance-table__link"
                            href="/admin/attendance/{{ $attendance->id }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection