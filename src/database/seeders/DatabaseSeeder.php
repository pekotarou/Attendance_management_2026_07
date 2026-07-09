<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seederを実行
    */
    public function run(): void
    {
        //初期ユーザー作成
        $this->call([
            UserSeeder::class,
        ]);
    }
}