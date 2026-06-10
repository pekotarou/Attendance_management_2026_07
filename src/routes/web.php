<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;

// 修正: トップページ確認用
Route::get('/', function () {
    return redirect('/login');
});

// 修正: 勤怠登録画面
Route::get('/attendance', [AttendanceController::class, 'index'])
    ->middleware(['auth', 'verified']);

// 修正: 出勤処理
Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
    ->middleware(['auth', 'verified']);


// 修正: 休憩入処理
Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])
    ->middleware(['auth', 'verified']);

// 修正: 休憩戻処理
Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])
    ->middleware(['auth', 'verified']);

// 修正: 退勤処理
Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
    ->middleware(['auth', 'verified']);

// 修正: 勤怠一覧画面
Route::get('/attendance/list', [AttendanceController::class, 'list'])
    ->middleware(['auth', 'verified']);

// 修正: 勤怠詳細画面
Route::get('/attendance/{attendance}', [AttendanceController::class, 'detail'])
    ->middleware(['auth', 'verified']);

// 修正: 勤怠修正申請処理
Route::post('/attendance/{attendance}/correction', [AttendanceController::class, 'storeCorrection'])
    ->middleware(['auth', 'verified']);

// 修正: 申請一覧画面
Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])
    ->middleware(['auth', 'verified']);