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
        // -------------------------------------------------------------
        // 👤 【要件】6つのアカウント作成 (すべてメール認証済み状態)
        // -------------------------------------------------------------
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

        $user3 = User::create([
            'name' => 'ユーザー3(管理者)',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'email_verified_at' => Carbon::now(),
        ]);

        $user4 = User::create([
            'name' => 'ユーザー4(一般)',
            'email' => 'user4@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => Carbon::now(),
        ]);

        $user5 = User::create([
            'name' => 'ユーザー5(一般)',
            'email' => 'user5@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => Carbon::now(),
        ]);

        $user6 = User::create([
            'name' => 'ユーザー6(一般)',
            'email' => 'user6@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => Carbon::now(),
        ]);

        // -------------------------------------------------------------
        // 📊 【テスト確認用】ユーザー2 〜 ユーザー6 の5名に対して、
        // 1週間分（8月7日 〜 8月13日）の「7:00〜15:00労働、12:00〜12:00休憩」を完全注入！
        // -------------------------------------------------------------
        $targetUsers = [$user2->id, $user3->id, $user4->id, $user5->id, $user6->id];
        
        for ($day = 7; $day <= 13; $day++) {
            $dateStr = sprintf('2026-08-%02d', $day);
            foreach ($targetUsers as $uId) {
                // 1日分の出退勤データ作成
                $att = Attendance::create([
                    'user_id' => $uId,
                    'date' => $dateStr,
                    'clock_in' => '07:00:00',
                    'clock_out' => '15:00:00',
                ]);
                
                // 12:00〜12:00（0分休憩）データを作成
                $att->breakTimes()->create([
                    'break_in' => '12:00:00',
                    'break_out' => '12:00:00',
                ]);
            }
        }

        // -------------------------------------------------------------
        // 📊 ユーザー1(一般)の既存検証データ (過去5ヶ月分 3月〜7月)
        // -------------------------------------------------------------
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

        // -------------------------------------------------------------
        // 🚨 ユーザー1(一般)の既存検証データ (当月8月の異常検知パターン)
        // -------------------------------------------------------------
        $augustMonth = Carbon::create(2026, 8, 1, 0, 0, 0);
        $patternCounts = [
            'normal' => 0, 'overtime' => 0, 'late' => 0, 'early' => 0, 'long' => 0,
        ];
        for ($day = 1; $day <= 31; $day++) {
            $dateStr = $augustMonth->copy()->day($day);
            if ($dateStr->isWeekday()) {
                $in = '09:00:00'; $out = '18:00:00';
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
    }
}
