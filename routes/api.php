<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AttendanceRecordController;

Route::prefix('v1')->group(function () {
    
    // 【FN057仕様】URLはケバブケース、動的セグメント変数を明示的に attendanceRecord にバインド
    Route::apiResource('attendance-records', AttendanceRecordController::class)
        ->parameters(['attendance-records' => 'attendanceRecord']);
        
});
