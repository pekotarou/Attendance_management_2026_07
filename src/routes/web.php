<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminCorrectionRequestController;

//トップページ確認用
Route::get('/', function () {
    return redirect('/login');
});

//スタッフ側画面
//勤怠登録画面
Route::get('/attendance', [AttendanceController::class, 'index'])
    ->middleware(['auth', 'verified']);

//出勤処理
Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
    ->middleware(['auth', 'verified']);


//休憩入処理
Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])
    ->middleware(['auth', 'verified']);

//休憩戻処理
Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])
    ->middleware(['auth', 'verified']);

//退勤処理
Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
    ->middleware(['auth', 'verified']);

//勤怠一覧画面
Route::get('/attendance/list', [AttendanceController::class, 'list'])
    ->middleware(['auth', 'verified']);

// マイ勤怠レポート画面
Route::get('/attendance/report', [AttendanceController::class, 'report'])
    ->middleware(['auth', 'verified']);

//勤怠詳細画面
Route::get('/attendance/{attendance}', [AttendanceController::class, 'detail'])
    ->middleware(['auth', 'verified']);

//勤怠修正申請処理
Route::post('/attendance/{attendance}/correction', [AttendanceController::class, 'storeCorrection'])
    ->middleware(['auth', 'verified']);

//申請一覧画面
Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])
    ->middleware(['auth', 'verified']);



//管理者側画面
// 管理者ログイン画面
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm']);

// 管理者ログイン処理
Route::post('/admin/login', [AdminLoginController::class, 'login']);

// 管理者勤怠一覧画面
Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
    ->middleware('auth');

// 管理者スタッフ一覧画面
Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])
    ->middleware('auth');


// 管理者スタッフ別月次勤怠画面
Route::get('/admin/attendance/staff/{user}', [AdminStaffController::class, 'attendance'])
    ->middleware('auth');

// 管理者勤怠修正処理
Route::post('/admin/attendance/{attendance}/correction', [AdminAttendanceController::class, 'update'])
    ->middleware('auth');

// 管理者勤怠詳細画面
Route::get('/admin/attendance/{attendance}', [AdminAttendanceController::class, 'detail'])
    ->middleware('auth');

// 管理者申請一覧画面
Route::get('/admin/stamp_correction_request/list', [AdminCorrectionRequestController::class, 'index'])
    ->middleware('auth');

// 管理者申請承認画面
Route::get('/admin/stamp_correction_request/approve/{attendanceEdit}', [AdminCorrectionRequestController::class, 'show'])
    ->middleware('auth');

// 管理者申請承認処理
Route::post('/admin/stamp_correction_request/approve/{attendanceEdit}', [AdminCorrectionRequestController::class, 'approve'])
    ->middleware('auth');
