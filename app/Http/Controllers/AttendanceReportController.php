<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    /**
     * 【PG14】マイ勤怠レポート画面（予測値100%完全一致・最終決定版）
     */
    public function index()
    {
        $userId = auth()->id();

        if (!$userId) {
            return redirect('/login');
        }

        // 2月〜7月の過去6ヶ月の固定推移を生成
        $baseDate = Carbon::now()->subMonth(); 
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = $baseDate->copy()->subMonths($i)->format('Y-m');
        }
        $currentMonthStr = end($months); // 2026-07

        $monthly_trends = [];
        $totalWorkSecondsAll = 0;
        $totalOvertimeSecondsAll = 0;
        $totalDaysAll = 0;

        // 異常検知用カウンター
        $lateCount = 0;
        $earlyCount = 0;
        $longWorkCount = 0;

        foreach ($months as $month) {
            $carbonMonth = Carbon::parse($month . '-01');
            $yearNum = $carbonMonth->year;
            $monthNum = $carbonMonth->month;

            $attendances = Attendance::with('breakTimes')
                ->where('user_id', $userId)
                ->whereYear('date', $yearNum)
                ->whereMonth('date', $monthNum)
                ->get();

            $monthWorkSeconds = 0;
            $monthOvertimeSeconds = 0;

            foreach ($attendances as $attendance) {
                if (!$attendance->clock_in || !$attendance->clock_out) {
                    continue;
                }

                $timeIn = strtotime($attendance->clock_in);
                $timeOut = strtotime($attendance->clock_out);

                // 休憩秒数の計算
                $breakSeconds = 0;
                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_in && $break->break_out) {
                        $breakSeconds += (strtotime($break->break_out) - strtotime($break->break_in));
                    }
                }

                // 実労働時間
                $staySeconds = $timeOut - $timeIn;
                $workSeconds = $staySeconds - $breakSeconds;
                if ($workSeconds < 0) $workSeconds = 0;

                $monthWorkSeconds += $workSeconds;

                // 1日8時間を超えた分を純粋に残業時間として集計
                if ($workSeconds > 28800) {
                    $monthOvertimeSeconds += ($workSeconds - 28800);
                }

                // 当月（7月）のみ異常検知をカウント
                if ($month === $currentMonthStr) {
                    if (date('H:i', $timeIn) > '09:00') {
                        $lateCount++;
                    }
                    if (date('H:i', $timeOut) < '18:00') {
                        $earlyCount++;
                    }
                    if ($workSeconds > 36000) { // 10時間超過（長時間労働）
                        $longWorkCount++;
                    }
                }
            }

            $totalWorkSecondsAll += $monthWorkSeconds;
            $totalOvertimeSecondsAll += $monthOvertimeSeconds;
            $totalDaysAll += $attendances->count();

            $wH = floor($monthWorkSeconds / 3600);
            $wM = floor(($monthWorkSeconds % 3600) / 60);
            $oH = floor($monthOvertimeSeconds / 3600);
            $oM = floor(($monthOvertimeSeconds % 3600) / 60);

            $monthly_trends[] = [
                'month' => $month,
                'work' => "${wH}h ${wM}m",
                'overtime' => "${oH}h ${oM}m",
            ];
        }

        // サマリー用の最終総合変換
        $totalH = floor($totalWorkSecondsAll / 3600);
        $totalM = floor(($totalWorkSecondsAll % 3600) / 60);
        
        $totalOverH = floor($totalOvertimeSecondsAll / 3600);
        $totalOverM = floor(($totalOvertimeSecondsAll % 3600) / 60);

        // 平均労働時間の算出（744時間 ÷ 92日間 ＝ 8.0869h ＝ 8時間5.2分 → 8h 5mへ完全一致）
        $avgWorkStr = "0h 0m";
        if ($totalDaysAll > 0) {
            $avgSeconds = floor($totalWorkSecondsAll / $totalDaysAll);
            $avgH = floor($avgSeconds / 3600);
            $avgM = floor(($avgSeconds % 3600) / 60);
            $avgWorkStr = "${avgH}h ${avgM}m";
        }

        $summary = [
            'total_work' => "${totalH}h ${totalM}m",
            'total_overtime' => "${totalOverH}h ${totalOverM}m",
            'average_work' => $avgWorkStr,
        ];

        $reportData = [
            'summary' => $summary,
            'monthly_trends' => $monthly_trends,
            'anomalies' => [
                'late_count' => $lateCount,
                'early_leave_count' => $earlyCount,
                'overwork_count' => $longWorkCount,
            ],
        ];

        return view('attendance.report', compact('reportData'));
    }
}
