<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEdit;
use Illuminate\Http\Request;

class AdminCorrectionRequestController extends Controller
{
    /**
     * 管理者用の申請一覧画面を表示
     */
    public function index(Request $request)
    {
        // 修正: 管理者以外はアクセス禁止
        if (! auth()->user()->admin) {
            abort(403);
        }

        // 修正: タブ判定
        $tab = $request->query('status', 'pending');

        $status = $tab === 'approved'
            ? '承認済み'
            : '承認待ち';

        // 修正: 全ユーザーの申請を取得
        $attendanceEdits = AttendanceEdit::with(['attendance', 'user'])
            ->where('status', $status)
            ->latest()
            ->get();

        return view('admin.correction_requests.index', [
            'attendanceEdits' => $attendanceEdits,
            'tab' => $tab,
        ]);
    }

    /**
     * 管理者用の申請承認処理
     */
    public function approve(AttendanceEdit $attendanceEdit)
    {
        // 修正: 管理者以外はアクセス禁止
        if (! auth()->user()->admin) {
            abort(403);
        }

        // 修正: 申請ステータスだけ承認済みにする
        // attendances / breaks は上書きしない
        $attendanceEdit->update([
            'status' => '承認済み',
        ]);

        return redirect('/admin/stamp_correction_request/approve/' . $attendanceEdit->id);
    }
    /**
     * 管理者用の申請承認画面を表示
     */
    public function show(AttendanceEdit $attendanceEdit)
    {
        // 修正: 管理者以外はアクセス禁止
        if (! auth()->user()->admin) {
            abort(403);
        }

        // 修正: 勤怠・ユーザー・休憩修正データを取得
        $attendanceEdit->load([
            'attendance.user',
            'breakEdits',
        ]);

        return view('admin.correction_requests.show', [
            'attendanceEdit' => $attendanceEdit,
        ]);
    }
}