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

        $admin = User::create([
            'name' => 'ユーザー3(管理者)',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'email_verified_at' => Carbon::now(),
        ]);

        // 2. ★【user1の意図的データ】過去5ヶ月分 (各月平日15日固定)
        // 今月の一日(ついたち)から遡ることで、月末のズレを完全に防ぎます
        $startOfThisMonth = Carbon::now()->startOfMonth();

        for ($i = 5; $i >= 1; $i--) {
            $monthTarget = $startOfThisMonth->copy()->subMonths($i);
            $workDaysCount = 0;
            $daysInMonth = $monthTarget->daysInMonth;
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = $monthTarget->copy()->day($day);
                
                if ($dateStr->isWeekday() && $workDaysCount < 15) {
                    $att = Attendance::create([
                        'user_id' => $user1->id,
                        'date' => $dateStr->format('Y-m-d'),
                        'clock_in' => '09:00:00',
                        'clock_out' => '18:00:00',
                    ]);
                    
                    $att->breakTimes()->create([
                        'break_in' => '12:00:00',
                        'break_out' => '13:00:00',
                    ]);
                    
                    $workDaysCount++;
                }
            }
        }

        // 3. ★【user1の意図的データ】当月17日のパターン
        $thisMonth = Carbon::now()->startOfMonth();
        $patternCounts = [
            'normal' => 0,     // 通常 10件 (9:00 - 18:00)
            'overtime' => 0,   // 残業 3件  (9:00 - 20:00)
            'late' => 0,       // 遅刻 2件  (9:30 - 18:00)
            'early' => 0,      // 早退 1件  (9:00 - 17:00)
            'long' => 0,       // 長時間 1件(8:00 - 21:00)
        ];

        for ($day = 1; $day <= $thisMonth->daysInMonth; $day++) {
            $dateStr = $thisMonth->copy()->day($day);
            
            if ($dateStr->isAfter(Carbon::now())) {
                continue;
            }

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
                    'clock_out' => $out,
                ]);

                $att->breakTimes()->create([
                    'break_in' => '12:00:00',
                    'break_out' => '13:00:00',
                ]);
            }
        }

        // 4. ユーザー2(一般)用のサンプルダミー
        for ($d = 0; $d < 5; $d++) {
            $dateStr = Carbon::now()->subDays($d);
            if ($dateStr->isWeekday() && !$dateStr->isAfter(Carbon::now())) {
                $att2 = Attendance::create([
                    'user_id' => $user2->id,
                    'date' => $dateStr->format('Y-m-d'),
                    'clock_in' => '09:00:00',
                    'clock_out' => '18:00:00',
                ]);
                $att2->breakTimes()->create([
                    'break_in' => '12:00:00',
                    'break_out' => '13:00:00',
                ]);
            }
        }
    }
}
