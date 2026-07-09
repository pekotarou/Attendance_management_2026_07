<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Resources\AttendanceRecordResource;
use Carbon\Carbon;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;

class AttendanceRecordController extends Controller
{
    /**
     * 勤怠一覧取得 API
     */
    public function index(Request $request)
    {
        // 勤怠データを取得する基本クエリ
        $query = Attendance::with(['user', 'breaks', 'attendanceEdits.breakEdits']);

        // user_idで絞り込み
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // dateで絞り込み
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // monthで絞り込み
        if ($request->filled('month')) {
            $month = Carbon::parse($request->month);

            $query->whereYear('date', $month->year)
                ->whereMonth('date', $month->month);
        }

        // ページ数。未指定なら20件、最大100件
        $perPage = min($request->input('per_page', 20), 100);

        $attendances = $query
            ->orderBy('date', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => AttendanceRecordResource::collection($attendances)->resolve(),
            'meta' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ],
        ]);
    }

    /**
     * 勤怠詳細取得 API
     */
    public function show(Attendance $attendanceRecord)
    {
        // 関連データを読み込む
        $attendanceRecord->load([
            'user',
            'breaks',
            'attendanceEdits.breakEdits',
        ]);

        return response()->json([
            'data' => new AttendanceRecordResource($attendanceRecord),
        ]);
    }

    /**
     * 勤怠登録 API
     */
    public function store(StoreAttendanceRecordRequest $request)
    {
        $validated = $request->validated();

        // 同じユーザー・同じ日付の勤怠がある場合は422で返す
        $exists = Attendance::where('user_id', $validated['user_id'])
            ->where('date', $validated['date'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'この日付の勤怠は既に登録されています。',
                'errors' => [
                    'date' => ['この日付の勤怠は既に登録されています。'],
                ],
            ], 422);
        }

        // 勤怠データを登録
        $attendance = Attendance::create([
            'user_id' => $validated['user_id'],
            'date' => $validated['date'],
            'clock_in_time' => $validated['date'] . ' ' . $validated['clock_in'],
            'clock_out_time' => isset($validated['clock_out'])
                ? $validated['date'] . ' ' . $validated['clock_out']
                : null,
            'note' => $validated['comment'] ?? null,
        ]);

        $attendance->load([
            'user',
            'breaks',
            'attendanceEdits.breakEdits',
        ]);

        return response()->json([
            'data' => new AttendanceRecordResource($attendance),
        ], 201);
    }

   /**
     * 勤怠更新 API
     */
    public function update(UpdateAttendanceRecordRequest $request, Attendance $attendanceRecord)
    {
         // 本人または管理者のみ更新可能
        $this->authorize('update', $attendanceRecord);
        $validated = $request->validated();

        // 勤怠データを更新
        $attendanceRecord->update([
            'date' => $validated['date'],
            'clock_in_time' => $validated['date'] . ' ' . $validated['clock_in'],
            'clock_out_time' => isset($validated['clock_out'])
                ? $validated['date'] . ' ' . $validated['clock_out']
                : null,
            'note' => $validated['comment'] ?? null,
        ]);

        $attendanceRecord->load([
            'user',
            'breaks',
            'attendanceEdits.breakEdits',
        ]);

        return response()->json([
            'data' => new AttendanceRecordResource($attendanceRecord),
        ]);
    }

    /**
     * 勤怠削除 API
     */
    public function destroy(Attendance $attendanceRecord)
    {
        // 本人または管理者のみ削除可能
        $this->authorize('delete', $attendanceRecord);
        // 勤怠データを削除
        $attendanceRecord->delete();

        // 削除成功時は204を返す
        return response()->json(null, 204);
    }

}