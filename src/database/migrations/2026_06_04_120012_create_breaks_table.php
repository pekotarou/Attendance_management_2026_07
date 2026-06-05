<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * breaksテーブルを作成
     */
    public function up(): void
    {
        Schema::create('breaks', function (Blueprint $table) {
            $table->id();

            //attendancesテーブルと紐づける
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();

            //休憩開始時刻
            $table->dateTime('break_in_time')->nullable();

            //休憩終了時刻
            $table->dateTime('break_out_time')->nullable();

            //休憩時間（分単位で保存する想定）
            $table->integer('break_time')->nullable();

            $table->timestamps();
        });
    }

    /**
     * breaksテーブルを削除
     */
    public function down(): void
    {
        Schema::dropIfExists('breaks');
    }
};