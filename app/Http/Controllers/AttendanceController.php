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

        // 現在の日付（UI表示用）
        Carbon::setLocale('ja');
        $currentDate = Carbon::now()->isoFormat('YYYY年MM月DD日(ddd)');

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
     * 【FN023〜FN025】勤怠一覧画面の表示（月切り替え対応）
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        
        // クエリパラメータから対象月を取得（なければ当月）
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        // 対象月の勤怠データを取得
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->keyBy('date'); // 日付をキーにして検索しやすくする

        // カレンダーの日数分ループして、データがない日も含めた配列を作る（FN023仕様）
        $monthlyRecords = [];
        $daysInMonth = $startOfMonth->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = $startOfMonth->copy()->day($day)->format('Y-m-d');
            $attendance = $attendances->get($dateStr);

            if ($attendance) {
                // 休憩時間の合計（秒）を計算
                $breakSeconds = 0;
                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_out) {
                        $breakSeconds += Carbon::parse($break->break_out)->diffInSeconds(Carbon::parse($break->break_in));
                    }
                }

                // 労働時間の計算（退勤していれば算出）
                $workTime = '-';
                if ($attendance->clock_out) {
                    $totalSeconds = Carbon::parse($attendance->clock_out)->diffInSeconds(Carbon::parse($attendance->clock_in));
                    $actualSeconds = max(0, $totalSeconds - $breakSeconds);
                    $hours = floor($actualSeconds / 3600);
                    $minutes = floor(($actualSeconds % 3600) / 60);
                    $workTime = sprintf('%02d:%02d', $hours, $minutes);
                }

                $breakTimeStr = $breakSeconds > 0 ? sprintf('%02d:%02d', floor($breakSeconds / 3600), floor(($breakSeconds % 3600) / 60)) : '-';

                $monthlyRecords[] = [
                    'id' => $attendance->id,
                    'date' => Carbon::parse($dateStr)->isoFormat('MM/DD(ddd)'),
                    'clock_in' => Carbon::parse($attendance->clock_in)->format('H:i'),
                    'clock_out' => $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '-',
                    'break_time' => $breakTimeStr,
                    'work_time' => $workTime,
                ];
            } else {
                // 【FN023】データがない日付は項目を空白（空文字・ハイフン）にする
                $monthlyRecords[] = [
                    'id' => null,
                    'date' => Carbon::parse($dateStr)->isoFormat('MM/DD(ddd)'),
                    'clock_in' => '',
                    'clock_out' => '',
                    'break_time' => '',
                    'work_time' => '',
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
        $userId = Auth::id();
        $pendingRequests = AttendanceRequest::where('user_id', $userId)->where('status', 'pending')->get();
        $processedRequests = AttendanceRequest::where('user_id', $userId)->where('status', '!=', 'pending')->get();

        return view('attendance.request_list', compact('pendingRequests', 'processedRequests'));
    }

}
