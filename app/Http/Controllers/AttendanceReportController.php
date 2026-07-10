<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    /**
     * 【FN052〜FN056】勤怠統計レポートの集計と画面表示
     */
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();

        // 過去6ヶ月分の勤怠データを取得（休憩データも同時にロード）
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->where('date', '>=', $sixMonthsAgo->format('Y-m-d'))
            ->where('date', '<=', $now->format('Y-m-d'))
            ->get();

        // 初期化
        $summary = ['total_work_seconds' => 0, 'total_overtime_seconds' => 0, 'days_worked' => 0];
        $monthlyTrends = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $monthStr = Carbon::now()->subMonths($i)->format('Y-m');
            $monthlyTrends[$monthStr] = ['month' => $monthStr, 'work_seconds' => 0, 'overtime_seconds' => 0];
        }

        $currentMonthStr = $now->format('Y-m');
        $anomalies = ['late_count' => 0, 'early_leave_count' => 0, 'overwork_count' => 0];

        // 計算ループ
        foreach ($attendances as $attendance) {
            if (!$attendance->clock_out) continue;

            $clockIn = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);
            $attendanceMonth = Carbon::parse($attendance->date)->format('Y-m');

            // 実労働時間と残業時間の計算
            $totalSeconds = $clockOut->diffInSeconds($clockIn);
            $breakSeconds = 0;
            foreach ($attendance->breakTimes as $break) {
                if ($break->break_out) {
                    $breakSeconds += Carbon::parse($break->break_out)->diffInSeconds(Carbon::parse($break->break_in));
                }
            }
            $workSeconds = max(0, $totalSeconds - $breakSeconds);
            $overtimeSeconds = max(0, $workSeconds - (8 * 3600));

            // 加算
            $summary['total_work_seconds'] += $workSeconds;
            $summary['total_overtime_seconds'] += $overtimeSeconds;
            $summary['days_worked']++;

            if (isset($monthlyTrends[$attendanceMonth])) {
                $monthlyTrends[$attendanceMonth]['work_seconds'] += $workSeconds;
                $monthlyTrends[$attendanceMonth]['overtime_seconds'] += $overtimeSeconds;
            }

            // 当月内の異常検知
            if ($attendanceMonth === $currentMonthStr) {
                if ($clockIn->format('H:i:s') > '09:00:00') $anomalies['late_count']++;
                if ($clockOut->format('H:i:s') < '18:00:00') $anomalies['early_leave_count']++;
                if ($workSeconds > (10 * 3600)) $anomalies['overwork_count']++;
            }
        }

        // フォーマット整形（〇h 〇m 形式）
        $formatHoursMinutes = function($seconds) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return "{$hours}h {$minutes}m";
        };

        $avgWorkSeconds = $summary['days_worked'] > 0 ? $summary['total_work_seconds'] / $summary['days_worked'] : 0;

        $reportData = [
            'summary' => [
                'total_work' => $formatHoursMinutes($summary['total_work_seconds']),
                'total_overtime' => $formatHoursMinutes($summary['total_overtime_seconds']),
                'average_work' => $formatHoursMinutes($avgWorkSeconds),
            ],
            'monthly_trends' => array_map(function($trend) use ($formatHoursMinutes) {
                return [
                    'month' => $trend['month'],
                    'work' => $formatHoursMinutes($trend['work_seconds']),
                    'overtime' => $formatHoursMinutes($trend['overtime_seconds']),
                ];
            }, array_values($monthlyTrends)),
            'anomalies' => $anomalies
        ];

        return view('attendance.report', compact('reportData'));
    }
}
