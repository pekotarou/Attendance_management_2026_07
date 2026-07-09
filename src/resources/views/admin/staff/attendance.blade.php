@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-staff-attendance.css') }}">
@endsection

@section('content')
<div class="admin-staff-attendance">
    <div class="admin-staff-attendance__inner">
        <h1 class="admin-staff-attendance__heading">
            {{ $user->name }}さんの勤怠
        </h1>

        <div class="admin-staff-attendance__month">
            <a
                class="admin-staff-attendance__month-button"
                href="/admin/attendance/staff/{{ $user->id }}?month={{ $previousMonth }}">
                ← 前月
            </a>

            <p class="admin-staff-attendance__month-text">
                <img
                    class="admin-staff-attendance__calendar-icon"
                    src="{{ asset('images/calendar-icon.png') }}"
                    alt="カレンダー">
                {{ $currentMonth->format('Y/m') }}
            </p>

            <a
                class="admin-staff-attendance__month-button"
                href="/admin/attendance/staff/{{ $user->id }}?month={{ $nextMonth }}">
                翌月 →
            </a>
        </div>

        <table class="admin-staff-attendance-table">
            <tr class="admin-staff-attendance-table__row admin-staff-attendance-table__row--head">
                <th class="admin-staff-attendance-table__heading">日付</th>
                <th class="admin-staff-attendance-table__heading">出勤</th>
                <th class="admin-staff-attendance-table__heading">退勤</th>
                <th class="admin-staff-attendance-table__heading">休憩</th>
                <th class="admin-staff-attendance-table__heading">合計</th>
                <th class="admin-staff-attendance-table__heading">詳細</th>
            </tr>

            @foreach ($dates as $date)
                @php
                    // 修正: その日付に対応する勤怠データを取得
                    $attendance = $attendances->get($date->toDateString());

                    // 修正: 承認済みの修正申請があれば最新のものを取得
                    $approvedAttendanceEdit = $attendance
                        ? $attendance->attendanceEdits
                            ->where('status', '承認済み')
                            ->sortByDesc('created_at')
                            ->first()
                        : null;

                    // 修正: 承認済み申請があれば、申請後の出勤・退勤を優先表示
                    $clockInTime = $approvedAttendanceEdit
                        ? $approvedAttendanceEdit->requested_clock_in_time
                        : ($attendance ? $attendance->clock_in_time : null);

                    $clockOutTime = $approvedAttendanceEdit
                        ? $approvedAttendanceEdit->requested_clock_out_time
                        : ($attendance ? $attendance->clock_out_time : null);

                    // 修正: 承認済み申請があれば、break_editsから休憩時間を計算
                    $breakMinutes = 0;

                    if ($approvedAttendanceEdit) {
                        foreach ($approvedAttendanceEdit->breakEdits as $breakEdit) {
                            if ($breakEdit->requested_break_in_time && $breakEdit->requested_break_out_time) {
                                $breakMinutes += \Carbon\Carbon::parse($breakEdit->requested_break_in_time)
                                    ->diffInMinutes(\Carbon\Carbon::parse($breakEdit->requested_break_out_time));
                            }
                        }
                    } elseif ($attendance) {
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

                <tr class="admin-staff-attendance-table__row">
                    <td class="admin-staff-attendance-table__data">
                        {{ $date->format('m/d') }}({{ ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] }})
                    </td>

                    <td class="admin-staff-attendance-table__data">
                        {{ $clockInTime ? \Carbon\Carbon::parse($clockInTime)->format('H:i') : '' }}
                    </td>

                    <td class="admin-staff-attendance-table__data">
                        {{ $clockOutTime ? \Carbon\Carbon::parse($clockOutTime)->format('H:i') : '' }}
                    </td>

                    <td class="admin-staff-attendance-table__data">
                        {{ $breakMinutes ? sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60) : '' }}
                    </td>

                    <td class="admin-staff-attendance-table__data">
                        {{ $workMinutes ? sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60) : '' }}
                    </td>

                    <td class="admin-staff-attendance-table__data">
                        @if ($attendance)
                            <a
                                class="admin-staff-attendance-table__link"
                                href="/admin/attendance/{{ $attendance->id }}">
                                詳細
                            </a>
                        @else
                            <span class="admin-staff-attendance-table__link">
                                詳細
                            </span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection