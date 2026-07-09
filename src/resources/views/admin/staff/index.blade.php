@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-staff-list.css') }}">
@endsection

@section('content')
<div class="admin-staff-list">
    <div class="admin-staff-list__inner">
        <h1 class="admin-staff-list__heading">
            スタッフ一覧
        </h1>

        <table class="admin-staff-table">
            <tr class="admin-staff-table__row admin-staff-table__row--head">
                <th class="admin-staff-table__heading">名前</th>
                <th class="admin-staff-table__heading">メールアドレス</th>
                <th class="admin-staff-table__heading">月次勤怠</th>
            </tr>

            @foreach ($users as $user)
                <tr class="admin-staff-table__row">
                    <td class="admin-staff-table__data">
                        {{ $user->name }}
                    </td>

                    <td class="admin-staff-table__data">
                        {{ $user->email }}
                    </td>

                    <td class="admin-staff-table__data">
                        {{-- 修正: 次に作るスタッフ別勤怠一覧へつなぐ --}}
                        <a
                            class="admin-staff-table__link"
                            href="/admin/attendance/staff/{{ $user->id }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection