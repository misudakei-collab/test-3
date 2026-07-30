<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Attendance;

// 【正常系】勤怠レコード一覧の取得（200 OK）
Route::get('/attendance-records', function () {
    return response()->json(Attendance::all(), 200, [], JSON_UNESCAPED_UNICODE);
});

// 【エラー系】存在しないIDに対して要件通りの「424 Failed Dependency」を返すロジック
Route::get('/attendance-records/{id}', function ($id) {
    $attendance = Attendance::find($id);
    
    if (!$attendance) {
        return response()->json([
            'error' => 'Failed Dependency',
            'message' => '指定された勤怠レコードが存在しないため、処理に失敗しました。'
        ], 424, [], JSON_UNESCAPED_UNICODE);
    }
    
    return response()->json($attendance, 200, [], JSON_UNESCAPED_UNICODE);
});
