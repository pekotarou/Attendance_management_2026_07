<?php

use Illuminate\Support\Facades\Route;

// 修正: トップページ確認用
Route::get('/', function () {
    return redirect('/login');
});

// 修正: 一時確認用。後でAttendanceControllerに置き換える
Route::get('/attendance', function () {
    return view('attendance.index');
})->middleware('auth');