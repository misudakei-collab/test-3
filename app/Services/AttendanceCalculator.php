<?php

namespace App\Services;

use App\Models\Attendance;

class AttendanceCalculator
{
    /**
     * 勤怠レコードから「休憩時間」と「実労働時間」を秒単位で計算し、文字列（H:i）形式の配列で返却します。
     * コメント履歴や指示書記号を排除し、処理の意図のみに限定したクリーンな設計です。
     */
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
            
            $bH = floor($totalBreakSeconds / 3600);
            $bM = floor(($totalBreakSeconds % 3600) / 60);
            $breakTimeStr = sprintf('%d:%02d', $bH, $bM);

            $timeIn = strtotime($attendance->clock_in);
            $timeOut = strtotime($attendance->clock_out);
            
            $workSeconds = ($timeOut - $timeIn) - $totalBreakSeconds;
            if ($workSeconds < 0) {
                $workSeconds = 0;
            }

            $wH = floor($workSeconds / 3600);
            $wM = floor(($workSeconds % 3600) / 60);
            $workTimeStr = sprintf('%d:%02d', $wH, $wM);
        }

        return [
            'break_time' => $breakTimeStr,
            'work_time' => $workTimeStr,
        ];
    }
}
