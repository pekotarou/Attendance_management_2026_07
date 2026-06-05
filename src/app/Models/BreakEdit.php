<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakEdit extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_edit_id',
        'break_id',
        'requested_break_in_time',
        'requested_break_out_time',
    ];

    protected $casts = [
        'requested_break_in_time' => 'datetime',
        'requested_break_out_time' => 'datetime',
    ];

    // 修正: 休憩修正は1つの修正申請に属する
    public function attendanceEdit()
    {
        return $this->belongsTo(AttendanceEdit::class);
    }

    // 修正: 休憩修正は元の休憩データに属する
    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class, 'break_id');
    }
}