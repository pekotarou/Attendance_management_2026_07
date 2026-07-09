<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\AdminAttendanceCorrectionRequest;
use App\Models\AttendanceEdit; // 修正: 勤怠修正履歴用
use App\Models\BreakEdit; // 修正: 休憩修正履歴用
use Illuminate\Support\Facades\DB; // 修正: トランザクション用

class AdminAttendanceController extends Controller
{
    /**
     * 管理者用の勤怠一覧画面を表示
     */
    public function index(Request $request)
    {
        // 修正: 管理者以外はアクセス禁止
        if (! auth()->user()->admin) {
            abort(403);
        }

        // 修正: 表示する日付。指定がなければ今日
        $currentDate = $request->filled('date')
            ? Carbon::parse($request->date)
            : Carbon::today();

        $previousDate = $currentDate->copy()->subDay()->toDateString();
        $nextDate = $currentDate->copy()->addDay()->toDateString();

        // 修正: 指定日の全ユーザー勤怠を取得
        // 修正: 承認済み申請データも一緒に取得
        $attendances = Attendance::with(['user', 'breaks', 'attendanceEdits.breakEdits'])
            ->whereDate('date', $currentDate->toDateString())
            ->get();

        return view('admin.attendance.index', [
            'attendances' => $attendances,
            'currentDate' => $currentDate,
            'previousDate' => $previousDate,
            'nextDate' => $nextDate,
        ]);
    }

    /**
     * 管理者用の勤怠詳細画面を表示
     */
    public function detail(Attendance $attendance)
    {
        // 修正: 管理者以外はアクセス禁止
        if (! auth()->user()->admin) {
            abort(403);
        }

        // 修正: ユーザー・休憩データを一緒に取得
        $attendance->load(['user', 'breaks']);

        // 修正: この勤怠に対する承認待ち申請を取得
        $pendingAttendanceEdit = $attendance->attendanceEdits()
            ->with('breakEdits')
            ->where('status', '承認待ち')
            ->latest()
            ->first();

        // 修正: この勤怠に対する承認済み申請を取得
        $approvedAttendanceEdit = $attendance->attendanceEdits()
            ->with('breakEdits')
            ->where('status', '承認済み')
            ->latest()
            ->first();

        return view('admin.attendance.detail', [
            'attendance' => $attendance,
            'pendingAttendanceEdit' => $pendingAttendanceEdit,
            'approvedAttendanceEdit' => $approvedAttendanceEdit, // 修正: 追加
        ]);
    }


    /**
     * 管理者用の勤怠修正処理
     */
    public function update(AdminAttendanceCorrectionRequest $request, Attendance $attendance)
    {
        // 修正: 管理者以外はアクセス禁止
        if (! auth()->user()->admin) {
            abort(403);
        }

        $validated = $request->validated();

        // 修正: 勤怠日付を取得
        $date = Carbon::parse($attendance->date)->toDateString();

        DB::transaction(function () use ($validated, $attendance, $date) {
            // 修正: 承認待ち申請があれば、それを承認済みに更新する
            $attendanceEdit = AttendanceEdit::where('attendance_id', $attendance->id)
                ->where('status', '承認待ち')
                ->latest()
                ->first();

            if ($attendanceEdit) {
                // 修正: 既存の承認待ち申請を、管理者入力内容で承認済みにする
                $attendanceEdit->update([
                    'requested_clock_in_time' => Carbon::parse($date . ' ' . $validated['clock_in_time']),
                    'requested_clock_out_time' => Carbon::parse($date . ' ' . $validated['clock_out_time']),
                    'status' => '承認済み',
                    'note' => $validated['note'],
                ]);

                // 修正: 既存の休憩修正申請を作り直す
                BreakEdit::where('attendance_edit_id', $attendanceEdit->id)->delete();
            } else {
                // 修正: 承認待ちがない場合は、管理者修正を承認済み履歴として新規作成する
                $attendanceEdit = AttendanceEdit::create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                    'requested_clock_in_time' => Carbon::parse($date . ' ' . $validated['clock_in_time']),
                    'requested_clock_out_time' => Carbon::parse($date . ' ' . $validated['clock_out_time']),
                    'status' => '承認済み',
                    'note' => $validated['note'],
                ]);
            }

            $breakIds = $validated['break_id'] ?? [];
            $breakInTimes = $validated['break_in_time'] ?? [];
            $breakOutTimes = $validated['break_out_time'] ?? [];

            foreach ($breakInTimes as $index => $breakInTime) {
                $breakOutTime = $breakOutTimes[$index] ?? null;
                $breakId = $breakIds[$index] ?? null;

                // 修正: 開始・終了が両方空なら保存しない
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
        });

        return redirect('/admin/attendance/' . $attendance->id);
    }
}