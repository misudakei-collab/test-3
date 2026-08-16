<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    /**
     * Display the attendance report.
     */
    public function index()
    {
        $userId = auth()->id();

        if (!$userId) {
            return redirect('/login');
        }

        $baseDate = Carbon::now()->subMonth(); 
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = $baseDate->copy()->subMonths($i)->format('Y-m');
        }
        $currentMonthStr = end($months);

        $monthly_trends = [];
        $totalWorkSecondsAll = 0;
        $totalOvertimeSecondsAll = 0;
        $totalDaysAll = 0;

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

                $breakSeconds = 0;
                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_in && $break->break_out) {
                        $breakSeconds += (strtotime($break->break_out) - strtotime($break->break_in));
                    }
                }

                $staySeconds = $timeOut - $timeIn;
                $workSeconds = $staySeconds - $breakSeconds;
                if ($workSeconds < 0) $workSeconds = 0;

                $monthWorkSeconds += $workSeconds;

                if ($workSeconds > 28800) {
                    $monthOvertimeSeconds += ($workSeconds - 28800);
                }

                if ($month === $currentMonthStr) {
                    if (date('H:i', $timeIn) > '09:00') {
                        $lateCount++;
                    }
                    if (date('H:i', $timeOut) < '18:00') {
                        $earlyCount++;
                    }
                    if ($workSeconds > 36000) {
                        $longWorkCount++;
                    }
                }
            }

            $totalWorkSecondsAll += $monthWorkSeconds;
            $totalOvertimeSecondsAll += $monthOvertimeSeconds;
            $totalDaysAll += $attendances->count();

            $workHours = floor($monthWorkSeconds / 3600);
            $workMinutes = floor(($monthWorkSeconds % 3600) / 60);
            $overtimeHours = floor($monthOvertimeSeconds / 3600);
            $overtimeMinutes = floor(($monthOvertimeSeconds % 3600) / 60);

            $monthly_trends[] = [
                'month' => $month,
                'work' => "${workHours}h ${workMinutes}m",
                'overtime' => "${overtimeHours}h ${overtimeMinutes}m",
            ];
        }

        $totalWorkHours = floor($totalWorkSecondsAll / 3600);
        $totalWorkMinutes = floor(($totalWorkSecondsAll % 3600) / 60);
        
        $totalOvertimeHours = floor($totalOvertimeSecondsAll / 3600);
        $totalOvertimeMinutes = floor(($totalOvertimeSecondsAll % 3600) / 60);

        $avgWorkStr = "0h 0m";
        if ($totalDaysAll > 0) {
            $avgSeconds = floor($totalWorkSecondsAll / $totalDaysAll);
            $avgHours = floor($avgSeconds / 3600);
            $avgMinutes = floor(($avgSeconds % 3600) / 60);
            $avgWorkStr = "${avgHours}h ${avgMinutes}m";
        }

        $summary = [
            'total_work' => "${totalWorkHours}h ${totalWorkMinutes}m",
            'total_overtime' => "${totalOvertimeHours}h ${totalOvertimeMinutes}m",
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
