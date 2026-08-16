<?php

namespace App\Services;

use App\Models\Attendance;

class AttendanceCalculator
{
    public static function calculateTimes(Attendance $attendance): array
    {
        $breakTimeStr = '0:00';
        $workTimeStr = '-';

        if ($attendance->clock_in && $attendance->clock_out) {
            $totalBreakSeconds = 0;
            foreach ($attendance->breakTimes as $break) {
                if ($break->break_in && $break->break_out) {
                    $totalBreakSeconds += (strtotime($break->break_out) - strtotime($break->break_in));
                }
            }

            $breakHours = floor($totalBreakSeconds / 3600);
            $breakMinutes = floor(($totalBreakSeconds % 3600) / 60);
            $breakTimeStr = sprintf('%d:%02d', $breakHours, $breakMinutes);

            $timeIn = strtotime($attendance->clock_in);
            $timeOut = strtotime($attendance->clock_out);
            
            $workSeconds = ($timeOut - $timeIn) - $totalBreakSeconds;
            if ($workSeconds < 0) {
                $workSeconds = 0;
            }

            $workHours = floor($workSeconds / 3600);
            $workMinutes = floor(($workSeconds % 3600) / 60);
            $workTimeStr = sprintf('%d:%02d', $workHours, $workMinutes);

        }

        return [
            'break_time' => $breakTimeStr,
            'work_time' => $workTimeStr,
        ];
    }
}
