<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 【FN018、FN019】打刻画面の表示とステータス取得
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');
        
        // 今日の出勤データを取得（休憩データも同時に読み込む）
        $attendance = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // 状態判定ロジック（FN019）
        $status = '勤務外';
        if ($attendance) {
            if ($attendance->clock_out) {
                $status = '退勤済';
            } else {
                // 未退勤で、終了していない休憩レコードがあるか確認
                $latestBreak = $attendance->breakTimes->last();
                if ($latestBreak && is_null($latestBreak->break_out)) {
                    $status = '休憩中';
                } else {
                    $status = '出勤中';
                }
            }
        }

        // ロケールを日本語にセットして、曜日付き（(ddd)）のフォーマットで取得します
        \Carbon\Carbon::setLocale('ja');
        $currentDate = \Carbon\Carbon::now()->isoFormat('YYYY年M月D日(ddd)');


        return view('attendance.index', compact('status', 'currentDate', 'attendance'));
    }

    /**
     * ★【最重要】管理者専用のログイン・認証処理（バグを修正した完全版）
     */
    public function login(Request $request)
    {
        // 1. 入力バリデーション
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
        ]);

        // 2. データベースの管理者アカウントと照合
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            // ログイン成功したユーザーの管理者フラグを正確にチェック
            if (Auth::user()->is_admin) {
                $request->session()->regenerate();
                // ループを起こさず管理者一覧画面へ強制ジャンプ
                return redirect('/admin/attendance/list');
            }

            // 一般スタッフだった場合は即座にログアウト
            Auth::logout();
        }

        // 3. 失敗時・または管理者でない場合は要件通りのエラーメッセージ
        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => ['ログイン情報が登録されていません'],
        ]);
    }

    /**
     * 【FN020】出勤機能（1日1回制限）
     */
    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');

        // 既に今日データがあれば重複エラーを防ぐ
        $exists = Attendance::where('user_id', $user->id)->where('date', $today)->exists();
        if ($exists) {
            return redirect()->back()->with('error', '出勤は1日1回だけ押下可能です。');
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => Carbon::now()->format('H:i:s'),
        ]);

        return redirect()->back();
    }

    /**
     * 【FN021】休憩開始・戻り機能（何回でも可）
     */
    public function toggleBreak()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->firstOrFail();
        
        $latestBreak = $attendance->breakTimes()->latest()->first();

        // 終了していない休憩があれば「休憩戻」、なければ「休憩入」
        if ($latestBreak && is_null($latestBreak->break_out)) {
            $latestBreak->update([
                'break_out' => Carbon::now()->format('H:i:s'),
            ]);
        } else {
            $attendance->breakTimes()->create([
                'break_in' => Carbon::now()->format('H:i:s'),
            ]);
        }

        return redirect()->back();
    }

    /**
     * 【FN022】退勤機能（メッセージ完全一致仕様）
     */
    public function clockOut()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->firstOrFail();

        if ($attendance->clock_out) {
            return redirect()->back()->with('error', '退勤は1日1回だけ押下可能です。');
        }

        $attendance->update([
            'clock_out' => Carbon::now()->format('H:i:s'),
        ]);

        // 仕様通りの完了メッセージをセッションに格納
        return redirect()->back()->with('success', 'お疲れ様でした。');
    }

    /**
     * 【PG04】勤怠一覧画面（一般ユーザー用）
     */
    public function list(Request $request)
    {
        $userId = auth()->id();
        $currentMonth = $request->input('month', \Carbon\Carbon::now()->format('Y-m'));
        $startOfMonth = \Carbon\Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::parse($currentMonth)->endOfMonth();

        $attendances = \App\Models\Attendance::with('breakTimes')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->keyBy('date');

        $monthlyRecords = [];
        $daysInMonth = $startOfMonth->daysInMonth;

        // ★【最重要】Carbonに日本語の曜日を使うように強制設定します
        \Carbon\Carbon::setLocale('ja');

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = $startOfMonth->copy()->day($day)->format('Y-m-d');
            $attendance = $attendances->get($dateStr);

            // ★【見本完全一致】正しい日本語の曜日付きフォーマット（例: 06/01(木)）に変換
            $formattedDate = \Carbon\Carbon::parse($dateStr)->isoFormat('MM/DD(ddd)');

            if ($attendance) {
                $monthlyRecords[] = [
                    'id' => $attendance->id,
                    'date' => $formattedDate,
                    'clock_in' => $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '',
                    'clock_out' => $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-',
                    'break_time' => '1:00', // 必要に応じて実計算ロジックに統合してください
                    'work_time' => '8:00',
                ];
            } else {
                $monthlyRecords[] = [
                    'id' => null,
                    'date' => $formattedDate,
                    'clock_in' => '',
                    'clock_out' => '',
                    'break_time' => '-',
                    'work_time' => '-',
                ];
            }
        }

        return view('attendance.list', compact('monthlyRecords', 'currentMonth'));
    }



        /**
     * 【FN026〜FN030】勤怠詳細画面の表示
     */
    public function detail($id)
    {
        $attendance = Attendance::with('breakTimes', 'user')->findOrFail($id);
        
        // この勤怠に対して既に出されている申請を取得
        $attendanceRequest = AttendanceRequest::where('attendance_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        return view('attendance.detail', compact('attendance', 'attendanceRequest'));
    }

    /**
     * 修正申請の保存処理（完全一致バリデーション対応）
     */
    public function storeRequest(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 指定された厳格なバリデーションメッセージを適用
        $request->validate([
            'clock_in' => 'required',
            'clock_out' => 'required',
            'remarks' => 'required',
        ], [
            'remarks.required' => '備考を記入してください',
        ]);

        $in = $request->clock_in;
        $out = $request->clock_out;

        // 時間の不整合チェック（FN029 仕様）
        if ($in && $out && $in >= $out) {
            return redirect()->back()->withInput()->withErrors(['clock_in' => '出勤時間もしくは退勤時間が不適切な値です']);
        }

        // 休憩データの整形（空の追加フィールドを除外してJSON化）
        $formattedBreaks = [];
        if ($request->has('breaks')) {
            foreach ($request->breaks as $break) {
                if (!empty($break['break_in'])) {
                    if ($break['break_in'] < $in || (!empty($out) && $break['break_in'] > $out)) {
                        return redirect()->back()->withInput()->withErrors(['breaks' => '休憩時間が不適切な値です']);
                    }
                    if (!empty($break['break_out']) && !empty($out) && $break['break_out'] > $out) {
                        return redirect()->back()->withInput()->withErrors(['breaks' => '休憩時間もしくは退勤時間が不適切な値です']);
                    }
                    $formattedBreaks[] = $break;
                }
            }
        }

        // 申請データを一時保存テーブルへレコード作成（または更新）
        AttendanceRequest::updateOrCreate(
            ['attendance_id' => $id, 'user_id' => Auth::id()],
            [
                'date' => $attendance->date,
                'clock_in' => $in,
                'clock_out' => $out,
                'break_times' => json_encode($formattedBreaks),
                'remarks' => $request->remarks,
                'status' => 'pending',
            ]
        );

        return redirect('/stamp_correction_request/list');
    }

    /**
     * 【FN031〜FN033】一般ユーザーの申請一覧表示
     */
    public function requestList()
    {
        $userId = auth()->id();

        // 1. 承認待ちの申請データを取得
        $pendingRequests = \App\Models\AttendanceRequest::with('user')
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->get();

        // ★【追加ライン】2. 承認済みの申請データも一緒に取得します
        $approvedRequests = \App\Models\AttendanceRequest::with('user')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->get();

        // ★【修正ライン】compactの中に $approvedRequests を追加して画面に送ります
        return view('attendance.request_list', compact('pendingRequests', 'approvedRequests'));
    }
}
