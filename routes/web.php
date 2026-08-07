<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;

// =========================================================================
// 🔓 【制限なし】トップページ & リダイレクト設定
// =========================================================================
Route::get('/', function () {
    return redirect('/login');
});

// =========================================================================
// 👤 【一般スタッフ専用ルート】 (auth およびメール認証制限グループ)
// =========================================================================
Route::middleware(['auth', 'verified'])->group(function () {
    
    // 【PG03】打刻用ホーム画面 (GET)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    
    // ✨【打刻アクション用POSTルート：重複や競合を完全に防ぐため、ここに一本化！】
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock_in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock_out');
    Route::post('/attendance/break', [AttendanceController::class, 'breakToggle'])->name('attendance.break_toggle');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.break_in');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.break_out');
    
    // 【PG04】自身の月次勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    
    // 【PG05】勤怠詳細 & 修正申請の送信
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'updateDetail']);
    
    // 【PG06】自身が提出した申請一覧
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->name('attendance.request_list');
    
    // 【PG14】マイ勤怠レポート画面
    Route::get('/attendance/report', [AttendanceReportController::class, 'index'])->name('attendance.report');
});

// =========================================================================
// 👑 【管理者専用ルート】 (URLの先頭に /admin を強制固定・完全に分離)
// =========================================================================
Route::prefix('admin')->group(function () {
    
    // 【PG07】管理者専用ログイン画面
    Route::get('/login', [AdminAttendanceController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAttendanceController::class, 'login']);

    // 🔒 管理者専用ログイン認証ガード
    Route::middleware(['auth'])->group(function () {
        
        // 【PG08】当日の全スタッフ勤怠一覧
        Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])->name('admin.attendance.list');
        
        // 【PG09】管理者によるスタッフ勤怠データの直接修正
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail'])->name('admin.detail');
        Route::post('/attendance/{id}', [AdminAttendanceController::class, 'updateDetail']);
        
        // 【PG10】登録されている全一般スタッフのリスト閲覧
        Route::get('/staff/list', [AdminAttendanceController::class, 'staffList'])->name('admin.staff.list');
        
        // 【PG11】選択したスタッフの月次一覧閲覧 & CSV出力
        Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staffAttendance'])->name('admin.staff.attendance');
        Route::post('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportCsv'])->name('admin.staff.csv');
        
        // 【PG12】全スタッフから提出された承認待ち申請の一覧
        Route::get('/stamp_correction_request/list', [AdminAttendanceController::class, 'requestList'])->name('admin.request_list');
        
        // 【PG13】申請内容の確認 ＆ 承認アクション
        Route::get('/stamp_correction_request/approve/{id}', [AdminAttendanceController::class, 'approveView'])->name('admin.approve.view');
        Route::post('/stamp_correction_request/approve/{id}', [AdminAttendanceController::class, 'approveAction'])->name('admin.approve.action');
    });
});
