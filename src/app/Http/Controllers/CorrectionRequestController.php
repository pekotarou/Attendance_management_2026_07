<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEdit;
use Illuminate\Http\Request;

class CorrectionRequestController extends Controller
{
    /**
     * 申請一覧画面を表示
    */
    public function index(Request $request)
    {
        //タブ切り替え用。何も指定がなければ承認待ち
        $tab = $request->query('status', 'pending');

        $status = $tab === 'approved' ? '承認済み' : '承認待ち';

        //ログインユーザー自身の修正申請だけ取得
        $attendanceEdits = AttendanceEdit::with(['attendance', 'user'])
            ->where('user_id', auth()->id())
            ->where('status', $status)
            ->latest()
            ->get();

        return view('correction_requests.index', [
            'attendanceEdits' => $attendanceEdits,
            'tab' => $tab,
        ]);
    }
}