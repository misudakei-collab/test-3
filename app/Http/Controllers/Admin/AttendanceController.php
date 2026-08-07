<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\AttendanceRequest; 
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
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

    public function staffAttendance(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $id)
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

            // ★【見本完全一致】MM/DDではなく、正しい曜日付きフォーマットに修正しました
             $formattedDate = Carbon::parse($dateStr)->isoFormat('MM/DD(ddd)');

            if ($attendance) {
                $monthlyRecords[] = [
                    'id' => $attendance->id,
                    'date' => $formattedDate,
                    'clock_in' => Carbon::parse($attendance->clock_in)->format('H:i'),
                    'clock_out' => $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '-',
                    'break_count' => $attendance->breakTimes->count(),
                ];
            } else {
                $monthlyRecords[] = [
                    'id' => null,
                    'date' => $formattedDate,
                    'clock_in' => '',
                    'clock_out' => '',
                    'break_count' => 0,
                ];
            }
        }

        // ※ループの後にビューへデータを渡して返却する記述（残りの行）はそのままで大丈夫です
        return view('admin.staff_attendance', compact('user', 'monthlyRecords', 'currentMonth'));
    }


    /**
     * 【PG10】スタッフ一覧画面（管理者用）
     */
    public function staffList()
    {
        $staffs = User::where('id', '!=', auth()->id())->get();
        return view('admin.staff_list', compact('staffs'));
    }

    /**
     * 【PG08】当日の全スタッフ勤怠一覧画面（管理者用）
     */
    public function list(Request $request)
    {
        // 1. 画面上部の日付コントロールから選択された日付（なければ今日の日付）を取得
        $currentDate = $request->input('date', \Carbon\Carbon::now()->format('Y-m-d'));

        // 2. 登録されているすべての一般スタッフ（is_adminがfalseのユーザー）を取得
        $users = \App\Models\User::where('is_admin', false)->get();

        // 3. 選択された「その日」の全員の勤怠データを一括取得
        $attendances = \App\Models\Attendance::with('breakTimes')
            ->where('date', $currentDate)
            ->get()
            ->keyBy('user_id');

        $records = [];

        foreach ($users as $user) {
            $attendance = $attendances->get($user->id);

                if ($attendance) {
                // ★【重要修正】Carbonを使わずPHP標準のstrtotimeで純粋な時刻から休憩秒数を引き算します
                $totalBreakSeconds = 0;
                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_in && $break->break_out) {
                        $totalBreakSeconds += (strtotime($break->break_out) - strtotime($break->break_in));
                    }
                }
                $bH = floor($totalBreakSeconds / 3600);
                $bM = floor(($totalBreakSeconds % 3600) / 60);
                $breakTimeStr = sprintf('%d:%02d', $bH, $bM);

                // ★【重要修正】ここも同様にstrtotimeで出退勤の総拘束時間を計算します
                $workTimeStr = '-';
                if ($attendance->clock_out) {
                    $timeIn = strtotime($attendance->clock_in);
                    $timeOut = strtotime($attendance->clock_out);
                    
                    $staySeconds = $timeOut - $timeIn;
                    $workSeconds = $staySeconds - $totalBreakSeconds;
                    if ($workSeconds < 0) $workSeconds = 0;
                    
                    $wH = floor($workSeconds / 3600);
                    $wM = floor(($workSeconds % 3600) / 60);
                    $workTimeStr = sprintf('%d:%02d', $wH, $wM);
                }

                $records[] = [
                    'attendance_id' => $attendance->id,
                    'name' => $user->name,
                    'clock_in' => $attendance->clock_in ? date('H:i', strtotime($attendance->clock_in)) : '-',
                    'clock_out' => $attendance->clock_out ? date('H:i', strtotime($attendance->clock_out)) : '-',
                    'break_time' => $breakTimeStr,
                    'work_time' => $workTimeStr,
                ];

            } else {
                // その日、まだ打刻データがないスタッフの空枠
                $records[] = [
                    'attendance_id' => null,
                    'name' => $user->name,
                    'clock_in' => '-',
                    'clock_out' => '-',
                    'break_time' => '-',
                    'work_time' => '-',
                ];
            }
        }

        // 先ほど作成した公式見本完全一致のBladeファイルを正しく呼び出します
        return view('admin.attendance_list', compact('records', 'currentDate'));
    }




    /**
     * ★【FN045】スタッフ別月次勤怠情報の ★CSV出力機能（休憩時間・労働時間対応版）
     */
    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $id)
            ->where('date', 'like', "${month}%")
            ->orderBy('date', 'asc')
            ->get();

        $response = new StreamedResponse(function () use ($attendances, $user) {
            $stream = fopen('php://output', 'w');
            
            // ★Excelの文字化けを100%防ぐBOM（Byte Order Mark）を確実に先頭へ注入
            fwrite($stream, pack('C*', 0xEF, 0xBB, 0xBF));

            // 【見本・指示書完全一致】項目名を回数から「休憩時間」「労働時間」へ変更
            fputcsv($stream, ['氏名', '日付', '出勤時間', '退勤時間', '休憩時間', '労働時間']);

            // Carbonに日本語の曜日（月、火など）を使うように設定
            Carbon::setLocale('ja');

            foreach ($attendances as $attendance) {
                // 日付を「06/01(木)」のような美しい日本語曜日付き形式に変換
                $formattedDate = Carbon::parse($attendance->date)->isoFormat('MM/DD(ddd)');
                
                $clockIn = Carbon::parse($attendance->clock_in);
                $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;

                // ① 休憩時間の合計を計算（秒単位から H:i 形式へ変換）
                $totalBreakSeconds = 0;
                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_in && $break->break_out) {
                        $totalBreakSeconds += Carbon::parse($break->break_out)->diffInSeconds(Carbon::parse($break->break_in));
                    }
                }
                $breakHours = floor($totalBreakSeconds / 3600);
                $breakMinutes = floor(($totalBreakSeconds % 3600) / 60);
                $breakTimeStr = sprintf('%02d:%02d', $breakHours, $breakMinutes);

                // ② 労働時間の合計を計算（退勤していれば、総拘束時間 - 休憩時間）
                $workTimeStr = '-';
                if ($clockOut) {
                    $totalStaySeconds = $clockOut->diffInSeconds($clockIn);
                    $totalWorkSeconds = $totalStaySeconds - $totalBreakSeconds;
                    
                    if ($totalWorkSeconds < 0) {
                        $totalWorkSeconds = 0;
                    }
                    
                    $workHours = floor($totalWorkSeconds / 3600);
                    $workMinutes = floor(($totalWorkSeconds % 3600) / 60);
                    $workTimeStr = sprintf('%02d:%02d', $workHours, $workMinutes);
                }

                fputcsv($stream, [
                    $user->name,
                    $formattedDate,
                    $clockIn->format('H:i'),
                    $clockOut ? $clockOut->format('H:i') : '-',
                    $breakTimeStr,
                    $workTimeStr,
                ]);
            }
            fclose($stream);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"attendance_${id}_${month}.csv\"");

        return $response;
    }

    /**
     * 【PG12】申請一覧画面（管理者用）
     */
    public function requestList()
    {
        // 1. 全スタッフの承認待ち申請データを取得
        $pendingRequests = \App\Models\AttendanceRequest::with('user')
            ->where('status', 'pending')
            ->get();

        // ★【追加ライン】2. 全スタッフの承認済み申請データも一緒に取得します
        $approvedRequests = \App\Models\AttendanceRequest::with('user')
            ->where('status', 'approved')
            ->get();

        // ★【修正ライン】compactの中に $approvedRequests を追加して画面に送ります
        return view('admin.request_list', compact('pendingRequests', 'approvedRequests'));
    }


        /**
     * 【PG13】修正申請承認画面（管理者用）
     */
    public function approveView($requestId)
    {
        // 申請データと、申請した一般スタッフの情報を一緒に取得
        $requestData = AttendanceRequest::with('user')->findOrFail($requestId);
        return view('admin.approve_view', compact('requestData'));
    }

    /**
     * 【FN051】修正申請の承認処理（画面表示変更・同じページに留まる仕様）
     */
    public function approveAction($requestId)
    {
        $attendanceRequest = AttendanceRequest::findOrFail($requestId);
        
        // ステータスを承認済み(approved)に変更
        $attendanceRequest->update(['status' => 'approved']);

        // 勤怠本データを自動上書き更新
        $attendance = Attendance::updateOrCreate(
            ['id' => $attendanceRequest->attendance_id],
            [
                'user_id' => $attendanceRequest->user_id,
                'date' => $attendanceRequest->date,
                'clock_in' => $attendanceRequest->clock_in,
                'clock_out' => $attendanceRequest->clock_out,
            ]
        );

        // 休憩データも同様にリセットして申請内容で上書き更新
        $attendance->breakTimes()->delete();
        $breaks = json_decode($attendanceRequest->break_times, true);
        if (is_array($breaks)) {
            foreach ($breaks as $break) {
                if (!empty($break['break_in'])) {
                    $attendance->breakTimes()->create([
                        'break_in' => $break['break_in'],
                        'break_out' => $break['break_out'] ?? null,
                    ]);
                }
            }
        }

        // 画面遷移させず、同じページに戻して「承認済み」グレーボタンに切り替えます
        return redirect()->back();
    }

}
