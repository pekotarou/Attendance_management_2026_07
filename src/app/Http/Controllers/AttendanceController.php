<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak; // 修正: 休憩モデルを追加
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\AttendanceCorrectionRequest; // 修正: 勤怠修正申請のFormRequest
use App\Models\AttendanceEdit; // 修正: 勤怠修正申請モデル
use App\Models\BreakEdit; // 修正: 休憩修正申請モデル

class AttendanceController extends Controller
{
    /**
     * 勤怠登録画面を表示
     */
    public function index()
    {
        $now = Carbon::now();

        // 修正: 今日の勤怠データを取得
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', $now->toDateString())
            ->first();

        $status = '勤務外';

        if ($attendance && $attendance->clock_in_time && ! $attendance->clock_out_time) {
            $status = '出勤中';

            // 修正: 休憩中かどうか確認
            $latestBreak = AttendanceBreak::where('attendance_id', $attendance->id)
                ->latest()
                ->first();

            if ($latestBreak && ! $latestBreak->break_out_time) {
                $status = '休憩中';
            }
        }

        if ($attendance && $attendance->clock_out_time) {
            $status = '退勤済';
        }

        return view('attendance.index', [
            'date' => $now->isoFormat('YYYY年M月D日(ddd)'),
            'time' => $now->format('H:i'),
            'status' => $status,
            'attendance' => $attendance,
        ]);
    }

