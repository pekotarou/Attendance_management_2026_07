@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/correction-request-list.css') }}">
@endsection

@section('content')
<div class="correction-request-list">
    <div class="correction-request-list__inner">
        <h1 class="correction-request-list__heading">
            申請一覧
        </h1>

        <div class="correction-request-list__tabs">
            <a
                class="correction-request-list__tab {{ $tab === 'pending' ? 'correction-request-list__tab--active' : '' }}"
                href="/stamp_correction_request/list?status=pending">
                承認待ち
            </a>

            <a
                class="correction-request-list__tab {{ $tab === 'approved' ? 'correction-request-list__tab--active' : '' }}"
                href="/stamp_correction_request/list?status=approved">
                承認済み
            </a>
        </div>

        <table class="correction-request-table">
            <tr class="correction-request-table__row correction-request-table__row--head">
                <th class="correction-request-table__heading">状態</th>
                <th class="correction-request-table__heading">名前</th>
                <th class="correction-request-table__heading">対象日時</th>
                <th class="correction-request-table__heading">申請理由</th>
                <th class="correction-request-table__heading">申請日時</th>
                <th class="correction-request-table__heading">詳細</th>
            </tr>

            @foreach ($attendanceEdits as $attendanceEdit)
                <tr class="correction-request-table__row">
                    <td class="correction-request-table__data">
                        {{ $attendanceEdit->status }}
                    </td>

                    <td class="correction-request-table__data">
                        {{ $attendanceEdit->user->name }}
                    </td>

                    <td class="correction-request-table__data">
                        {{ \Carbon\Carbon::parse($attendanceEdit->attendance->date)->format('Y/m/d') }}
                    </td>

                    <td class="correction-request-table__data">
                        {{ $attendanceEdit->note }}
                    </td>

                    <td class="correction-request-table__data">
                        {{ $attendanceEdit->created_at->format('Y/m/d') }}
                    </td>

                    <td class="correction-request-table__data">
                        <a
                            class="correction-request-table__link"
                            href="/attendance/{{ $attendanceEdit->attendance_id }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection