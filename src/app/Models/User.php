<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail // 修正: メール認証を有効化
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',

        // 修正: 管理者判定用
        'admin',

        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        // 修正: adminをtrue/falseで扱う
        'admin' => 'boolean',

        'email_verified_at' => 'datetime',
    ];

    // 修正: ユーザーは複数の勤怠を持つ
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // 修正: ユーザーは複数の修正申請を持つ
    public function attendanceEdits()
    {
        return $this->hasMany(AttendanceEdit::class);
    }
}