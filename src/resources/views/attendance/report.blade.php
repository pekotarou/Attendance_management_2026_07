@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-report.css') }}">
@endsection

@section('content')
<div class="attendance-report">
    <div class="attendance-report__inner">
        <h1 class="attendance-report__heading">
            マイ勤怠レポート
        </h1>

        <p class="attendance-report__lead">
            過去６ヶ月の勤怠データから集計しています。
        </p>

        <section class="attendance-report__section">
            <h2 class="attendance-report__section-heading">
                基本サマリー
            </h2>

            <div class="attendance-report-summary">
                <div class="attendance-report-summary__card">
                    <p class="attendance-report-summary__label">総労働時間</p>
                    <p class="attendance-report-summary__value">
                        {{ floor($totalWorkMinutes / 60) }}h {{ $totalWorkMinutes % 60 }}m
                    </p>
                </div>

                <div class="attendance-report-summary__card">
                    <p class="attendance-report-summary__label">総残業時間</p>
                    <p class="attendance-report-summary__value">
                        {{ floor($totalOvertimeMinutes / 60) }}h {{ $totalOvertimeMinutes % 60 }}m
                    </p>
                </div>

                <div class="attendance-report-summary__card">
                    <p class="attendance-report-summary__label">平均労働時間 / 日</p>
                    <p class="attendance-report-summary__value">
                        {{ floor($averageWorkMinutes / 60) }}h {{ $averageWorkMinutes % 60 }}m
                    </p>
                </div>
            </div>
        </section>

        <section class="attendance-report__section">
            <h2 class="attendance-report__section-heading">
                月次推移（過去６ヶ月）
            </h2>

            <table class="attendance-report-table">
                <tr class="attendance-report-table__row attendance-report-table__row--head">
                    <th class="attendance-report-table__heading">月</th>
                    <th class="attendance-report-table__heading">労働時間</th>
                    <th class="attendance-report-table__heading">残業時間</th>
                </tr>

                @foreach ($monthlyReports as $monthlyReport)
                    <tr class="attendance-report-table__row">
                        <td class="attendance-report-table__data">
                            {{ $monthlyReport['month'] }}
                        </td>

                        <td class="attendance-report-table__data">
                            {{ floor($monthlyReport['work_minutes'] / 60) }}h {{ $monthlyReport['work_minutes'] % 60 }}m
                        </td>

                        <td class="attendance-report-table__data">
                            {{ floor($monthlyReport['overtime_minutes'] / 60) }}h {{ $monthlyReport['overtime_minutes'] % 60 }}m
                        </td>
                    </tr>
                @endforeach
            </table>
        </section>

        <section class="attendance-report__section">
            <h2 class="attendance-report__section-heading">
                今月の異常検知
            </h2>

            <p class="attendance-report__note">
                基準：始業 09:00 / 終業 18:00 / 実労働時間が1日10時間超
            </p>

            <div class="attendance-report-summary">
                <div class="attendance-report-summary__card">
                    <p class="attendance-report-summary__label">遅刻回数</p>
                    <p class="attendance-report-summary__value">
                        {{ $lateCount }}回
                    </p>
                </div>

                <div class="attendance-report-summary__card">
                    <p class="attendance-report-summary__label">早退回数</p>
                    <p class="attendance-report-summary__value">
                        {{ $earlyLeaveCount }}回
                    </p>
                </div>

                <div class="attendance-report-summary__card">
                    <p class="attendance-report-summary__label">長時間労働日数</p>
                    <p class="attendance-report-summary__value">
                        {{ $longWorkDayCount }}日
                    </p>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection