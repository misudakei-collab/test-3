<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceReportController;

// 未ログイン時はログイン画面にリダイレクト
Route::get('/', function () {
    return redirect('/login');
});

// ★管理者のログイン画面を表示するためのルート（未ログインでもアクセス可能）
Route::get('/admin/login', function () {
    return view('auth.admin-login');
});

// ★【最重要】管理者ログインのデータを、Fortifyではなく今作った専用のログイン処理へ配線します
Route::post('/admin/login', [\App\Http\Controllers\Admin\AttendanceController::class, 'login']);


// 認証ルート（ログイン済みユーザーのみアクセス可）
Route::middleware(['auth'])->group(function () {
    
    // PG03: 勤怠登録画面
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::post('/attendance/break', [AttendanceController::class, 'toggleBreak']);
    
    // PG04: 勤怠一覧画面
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    
    // PG05: 勤怠詳細・修正申請
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'storeRequest']);

    // PG06 兼 PG12: 申請一覧画面（一般ユーザーと管理者の共通パス自動識別）
    Route::get('/stamp_correction_request/list', function () {
        if (auth()->user()->is_admin) {
            return app(\App\Http\Controllers\Admin\AttendanceController::class)->requestList();
        }
        return app(AttendanceController::class)->requestList();
    })->name('stamp_correction_request.list');

    // PG14: マイ勤怠レポート画面
    Route::get('/attendance/report', [AttendanceReportController::class, 'index'])->name('attendance.report');
});

// 管理者専用 認証ルート（先ほど作った admin ミドルウェアで厳格にガード）
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // PG08: 日次勤怠一覧
    Route::get('/attendance/list', [\App\Http\Controllers\Admin\AttendanceController::class, 'list'])->name('attendance.list');
    
    // PG09: 勤怠詳細
    Route::get('/attendance/{id}', [\App\Http\Controllers\Admin\AttendanceController::class, 'detail'])->name('attendance.detail');
    
    // PG10: スタッフ一覧画面
    Route::get('/staff/list', [\App\Http\Controllers\Admin\AttendanceController::class, 'staffList'])->name('staff.list');
    
    // PG11: スタッフ別月次勤怠一覧 ＆ ★CSV出力
    Route::get('/attendance/staff/{id}', [\App\Http\Controllers\Admin\AttendanceController::class, 'staffAttendance'])->name('staff.attendance');
    Route::get('/attendance/staff/{id}/csv', [\App\Http\Controllers\Admin\AttendanceController::class, 'exportCsv']);
    
    // PG13: 修正申請承認画面 ＆ アクション
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [\App\Http\Controllers\Admin\AttendanceController::class, 'approveView'])->name('clearance.approve');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [\App\Http\Controllers\Admin\AttendanceController::class, 'approveAction']);

    Route::post('/attendance/{id}', [\App\Http\Controllers\Admin\AttendanceController::class, 'updateDirect']);
});
