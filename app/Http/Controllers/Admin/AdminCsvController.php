<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCsvController extends Controller
{
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
            
            fwrite($stream, pack('C*', 0xEF, 0xBB, 0xBF));

            fputcsv($stream, ['氏名', '日付', '出勤時間', '退勤時間', '休憩時間', '労働時間']);

            Carbon::setLocale('ja');
            foreach ($attendances as $attendance) {
                $formattedDate = Carbon::parse($attendance->date)->isoFormat('MM/DD(ddd)');
                $clockInStr = $attendance->clock_in ? date('H:i', strtotime($attendance->clock_in)) : '-';
                $clockOutStr = $attendance->clock_out ? date('H:i', strtotime($attendance->clock_out)) : '-';

                $breakTimeStr = '00:00'; $workTimeStr = '-';
                if ($attendance->clock_in && $attendance->clock_out) {
                    $totalBreakSeconds = 0;
                    foreach ($attendance->breakTimes as $break) {
                        if ($break->break_in && $break->break_out) {
                            $totalBreakSeconds += (strtotime($break->break_out) - strtotime($break->break_in));
                        }
                    }
                    $breakTimeStr = sprintf('%02d:%02d', floor($totalBreakSeconds / 3600), floor(($totalBreakSeconds % 3600) / 60));

                    $staySeconds = strtotime($attendance->clock_out) - strtotime($attendance->clock_in);
                    $totalWorkSeconds = $staySeconds - $totalBreakSeconds;
                    if ($totalWorkSeconds < 0) $totalWorkSeconds = 0;
                    $workTimeStr = sprintf('%02d:%02d', floor($totalWorkSeconds / 3600), floor(($totalWorkSeconds % 3600) / 60));
                }

                fputcsv($stream, [$user->name, $formattedDate, $clockInStr, $clockOutStr, $breakTimeStr, $workTimeStr]);
            }
            fclose($stream);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"attendance_${id}_${month}.csv\"");
        return $response;
    }
}
