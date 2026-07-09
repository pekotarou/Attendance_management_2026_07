<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendanceRecordPolicy
{
    /**
     * 勤怠更新の権限
     */
    public function update(User $user, Attendance $attendance)
    {
        // 管理者、または本人の勤怠のみ更新可能
        return $user->admin || $attendance->user_id === $user->id;
    }

    /**
     * 勤怠削除の権限
     */
    public function delete(User $user, Attendance $attendance)
    {
        // 管理者、または本人の勤怠のみ削除可能
        return $user->admin || $attendance->user_id === $user->id;
    }
}