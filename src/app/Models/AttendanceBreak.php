<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceBreak extends Model
{
    use HasFactory;

    //モデル名とテーブル名が一致しないため指定
    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id',
        'break_in_time',
        'break_out_time',
        'break_time',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}