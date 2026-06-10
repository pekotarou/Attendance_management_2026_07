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
            {{-- 修正: 前月リンク --}}
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

            {{-- 修正: 翌月リンク --}}
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
                    // 修正: その日付に対応する勤怠データを取得
                    $attendance = $attendances->get($date->toDateString());

                    $breakMinutes = 0;
                    $workMinutes = null;

                    if ($attendance) {
                        $breakMinutes = $attendance->breaks->sum('break_time');

                        if ($attendance->clock_in_time && $attendance->clock_out_time) {
                            $workMinutes = \Carbon\Carbon::parse($attendance->clock_in_time)
                                ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out_time))
                                - $breakMinutes;
                        }
                    }
                @endphp

                <tr class="attendance-table__row">
                    <td class="attendance-table__data">
                        {{ $date->isoFormat('MM/DD(ddd)') }}
                    </td>

                    <td class="attendance-table__data">
                        {{ $attendance && $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '' }}
                    </td>

                    <td class="attendance-table__data">
                        {{ $attendance && $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '' }}
                    </td>

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