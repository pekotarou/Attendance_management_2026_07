<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AttendanceRecordController;
use App\Http\Controllers\Api\V1\ApiTokenController;

Route::prefix('v1')->group(function () {
    // 修正: APIトークン発行
    Route::post('/tokens', [ApiTokenController::class, 'store']);

    // 修正: 勤怠データ取得は公開APIとして認証なし
    Route::get('/attendance-records', [AttendanceRecordController::class, 'index']);
    Route::get('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'show']);

    // 修正: 勤怠データ操作はAPIトークン必須
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/attendance-records', [AttendanceRecordController::class, 'store']);
        Route::put('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'update']);
        Route::delete('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'destroy']);
    });
});