<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeBreakIdNullableOnBreakEditsTable extends Migration
{
    public function up()
    {
        // 修正: 新規休憩追加申請では break_id が存在しないため NULL許可に変更
        DB::statement('ALTER TABLE break_edits MODIFY break_id BIGINT UNSIGNED NULL');
    }

    public function down()
    {
        // 修正: ロールバック時に NOT NULL に戻す
        DB::statement('ALTER TABLE break_edits MODIFY break_id BIGINT UNSIGNED NOT NULL');
    }
}