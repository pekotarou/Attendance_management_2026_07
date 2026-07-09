<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance; // 修正: 勤怠モデルを読み込む
use Carbon\Carbon; // 修正: 日付操作用
use Illuminate\Http\Request; // 修正: リクエストを読み込む

class AdminStaffController extends Controller
{
    /**
     * 管理者用スタッフ一覧画面を表示
     */
    public function index()
    {
        // 修正: 管理者以外はアクセス禁止
        if (! auth()->user()->admin) {
            abort(403);
        }

        // 修正: 一般ユーザーだけ取得
        $users = User::where('admin', false)
            ->orderBy('id')
            ->get();

        return view('admin.staff.index', compact('users'));
    }
    /**
     * 管理者用スタッフ別月次勤怠画面を表示
     */
    public function attendance(Request $request, User $user)
    {
        // 修正: 管理者以外はアクセス禁止
        if (! auth()->user()->admin) {
            abort(403);
        }

        // 修正: 管理者ユーザーの勤怠画面は表示しない
        if ($user->admin) {
            abort(404);
        }

        // 修正: 表示する月。指定がなければ今月
        $currentMonth = $request->filled('month')
            ? Carbon::parse($request->month)
            : Carbon::now();

        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // 修正: その月の日付一覧を作成
        $dates = [];

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dates[] = $date->copy();
        }

        // 修正: 指定スタッフの月次勤怠を取得
        // 修正: 承認済み申請データも一緒に取得
        $attendances = Attendance::with(['breaks', 'attendanceEdits.breakEdits'])
            ->where('user_id', $user->id)
            ->whereBetween('date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->date)->toDateString();
            });

        return view('admin.staff.attendance', [
            'user' => $user,
            'dates' => $dates,
            'attendances' => $attendances,
            'currentMonth' => $currentMonth,
            'previousMonth' => $previousMonth,
            'nextMonth' => $nextMonth,
        ]);
    }
}