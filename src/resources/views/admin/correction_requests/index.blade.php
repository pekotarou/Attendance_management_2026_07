@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-correction-request-list.css') }}">
@endsection

@section('content')
<div class="admin-correction-request-list">
    <div class="admin-correction-request-list__inner">
        <h1 class="admin-correction-request-list__heading">
            申請一覧
        </h1>

        <div class="admin-correction-request-list__tabs">
            <a
                class="admin-correction-request-list__tab {{ $tab === 'pending' ? 'admin-correction-request-list__tab--active' : '' }}"
                href="/admin/stamp_correction_request/list?status=pending">
                承認待ち
            </a>

            <a
                class="admin-correction-request-list__tab {{ $tab === 'approved' ? 'admin-correction-request-list__tab--active' : '' }}"
                href="/admin/stamp_correction_request/list?status=approved">
                承認済み
            </a>
        </div>

        <table class="admin-correction-request-table">
            <tr class="admin-correction-request-table__row admin-correction-request-table__row--head">
                <th class="admin-correction-request-table__heading">状態</th>
                <th class="admin-correction-request-table__heading">名前</th>
                <th class="admin-correction-request-table__heading">対象日時</th>
                <th class="admin-correction-request-table__heading">申請理由</th>
                <th class="admin-correction-request-table__heading">申請日時</th>
                <th class="admin-correction-request-table__heading">詳細</th>
            </tr>

            @foreach ($attendanceEdits as $attendanceEdit)
                <tr class="admin-correction-request-table__row">
                    <td class="admin-correction-request-table__data">
                        {{ $attendanceEdit->status }}
                    </td>

                    <td class="admin-correction-request-table__data">
                        {{ $attendanceEdit->user->name }}
                    </td>

                    <td class="admin-correction-request-table__data">
                        {{ \Carbon\Carbon::parse($attendanceEdit->attendance->date)->format('Y/m/d') }}
                    </td>

                    <td class="admin-correction-request-table__data">
                        {{ $attendanceEdit->note }}
                    </td>

                    <td class="admin-correction-request-table__data">
                        {{ $attendanceEdit->created_at->format('Y/m/d') }}
                    </td>

                    <td class="admin-correction-request-table__data">
                        {{-- 修正: 管理者用の申請詳細画面へ移動 --}}
                        <a
                            class="admin-correction-request-table__link"
                            href="/admin/stamp_correction_request/approve/{{ $attendanceEdit->id }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection