<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceEdit extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'requested_clock_in_time',
        'requested_clock_out_time',
        'note',
        'status',
    ];

    protected $casts = [
        'requested_clock_in_time' => 'datetime',
        'requested_clock_out_time' => 'datetime',
    ];

    //修正申請は元の勤怠に属する
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    //修正申請は申請したユーザーに属する
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //修正申請は複数の休憩修正を持つ
    public function breakEdits()
    {
        return $this->hasMany(BreakEdit::class);
    }
}