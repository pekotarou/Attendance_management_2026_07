<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    // 修正: モデル名はBreakTimeだが、テーブル名はbreaksを使う
    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id',
        'break_in_time',
        'break_out_time',
        'break_time',
    ];

    protected $casts = [
        'break_in_time' => 'datetime',
        'break_out_time' => 'datetime',
    ];

    // 修正: 休憩は1つの勤怠に属する
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // 修正: 元の休憩に対する修正申請を持つ
    public function breakEdits()
    {
        return $this->hasMany(BreakEdit::class, 'break_id');
    }
}