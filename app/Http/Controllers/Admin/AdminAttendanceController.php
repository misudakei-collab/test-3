<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    /**
     * 【PG08】当日の全スタッフ勤怠一覧画面
     */
    public function list(Request $request)
    {
        $currentDate = $request->input('date', Carbon::now()->format('Y-m-d'));
        $users = User::where('is_admin', false)->get();
        $attendances = Attendance::with('breakTimes')->where('date', $currentDate)->get()->keyBy('user_id');

        $records = [];
        foreach ($users as $user) {
            $attendance = $attendances->get($user->id);
            if ($attendance) {
                // 💡 当日の全スタッフの休憩秒数を集計するループ処理部分です
                $totalBreakSeconds = 0;
                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_in && $break->break_out) {
                        $totalBreakSeconds += (strtotime($break->break_out) - strtotime($break->break_in));
                    }
                }
                
                // 💡【重要修正：指摘175への完全対応】略称（$bH, $bM）を分かりやすいフルスペルに書き換えます
                $breakHours = floor($totalBreakSeconds / 3600);
                $breakMinutes = floor(($totalBreakSeconds % 3600) / 60);
                $breakTimeStr = sprintf('%d:%02d', $breakHours, $breakMinutes);

                $workTimeStr = '-';
                if ($attendance->clock_out) {
                    $staySeconds = strtotime($attendance->clock_out) - strtotime($attendance->clock_in);
                    $workSeconds = $staySeconds - $totalBreakSeconds;
                    if ($workSeconds < 0) $workSeconds = 0;
                    
                    // 💡【重要修正：指摘189への完全対応】略称（$wH, $wM）をフルスペル名称へ変更
                    $workHours = floor($workSeconds / 3600);
                    $workMinutes = floor(($workSeconds % 3600) / 60);
                    $workTimeStr = sprintf('%d:%02d', $workHours, $workMinutes);
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
                $records[] = [
                    'attendance_id' => null, 'name' => $user->name,
                    'clock_in' => '-', 'clock_out' => '-', 'break_time' => '-', 'work_time' => '-',
                ];
            }
        }
        return view('admin.attendance_list', compact('records', 'currentDate'));
    }

    /**
     * 【PG09】管理者用：スタッフ勤怠詳細画面の表示
     */
    public function detail($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);
        Carbon::setLocale('ja');
        $formattedDate = Carbon::parse($attendance->date)->isoFormat('YYYY年M月D日(ddd)');
        return view('admin.detail', compact('attendance', 'formattedDate'));
    }

    /**
     * 【PG09】管理者用：スタッフ勤怠データの直接修正・更新
     */
    public function updateDetail(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'clock_in' => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
        ]);

        if ($request->has('breaks')) {
            foreach ($request->input('breaks') as $breakId => $breakData) {
                $break = BreakTime::find($breakId);
                if ($break) {
                    $break->update([
                        'break_in' => $breakData['break_in'],
                        'break_out' => $breakData['break_out'],
                    ]);
                }
            }
        }
        return redirect()->route('admin.attendance.list', ['date' => $attendance->date]);
    }

    /**
     * 【PG10】登録されている全一般スタッフのリスト閲覧
     */
    public function staffList()
    {
        $staffs = User::where('is_admin', false)->get();
        return view('admin.staff_list', compact('staffs'));
    }

    /**
     * 管理者用：選択したスタッフの月次一覧閲覧画面を表示します。
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
        Carbon::setLocale('ja');

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = $startOfMonth->copy()->day($day)->format('Y-m-d');
            $attendance = $attendances->get($dateStr);
            $formattedDate = Carbon::parse($dateStr)->isoFormat('MM/DD(ddd)');

            if ($attendance) {
                // 一般スタッフ側と全く同じ共通計算サービスを利用してロジックを一元管理します
                $calculated = \App\Services\AttendanceCalculator::calculateTimes($attendance);

                $monthlyRecords[] = [
                    'id' => $attendance->id,
                    'date' => $formattedDate,
                    'clock_in' => $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                    'clock_out' => $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '-',
                    'break_time' => $calculated['break_time'],
                    'work_time' => $calculated['work_time'],
                ];
            } else {
                $monthlyRecords[] = [
                    'id' => null, 'date' => $formattedDate, 'clock_in' => '', 'clock_out' => '', 'break_time' => '-', 'work_time' => '-',
                ];
            }
        }
        return view('admin.staff_attendance', compact('monthlyRecords', 'currentMonth', 'user'));
    }

}
