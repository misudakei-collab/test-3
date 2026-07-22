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

    /**
     * 【FN034〜FN036】日次勤怠一覧画面
     */
    public function list(Request $request)
    {
        $currentDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        $attendances = Attendance::with('user', 'breakTimes')
            ->where('date', $currentDate)
            ->get();

        return view('admin.attendance_list', compact('attendances', 'currentDate'));
    }

    /**
     * 【FN037〜FN040】管理者用 勤怠詳細表示と直接修正保存処理
     */
    public function detail($id)
    {
        $attendance = Attendance::with('breakTimes', 'user')->findOrFail($id);
        return view('admin.detail', compact('attendance'));
    }

    public function updateDirect(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $request->validate([
            'clock_in' => 'required',
            'clock_out' => 'required',
        ]);

        $attendance->update([
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
        ]);

        $attendance->breakTimes()->delete();
        if ($request->has('breaks')) {
            foreach ($request->breaks as $break) {
                if (!empty($break['break_in'])) {
                    $attendance->breakTimes()->create([
                        'break_in' => $break['break_in'],
                        'break_out' => $break['break_out'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->back()->with('message', '勤怠データを修正しました。');
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
     * 【FN043、FN044】スタッフ別月次勤怠一覧画面
     */
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

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = $startOfMonth->copy()->day($day)->format('Y-m-d');
            $attendance = $attendances->get($dateStr);

            if ($attendance) {
                $monthlyRecords[] = [
                    'id' => $attendance->id,
                    'date' => Carbon::parse($dateStr)->isoFormat('MM/DD(ddd)'),
                    'clock_in' => Carbon::parse($attendance->clock_in)->format('H:i'),
                    'clock_out' => $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '-',
                    'break_count' => $attendance->breakTimes->count(),
                ];
            } else {
                $monthlyRecords[] = [
                    'id' => null,
                    'date' => Carbon::parse($dateStr)->isoFormat('MM/DD(ddd)'),
                    'clock_in' => '',
                    'clock_out' => '',
                    'break_count' => 0,
                ];
            }
        }

        return view('admin.staff_attendance', compact('user', 'monthlyRecords', 'currentMonth'));
    }

    /**
     * ★【FN045】スタッフ別月次勤怠情報の ★CSV出力機能
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
            fputcsv($stream, [chr(0xEF).chr(0xBB).chr(0xBF)]);
            fputcsv($stream, ['氏名', '日付', '出勤時間', '退勤時間', '休憩回数']);

            foreach ($attendances as $attendance) {
                fputcsv($stream, [
                    $user->name,
                    $attendance->date,
                    $attendance->clock_in,
                    $attendance->clock_out ?? '-',
                    $attendance->breakTimes->count(),
                ]);
            }
            fclose($stream);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"attendance_${id}_${month}.csv\"");

        return $response;
    }

    /**
     * 【PG12】申請一覧画面（管理者用）
     */
    public function requestList()
    {
        $pendingRequests = AttendanceRequest::with('user')
            ->where('status', 'pending')
            ->get();

        $processedRequests = AttendanceRequest::with('user')
            ->where('status', '!=', 'pending')
            ->get();

        return view('admin.request_list', compact('pendingRequests', 'processedRequests'));
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
