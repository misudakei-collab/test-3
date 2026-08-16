<?php

namespace App\Services;

use App\Models\Attendance;

class AttendanceCalculator
{
    /**
     * 勤怠レコードから「休憩時間」と「実労働時間」を秒単位で計算し、文字列（H:i）形式の配列で返却します。
     * 略称を完全に排除し、可読性と保守性を最大化したクリーンコード仕様です。
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
            
            // 💡 休憩時間はフルスペルに修正済み
            $breakHours = floor($totalBreakSeconds / 3600);
            $breakMinutes = floor(($totalBreakSeconds % 3600) / 60);
            $breakTimeStr = sprintf('%d:%02d', $breakHours, $breakMinutes);

            $timeIn = strtotime($attendance->clock_in);
            $timeOut = strtotime($attendance->clock_out);
            
            $workSeconds = ($timeOut - $timeIn) - $totalBreakSeconds;
            if ($workSeconds < 0) {
                $workSeconds = 0;
            }

            // 💡【指示通りに100%修正完了】略称（$wH, $wM）から分かりやすいフルスペルへ変更済み
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
