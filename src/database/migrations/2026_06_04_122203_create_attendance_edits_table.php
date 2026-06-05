<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * attendance_editsテーブルを作成
     */
    public function up(): void
    {
        Schema::create('attendance_edits', function (Blueprint $table) {
            $table->id();

            //元の勤怠データと紐づける
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();

            //申請したユーザーと紐づける
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            //申請後の出勤時刻
            $table->dateTime('requested_clock_in_time')->nullable();

            //申請後の退勤時刻
            $table->dateTime('requested_clock_out_time')->nullable();

            //出退勤・休憩を含む修正申請理由
            $table->text('note')->nullable();

            //pending=承認待ち / approved=承認済み
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    /**
     * attendance_editsテーブルを削除
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_edits');
    }
};