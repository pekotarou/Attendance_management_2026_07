<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * break_editsテーブルを作成
     */
    public function up(): void
    {
        Schema::create('break_edits', function (Blueprint $table) {
            $table->id();

            //どの修正申請に含まれる休憩修正か
            $table->foreignId('attendance_edit_id')->constrained()->cascadeOnDelete();

            //元の休憩データと紐づける
            $table->foreignId('break_id')->constrained()->cascadeOnDelete();

            //申請後の休憩開始時刻
            $table->dateTime('requested_break_in_time')->nullable();

            //申請後の休憩終了時刻
            $table->dateTime('requested_break_out_time')->nullable();

            $table->timestamps();
        });
    }

    /**
     * break_editsテーブルを削除
     */
    public function down(): void
    {
        Schema::dropIfExists('break_edits');
    }
};