<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * attendancesテーブルを作成
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            //usersテーブルと紐づける
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            //勤怠日
            $table->date('date');

            //出勤時刻
            $table->dateTime('clock_in_time')->nullable();

            //退勤時刻
            $table->dateTime('clock_out_time')->nullable();

            //備考・修正申請理由として使う
            $table->text('note')->nullable();

            $table->timestamps();

            //同じユーザーが同じ日に2件登録できないようにする
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * attendancesテーブルを削除
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}