    /**
     * 出勤処理
     */
    public function clockIn(Request $request)
    {
        $now = Carbon::now();

        Attendance::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'date' => $now->toDateString(),
            ],
            [
                'clock_in_time' => $now,
            ]
        );

        return redirect('/attendance');
    }

    /**
     * 休憩入処理
     */
    public function breakIn(Request $request)
    {
        $now = Carbon::now();

        // 修正: 今日の出勤データを取得
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', $now->toDateString())
            ->first();

        // 修正: 出勤データがない場合は勤怠画面へ戻す
        if (! $attendance) {
            return redirect('/attendance');
        }

        // 修正: すでに休憩中の場合は二重登録しない
        $activeBreak = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_out_time')
            ->first();

        if ($activeBreak) {
            return redirect('/attendance');
        }

        // 修正: 休憩開始時刻を保存
        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_in_time' => $now,
        ]);

        return redirect('/attendance');
    }

    /**
     * 休憩戻処理
     */
    public function breakOut(Request $request)
    {
        $now = Carbon::now();

        // 修正: 今日の勤怠データを取得
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', $now->toDateString())
            ->first();

        // 修正: 勤怠データがない場合は戻す
        if (! $attendance) {
            return redirect('/attendance');
        }

        // 修正: 終了していない最新の休憩データを取得
        $activeBreak = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_out_time')
            ->latest()
            ->first();

        // 修正: 休憩中データがない場合は戻す
        if (! $activeBreak) {
            return redirect('/attendance');
        }

        // 修正: 休憩開始から休憩終了までの分数を計算
        $breakTime = Carbon::parse($activeBreak->break_in_time)->diffInMinutes($now);

        // 修正: 休憩終了時刻と休憩時間を保存
        $activeBreak->update([
            'break_out_time' => $now,
            'break_time' => $breakTime,
        ]);

        return redirect('/attendance');
    }

    /**
     * 退勤処理
     */
    public function clockOut(Request $request)
    {
        $now = Carbon::now();

        // 修正: 今日の勤怠データを取得
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', $now->toDateString())
            ->first();

        // 修正: 勤怠データがない場合は戻す
        if (! $attendance) {
            return redirect('/attendance');
        }

        // 修正: 休憩中の場合は退勤できないようにする
        $activeBreak = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_out_time')
            ->first();

        if ($activeBreak) {
            return redirect('/attendance');
        }

        // 修正: すでに退勤済みの場合は二重登録しない
        if ($attendance->clock_out_time) {
            return redirect('/attendance');
        }

        // 修正: 退勤時刻を保存
        $attendance->update([
            'clock_out_time' => $now,
        ]);

        return redirect('/attendance');
    }


    /**
     * 勤怠一覧画面を表示
     */
    public function list(Request $request)
    {
        // 修正: URLにmonthがあればその月、なければ今月を表示
        $currentMonth = $request->filled('month')
            ? Carbon::parse($request->month)
            : Carbon::now();

        // 修正: 前月・翌月のリンク用
        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // 修正: 指定月の開始日・終了日を作成
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // 修正: ログインユーザーの指定月の勤怠データを取得
        // keyBy('date') で「日付」をキーにして取り出しやすくする
        $attendances = Attendance::with('breaks')
            ->where('user_id', auth()->id())
            ->whereBetween('date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->date)->toDateString();
            });

        // 修正: その月の全日付を作成
        $dates = [];

        $date = $startOfMonth->copy();

        while ($date->lte($endOfMonth)) {
            $dates[] = $date->copy();
            $date->addDay();
        }

        return view('attendance.list', [
            'currentMonth' => $currentMonth,
            'previousMonth' => $previousMonth,
            'nextMonth' => $nextMonth,
            'attendances' => $attendances,
            'dates' => $dates,
        ]);
    }

    /**
     * 勤怠詳細画面を表示
     */
    public function detail(Attendance $attendance)
    {
        // 修正: 他人の勤怠詳細を見られないようにする
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        // 修正: 休憩データも一緒に取得
        $attendance->load('breaks');

        // 修正: 承認待ちの修正申請があるか確認
        $pendingAttendanceEdit = AttendanceEdit::where('attendance_id', $attendance->id)
            ->where('user_id', auth()->id())
            ->where('status', '承認待ち')
            ->latest()
            ->first();

        return view('attendance.detail', [
            'attendance' => $attendance,
            'pendingAttendanceEdit' => $pendingAttendanceEdit,
        ]);
    }

    /**
     * 勤怠修正申請を保存
     */
    public function storeCorrection(AttendanceCorrectionRequest $request, Attendance $attendance)
    {
        // 修正: 他人の勤怠に対して申請できないようにする
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validated();

        // 修正: 入力された時刻を、対象日の日時として保存する
        $date = Carbon::parse($attendance->date)->toDateString();

        // 修正: 勤怠修正申請を保存
        $attendanceEdit = AttendanceEdit::create([
            'attendance_id' => $attendance->id,
            'user_id' => auth()->id(),
            'requested_clock_in_time' => $validated['clock_in_time']
                ? Carbon::parse($date . ' ' . $validated['clock_in_time'])
                : null,
            'requested_clock_out_time' => $validated['clock_out_time']
                ? Carbon::parse($date . ' ' . $validated['clock_out_time'])
                : null,
            'status' => '承認待ち',
            'note' => $validated['note'],
        ]);

        // 修正: 休憩修正申請を保存
        $breakIds = $request->input('break_id', []);
        $breakInTimes = $request->input('break_in_time', []);
        $breakOutTimes = $request->input('break_out_time', []);

        foreach ($breakInTimes as $index => $breakInTime) {
            $breakOutTime = $breakOutTimes[$index] ?? null;
            $breakId = $breakIds[$index] ?? null;

            // 修正: 休憩開始・終了がどちらも空なら保存しない
            if (empty($breakInTime) && empty($breakOutTime)) {
                continue;
            }

            BreakEdit::create([
                'attendance_edit_id' => $attendanceEdit->id,
                'break_id' => $breakId ?: null,
                'requested_break_in_time' => $breakInTime
                    ? Carbon::parse($date . ' ' . $breakInTime)
                    : null,
                'requested_break_out_time' => $breakOutTime
                    ? Carbon::parse($date . ' ' . $breakOutTime)
                    : null,
            ]);
        }

        return redirect('/attendance/' . $attendance->id);
    }
}