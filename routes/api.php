<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceApiController;

Route::fallback(function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');

Route::prefix('v1')->group(function () {

    // 勤怠情報の取得
    Route::get('/attendance-records', [AttendanceApiController::class, 'index']);
    Route::get('/attendance-records/{attendanceRecord}', [AttendanceApiController::class, 'show']);

    // 勤怠情報の登録・更新・削除
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/attendance-records', [AttendanceApiController::class, 'store']);
        Route::put('/attendance-records/{attendanceRecord}', [AttendanceApiController::class, 'update']);
        Route::delete('/attendance-records/{attendanceRecord}', [AttendanceApiController::class, 'destroy']);
    });

});
