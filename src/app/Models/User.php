<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable implements MustVerifyEmail //メール認証を有効化
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',

        //管理者判定用
        'admin',

        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        //adminをtrue/falseで扱う
        'admin' => 'boolean',

        'email_verified_at' => 'datetime',
    ];

    //ユーザーは複数の勤怠を持つ
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    //ユーザーは複数の修正申請を持つ
    public function attendanceEdits()
    {
        return $this->hasMany(AttendanceEdit::class);
    }
}