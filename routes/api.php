<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Attendance;
use Illuminate\Support\Facades\Validator;

// =========================================================================
// 🔓 【Sanctum未認証時・共通制限】 認証なしアクセス時の401エラー（指示書要件）
// =========================================================================
Route::fallback(function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');

// =========================================================================
// 🌐 【公開API v1 グループ】 指示書通りの /api/v1/attendance-records 構築
// =========================================================================
Route::prefix('v1')->group(function () {

    // -------------------------------------------------------------
    // ★公開API 読み取り系（一覧取得）: GET /api/v1/attendance-records
    // -------------------------------------------------------------
    Route::get('/attendance-records', function () {
        // 指示書要件：「data配列」と「meta情報（current_page, last_page等）」を含めたページネーション形式
        $paginated = Attendance::with('breakTimes')->paginate(10);
        
        return response()->json([
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    });

    // -------------------------------------------------------------
    // ★公開API 読み取り系（詳細取得）: GET /api/v1/attendance-records/{id}
    // -------------------------------------------------------------
    Route::get('/attendance-records/{attendanceRecord}', function ($id) {
        $attendance = Attendance::with('breakTimes')->find($id);
        
        // 指示書要件：存在しないIDでは 404 とエラー JSON が返る
        if (!$attendance) {
            return response()->json(['error' => '勤怠情報が見つかりませんでした。'], 404, [], JSON_UNESCAPED_UNICODE);
        }
        
        return response()->json(['data' => $attendance], 200, [], JSON_UNESCAPED_UNICODE);
    });

    // =========================================================================
    // 🔒 【Sanctum 認証ガードエリア】（POST, PUT, DELETE は認証ユーザーのみ）
    // =========================================================================
    Route::middleware('auth:sanctum')->group(function () {

        // -------------------------------------------------------------
        // ★公開API 書き込み系（新規作成）: POST /api/v1/attendance-records
        // -------------------------------------------------------------
        Route::post('/attendance-records', function (Request $request) {
            // 指示書要件：バリデーションエラー時に 422 と日本語エラーメッセージ
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
                'clock_in' => 'required',
            ], [
                'date.required' => '日付は必須項目です。',
                'clock_in.required' => '出勤時間は必須項目です。',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422, [], JSON_UNESCAPED_UNICODE);
            }

            // レコード作成
            $attendance = Attendance::create([
                'user_id' => auth()->id(),
                'date' => $request->date,
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
            ]);

            // 指示書要件：HTTP 201 が返り、テーブルにレコードが作成される
            return response()->json($attendance, 201);
        });

        // -------------------------------------------------------------
        // ★公開API 書き込み系（データ更新）: PUT /api/v1/attendance-records/{id}
        // -------------------------------------------------------------
        Route::put('/attendance-records/{attendanceRecord}', function (Request $request, $id) {
            $attendance = Attendance::find($id);

            // 指示書要件：存在しないIDに対しては HTTP 404 を返す
            if (!$attendance) {
                return response()->json(['error' => '勤怠情報が見つかりませんでした。'], 404, [], JSON_UNESCAPED_UNICODE);
            }

            // 指示書要件（Sanctum他ユーザー制限）：他ユーザーの勤怠を更新しようとすると 403 が返る
            if ($attendance->user_id !== auth()->id()) {
                return response()->json(['error' => 'この操作を実行する権限がありません。'], 403, [], JSON_UNESCAPED_UNICODE);
            }

            $attendance->update($request->only(['clock_in', 'clock_out']));

            // 指示書要件：HTTP 200 が返り、レコードが更新されている
            return response()->json(['message' => 'レコードが更新されました。'], 200, [], JSON_UNESCAPED_UNICODE);
        });

        // -------------------------------------------------------------
        // ★公開API 書き込み系（データ削除）: DELETE /api/v1/attendance-records/{id}
        // -------------------------------------------------------------
        Route::delete('/attendance-records/{attendanceRecord}', function ($id) {
            $attendance = Attendance::find($id);

            // 指示書要件：存在しないIDに対しては HTTP 404 を返す
            if (!$attendance) {
                return response()->json(['error' => '勤怠情報が見つかりませんでした。'], 404, [], JSON_UNESCAPED_UNICODE);
            }

            // 指示書要件（Sanctum他ユーザー制限）：他ユーザーの勤怠を削除しようとすると 403 が返る
            if ($attendance->user_id !== auth()->id()) {
                return response()->json(['error' => 'この操作を実行する権限がありません。'], 403, [], JSON_UNESCAPED_UNICODE);
            }

            $attendance->delete();

            // 指示書要件：HTTP 204 が返り、レコードが削除されている
            return response(null, 204);
        });

    });
});
