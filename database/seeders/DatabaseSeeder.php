<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ユーザー情報の作成 (メール認証済み)
        $user1 = User::create([
            'name' => 'ユーザー1(一般)',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => Carbon::now(),
        ]);

        $user2 = User::create([
            'name' => 'ユーザー2(一般)',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => Carbon::now(),
        ]);

        User::create([
            'name' => 'ユーザー3(管理者)',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'email_verified_at' => Carbon::now(),
        ]);

        // 2. ★過去5ヶ月分 (2026年3月〜2026年7月) 各月平日15日 = 計75日
        // 時間計算が絶対にスキップされないよう、clock_outを確実にセットして保存します
        for ($m = 3; $m <= 7; $m++) {
            $monthTarget = Carbon::create(2026, $m, 1, 0, 0, 0);
            $workDaysCount = 0;
            $daysInMonth = $monthTarget->daysInMonth;
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = $monthTarget->copy()->day($day);
                
                if ($dateStr->isWeekday() && $workDaysCount < 15) {
                    $att = Attendance::create([
                        'user_id' => $user1->id,
                        'date' => $dateStr->format('Y-m-d'),
                        'clock_in' => '09:00:00',
                        'clock_out' => '18:00:00', // ★確実に出力
                    ]);
                    
                    $att->breakTimes()->create([
                        'break_in' => '12:00:00',
                        'break_out' => '13:00:00',
                    ]);
                    
                    $workDaysCount++;
                }
            }
        }

        // 3. ★当月 (2026年8月) の意図的な異常検知・時間集計用データ (計17日分)
        $augustMonth = Carbon::create(2026, 8, 1, 0, 0, 0);
        $patternCounts = [
            'normal' => 0,     // 通常 10件 (9:00 - 18:00)
            'overtime' => 0,   // 残業 3件  (9:00 - 20:00)
            'late' => 0,       // 遅刻 2件  (09:30 - 18:00)
            'early' => 0,      // 早退 1件  (09:00 - 17:00)
            'long' => 0,       // 長時間 1件(08:00 - 21:00)
        ];

        for ($day = 1; $day <= 31; $day++) {
            $dateStr = $augustMonth->copy()->day($day);
            if ($dateStr->isWeekday()) {
                $in = '09:00:00';
                $out = '18:00:00';

                if ($patternCounts['normal'] < 10) {
                    $patternCounts['normal']++;
                } elseif ($patternCounts['overtime'] < 3) {
                    $in = '09:00:00'; $out = '20:00:00';
                    $patternCounts['overtime']++;
                } elseif ($patternCounts['late'] < 2) {
                    $in = '09:30:00'; $out = '18:00:00';
                    $patternCounts['late']++;
                } elseif ($patternCounts['early'] < 1) {
                    $in = '09:00:00'; $out = '17:00:00';
                    $patternCounts['early']++;
                } elseif ($patternCounts['long'] < 1) {
                    $in = '08:00:00'; $out = '21:00:00';
                    $patternCounts['long']++;
                } else {
                    continue;
                }

                $att = Attendance::create([
                    'user_id' => $user1->id,
                    'date' => $dateStr->format('Y-m-d'),
                    'clock_in' => $in,
                    'clock_out' => $out, // ★確実に出力
                ]);

                $att->breakTimes()->create([
                    'break_in' => '12:00:00',
                    'break_out' => '13:00:00',
                ]);
            }
        }
    }
}
