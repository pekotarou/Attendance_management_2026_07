@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <div class="attendance-list__inner">
        <h1 class="attendance-list__heading">
            勤怠一覧
        </h1>

        <div class="attendance-list__month">
            {{--前月リンク --}}
            <a
                class="attendance-list__month-button"
                href="/attendance/list?month={{ $previousMonth }}">
                ← 前月
            </a>

            <p class="attendance-list__month-text">
                <img
                    class="attendance-list__calendar-icon"
                    src="{{ asset('images/calendar-icon.png') }}"
                    alt="カレンダー">
                {{ $currentMonth->format('Y/m') }}
            </p>

            {{--翌月リンク --}}
            <a
                class="attendance-list__month-button"
                href="/attendance/list?month={{ $nextMonth }}">
                翌月 →
            </a>
        </div>

        <table class="attendance-table">
            <tr class="attendance-table__row">
                <th class="attendance-table__heading">日付</th>
                <th class="attendance-table__heading">出勤</th>
                <th class="attendance-table__heading">退勤</th>
                <th class="attendance-table__heading">休憩</th>
                <th class="attendance-table__heading">合計</th>
                <th class="attendance-table__heading">詳細</th>
            </tr>
            @foreach ($dates as $date)
                @php
                    // その日付に対応する勤怠データを取得
                    $attendance = $attendances->get($date->toDateString());

                    // 承認済みの修正申請があれば最新のものを取得
                    $approvedAttendanceEdit = $attendance
                        ? $attendance->attendanceEdits
                            ->where('status', '承認済み')
                            ->sortByDesc('created_at')
                            ->first()
                        : null;

                    // 承認済み申請があれば、申請後の出勤・退勤を優先表示
                    $clockInTime = $approvedAttendanceEdit
                        ? $approvedAttendanceEdit->requested_clock_in_time
                        : ($attendance ? $attendance->clock_in_time : null);

                    $clockOutTime = $approvedAttendanceEdit
                        ? $approvedAttendanceEdit->requested_clock_out_time
                        : ($attendance ? $attendance->clock_out_time : null);

                    // 承認済み申請があれば、break_editsから休憩時間を計算
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

                    // 表示用の出勤・退勤・休憩から合計時間を計算
                    $workMinutes = null;

                    if ($clockInTime && $clockOutTime) {
                        $workMinutes = \Carbon\Carbon::parse($clockInTime)
                            ->diffInMinutes(\Carbon\Carbon::parse($clockOutTime))
                            - $breakMinutes;
                    }
                @endphp

                <tr class="attendance-table__row">
                    <td class="attendance-table__data">
                        {{ $date->isoFormat('MM/DD(ddd)') }}
                    </td>

                    <td class="attendance-table__data">
                        {{ $clockInTime ? \Carbon\Carbon::parse($clockInTime)->format('H:i') : '' }}
                    </td>

                    <td class="attendance-table__data">
                        {{ $clockOutTime ? \Carbon\Carbon::parse($clockOutTime)->format('H:i') : '' }}                    </td>

                    <td class="attendance-table__data">
                        {{ $breakMinutes > 0 ? floor($breakMinutes / 60) . ':' . sprintf('%02d', $breakMinutes % 60) : '' }}
                    </td>

                    <td class="attendance-table__data">
                        {{ $workMinutes !== null ? floor($workMinutes / 60) . ':' . sprintf('%02d', $workMinutes % 60) : '' }}
                    </td>

                    <td class="attendance-table__data">
                        @if ($attendance)
                            <a class="attendance-table__link" href="/attendance/{{ $attendance->id }}">
                                詳細
                            </a>
                        @else
                            <span class="attendance-table__link">
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