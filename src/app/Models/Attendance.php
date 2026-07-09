<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttendanceBreak;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in_time',
        'clock_out_time',
        'note',
    ];

    //日付・日時をCarbonで扱えるようにする
    protected $casts = [
        'date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
    ];

    //勤怠は1人のユーザーに属する
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //勤怠は複数の休憩を持つ
    public function breaks()
{
    //breaksテーブル用のモデルに合わせる
    return $this->hasMany(AttendanceBreak::class);
}

    //勤怠は複数の修正申請を持つ
    public function attendanceEdits()
    {
        return $this->hasMany(AttendanceEdit::class);
    }
    
}