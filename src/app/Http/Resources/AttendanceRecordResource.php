<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    /**
     * 勤怠データをAPI用JSONに変換
     */
    public function toArray($request)
    {
        // 承認済みの修正申請があれば最新のものを優先する
        $approvedAttendanceEdit = $this->attendanceEdits
            ->where('status', '承認済み')
            ->sortByDesc('created_at')
            ->first();

        $clockInTime = $approvedAttendanceEdit
            ? $approvedAttendanceEdit->requested_clock_in_time
            : $this->clock_in_time;

        $clockOutTime = $approvedAttendanceEdit
            ? $approvedAttendanceEdit->requested_clock_out_time
            : $this->clock_out_time;

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
            $breakMinutes = $this->breaks->sum('break_time');
        }

        $workMinutes = null;

        if ($clockInTime && $clockOutTime) {
            $workMinutes = Carbon::parse($clockInTime)
                ->diffInMinutes(Carbon::parse($clockOutTime))
                - $breakMinutes;
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user ? $this->user->name : null,
            'date' => Carbon::parse($this->date)->format('Y-m-d'),
            'clock_in' => $clockInTime ? Carbon::parse($clockInTime)->format('H:i:s') : null,
            'clock_out' => $clockOutTime ? Carbon::parse($clockOutTime)->format('H:i:s') : null,
            'break_minutes' => $breakMinutes,
            'work_minutes' => $workMinutes,
            'note' => $approvedAttendanceEdit ? $approvedAttendanceEdit->note : $this->note,
            'is_corrected' => $approvedAttendanceEdit ? true : false,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}