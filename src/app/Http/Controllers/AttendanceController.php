<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak; //休憩モデルを追加
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\AttendanceCorrectionRequest; //勤怠修正申請のFormRequest
use App\Models\AttendanceEdit; //勤怠修正申請モデル
use App\Models\BreakEdit; //休憩修正申請モデル
use Illuminate\Support\Facades\DB; // トランザクション用

class AttendanceController extends Controller
{
    /**
     * 勤怠登録画面を表示
    */
    public function index()
    {
        // 管理者が一般ユーザー用勤怠画面に入らないようにする
        if (auth()->user()->admin) {
            return redirect('/admin/attendance/list');
        }
        $now = Carbon::now();

        //今日の勤怠データを取得
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', $now->toDateString())
            ->first();

        $status = '勤務外';

        if ($attendance && $attendance->clock_in_time && ! $attendance->clock_out_time) {
            $status = '出勤中';

            //休憩中かどうか確認
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

        //今日の出勤データを取得
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', $now->toDateString())
            ->first();

        //出勤データがない場合は勤怠画面へ戻す
        if (! $attendance) {
            return redirect('/attendance');
        }

        //すでに休憩中の場合は二重登録しない
        $activeBreak = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_out_time')
            ->first();

        if ($activeBreak) {
            return redirect('/attendance');
        }

        //休憩開始時刻を保存
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

        //今日の勤怠データを取得
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', $now->toDateString())
            ->first();

        //勤怠データがない場合は戻す
        if (! $attendance) {
            return redirect('/attendance');
        }

        //終了していない最新の休憩データを取得
        $activeBreak = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_out_time')
            ->latest()
            ->first();

        //休憩中データがない場合は戻す
        if (! $activeBreak) {
            return redirect('/attendance');
        }

        //休憩開始から休憩終了までの分数を計算
        $breakTime = Carbon::parse($activeBreak->break_in_time)->diffInMinutes($now);

        //休憩終了時刻と休憩時間を保存
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

        //今日の勤怠データを取得
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', $now->toDateString())
            ->first();

        //勤怠データがない場合は戻す
        if (! $attendance) {
            return redirect('/attendance');
        }

        //休憩中の場合は退勤できないようにする
        $activeBreak = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_out_time')
            ->first();

        if ($activeBreak) {
            return redirect('/attendance');
        }

        //すでに退勤済みの場合は二重登録しない
        if ($attendance->clock_out_time) {
            return redirect('/attendance');
        }

        //退勤時刻を保存
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
        //URLにmonthがあればその月、なければ今月を表示
        $currentMonth = $request->filled('month')
            ? Carbon::parse($request->month)
            : Carbon::now();

        //前月・翌月のリンク用
        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        //指定月の開始日・終了日を作成
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        //ログインユーザーの指定月の勤怠データを取得
        // 休憩データと修正申請データも一緒に取得
        $attendances = Attendance::with(['breaks', 'attendanceEdits.breakEdits'])
            ->where('user_id', auth()->id())
            ->whereBetween('date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->get()
            ->keyBy(function ($attendance) {
                return \Carbon\Carbon::parse($attendance->date)->toDateString();
            });

        //その月の全日付を作成
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
    * マイ勤怠レポート画面を表示
    */
    public function report()
    {
        // 過去6ヶ月の開始月と終了月を作成
        $endMonth = Carbon::now()->startOfMonth();
        $startMonth = $endMonth->copy()->subMonths(5);

        $startDate = $startMonth->copy()->startOfMonth();
        $endDate = $endMonth->copy()->endOfMonth();

        // ログインユーザーの過去6ヶ月分の勤怠を取得
        // 承認済み修正申請も一緒に取得する
        $attendances = Attendance::with(['breaks', 'attendanceEdits.breakEdits'])
            ->where('user_id', auth()->id())
            ->whereBetween('date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->get();

        $totalWorkMinutes = 0;
        $totalOvertimeMinutes = 0;
        $workingDays = 0;

        $lateCount = 0;
        $earlyLeaveCount = 0;
        $longWorkDayCount = 0;

        $monthlyReports = [];

        // 過去6ヶ月分の月別データを先に用意
        for ($month = $startMonth->copy(); $month->lte($endMonth); $month->addMonth()) {
            $monthlyReports[$month->format('Y-m')] = [
                'month' => $month->format('Y-m'),
                'work_minutes' => 0,
                'overtime_minutes' => 0,
            ];
        }

        foreach ($attendances as $attendance) {
            // 承認済みの修正申請があれば最新のものを優先する
            $approvedAttendanceEdit = $attendance->attendanceEdits
                ->where('status', '承認済み')
                ->sortByDesc('created_at')
                ->first();

            $clockInTime = $approvedAttendanceEdit
                ? $approvedAttendanceEdit->requested_clock_in_time
                : $attendance->clock_in_time;

            $clockOutTime = $approvedAttendanceEdit
                ? $approvedAttendanceEdit->requested_clock_out_time
                : $attendance->clock_out_time;

            if (! $clockInTime || ! $clockOutTime) {
                continue;
            }

            // 休憩時間を計算
            $breakMinutes = 0;

            if ($approvedAttendanceEdit) {
                foreach ($approvedAttendanceEdit->breakEdits as $breakEdit) {
                    if ($breakEdit->requested_break_in_time && $breakEdit->requested_break_out_time) {
                        $breakMinutes += Carbon::parse($breakEdit->requested_break_in_time)
                            ->diffInMinutes(Carbon::parse($breakEdit->requested_break_out_time));
                    }
                }
            } else {
                $breakMinutes = $attendance->breaks->sum('break_time');
            }

            // 勤務時間を計算
            $workMinutes = Carbon::parse($clockInTime)
                ->diffInMinutes(Carbon::parse($clockOutTime))
                - $breakMinutes;

            if ($workMinutes < 0) {
                $workMinutes = 0;
            }

            // 残業時間は8時間を超えた分
            $overtimeMinutes = max(0, $workMinutes - 480);

            $totalWorkMinutes += $workMinutes;
            $totalOvertimeMinutes += $overtimeMinutes;
            $workingDays++;

            $monthKey = Carbon::parse($attendance->date)->format('Y-m');

            if (isset($monthlyReports[$monthKey])) {
                $monthlyReports[$monthKey]['work_minutes'] += $workMinutes;
                $monthlyReports[$monthKey]['overtime_minutes'] += $overtimeMinutes;
            }

            // 今月分だけ異常検知する
            if (Carbon::parse($attendance->date)->isSameMonth(Carbon::now())) {
                if (Carbon::parse($clockInTime)->format('H:i') > '09:00') {
                    $lateCount++;
                }

                if (Carbon::parse($clockOutTime)->format('H:i') < '18:00') {
                    $earlyLeaveCount++;
                }

                if ($workMinutes > 600) {
                    $longWorkDayCount++;
                }
            }
        }

        $averageWorkMinutes = $workingDays > 0
            ? floor($totalWorkMinutes / $workingDays)
            : 0;

        return view('attendance.report', [
            'totalWorkMinutes' => $totalWorkMinutes,
            'totalOvertimeMinutes' => $totalOvertimeMinutes,
            'averageWorkMinutes' => $averageWorkMinutes,
            'monthlyReports' => $monthlyReports,
            'lateCount' => $lateCount,
            'earlyLeaveCount' => $earlyLeaveCount,
            'longWorkDayCount' => $longWorkDayCount,
        ]);
    }

    
    /**
     * 勤怠詳細画面を表示
     */
    public function detail(Attendance $attendance)
    {
        // 他人の勤怠詳細を見られないようにする
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        // ユーザーと休憩データを一緒に取得
        $attendance->load(['user', 'breaks']);

        // 承認待ちの修正申請と休憩修正申請を取得
        $pendingAttendanceEdit = AttendanceEdit::with('breakEdits')
            ->where('attendance_id', $attendance->id)
            ->where('user_id', auth()->id())
            ->where('status', '承認待ち')
            ->latest()
            ->first();

        // 承認済みの修正申請と休憩修正申請を取得
        $approvedAttendanceEdit = AttendanceEdit::with('breakEdits')
            ->where('attendance_id', $attendance->id)
            ->where('user_id', auth()->id())
            ->where('status', '承認済み')
            ->latest()
            ->first();

        return view('attendance.detail', [
            'attendance' => $attendance,
            'pendingAttendanceEdit' => $pendingAttendanceEdit,
            'approvedAttendanceEdit' => $approvedAttendanceEdit, // 追加
        ]);
    }
    /**
     * 勤怠修正申請を保存
    */
    public function storeCorrection(AttendanceCorrectionRequest $request, Attendance $attendance)
    {
        // 他人の勤怠に対して申請できないようにする
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validated();

        // 同じ勤怠に承認待ち申請がある場合は二重申請させない
        $pendingAttendanceEdit = AttendanceEdit::where('attendance_id', $attendance->id)
            ->where('user_id', auth()->id())
            ->where('status', '承認待ち')
            ->first();

        if ($pendingAttendanceEdit) {
            return redirect('/attendance/' . $attendance->id);
        }

        // 入力された時刻を、対象日の日時として保存する
        $date = Carbon::parse($attendance->date)->toDateString();

        // attendance_edits と break_edits をセットで保存する
        DB::transaction(function () use ($validated, $request, $attendance, $date) {
            // 勤怠修正申請を保存
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

            // 休憩修正申請を保存
            $breakIds = $request->input('break_id', []);
            $breakInTimes = $request->input('break_in_time', []);
            $breakOutTimes = $request->input('break_out_time', []);

            foreach ($breakInTimes as $index => $breakInTime) {
                $breakOutTime = $breakOutTimes[$index] ?? null;
                $breakId = $breakIds[$index] ?? null;

                // 休憩開始・終了がどちらも空なら保存しない
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

        return redirect('/attendance/' . $attendance->id);
    }
}