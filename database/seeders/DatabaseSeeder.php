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
        // 👤 6つのアカウント作成 (すべてメール認証済み状態)
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
        // 📊 【残業時間ランダム分散】過去5ヶ月分 (2月〜6月) 各月平日15日 = 計75日間
        // 総残業10時間のうち、「通常残業6時間分」を各月に綺麗に分散させます。
        // 残り4時間分は7月の「長時間労働(12h)」で強制発生するため、ここでは計6時間(21,600秒)をプールします。
        // -------------------------------------------------------------
        $overtimePools = [
            2 => 3600,  // 2月: 残業1時間
            3 => 7200,  // 3月: 残業2時間
            4 => 7200,  // 4月: 残業2時間
            5 => 3600,  // 5月: 残業1時間
            6 => 0,     // 6月: 残業0時間 (合計21,600秒 = 6時間)
        ];

        for ($m = 2; $m <= 6; $m++) {
            $monthTarget = Carbon::create(2026, $m, 1, 0, 0, 0);
            $workDaysCount = 0;
            $daysInMonth = $monthTarget->daysInMonth;
            $remainingOvertime = $overtimePools[$m];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = $monthTarget->copy()->day($day);
                if ($dateStr->isWeekday() && $workDaysCount < 15) {
                    $clockIn = '09:00:00';
                    $clockOut = '18:00:00';

                    // その月の最終出勤日（15日目）に、プールされている残業時間をまとめて処理
                    if ($workDaysCount == 14 && $remainingOvertime > 0) {
                        $addHours = floor($remainingOvertime / 3600);
                        $clockOut = sprintf('%02d:00:00', 18 + $addHours);
                        $remainingOvertime = 0;
                    }

                    $att = Attendance::create([
                        'user_id' => $user1->id,
                        'date' => $dateStr->format('Y-m-d'),
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
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
        // 📊 【残業時間ランダム分散】当月 (7月) 指定の 17日間パターン
        // 2月〜6月に通常残業を逃がした分、7月の通常勤務日の退勤時間を
        // 前回の「20:00」から、残業なしの定時「18:00」へと戻して合計時間のバランスを取ります。
        // -------------------------------------------------------------
        $julyMonth = Carbon::create(2026, 7, 1, 0, 0, 0);
        $patternCounts = [
            'normal'   => 0, // 通常 13件 (09:00 - 18:00 = 実働8h) ※元々の通常10件に、残業から定時に戻した3件を合算
            'late'     => 0, // 遅刻  2件 (09:30 - 18:00 = 実働7.5h)
            'early'    => 0, // 早退  1件 (09:00 - 17:00 = 実働7h)
            'long'     => 0, // 長時間 1件 (08:00 - 21:00 = 実働12h / ここで残業が4時間発生)
        ]; // 👉 7月総実働 = (13×8) + 15 + 7 + 12 = 138時間 / 7月の残業 = 4時間ぴったり！
           // 👉 全期間(92日間)の総労働時間 = 2〜6月(600h + 通常残業6h) + 7月(138h) = 744時間！
           // 👉 全期間(92日間)の総残業時間 = 2〜6月(6h) + 7月(4h) = 10時間！完璧に一致！

        $workDaysCountJuly = 0;
        for ($day = 1; $day <= 31; $day++) {
            $dateStr = $julyMonth->copy()->day($day);
            if ($dateStr->isWeekday() && $workDaysCountJuly < 17) {
                $in = '09:00:00';
                $out = '18:00:00';

                if ($workDaysCountJuly == 3 && $patternCounts['late'] < 2) {
                    $in = '09:30:00'; $out = '18:00:00'; $patternCounts['late']++;
                } elseif ($workDaysCountJuly == 7 && $patternCounts['late'] < 2) {
                    $in = '09:30:00'; $out = '18:00:00'; $patternCounts['late']++;
                } elseif ($workDaysCountJuly == 9 && $patternCounts['early'] < 1) {
                    $in = '09:00:00'; $out = '17:00:00'; $patternCounts['early']++;
                } elseif ($workDaysCountJuly == 13 && $patternCounts['long'] < 1) {
                    $in = '08:00:00'; $out = '21:00:00'; $patternCounts['long']++;
                } else {
                    $patternCounts['normal']++;
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
                $workDaysCountJuly++;
            }
        }

        // -------------------------------------------------------------
        // 📊 他のユーザー2 〜 ユーザー6 への固定データ注入（7月）
        // -------------------------------------------------------------
        $targetUsers = [$user2->id, $user3->id, $user4->id, $user5->id, $user6->id];
        for ($day = 7; $day <= 13; $day++) {
            $dateStr = sprintf('2026-07-%02d', $day);
            foreach ($targetUsers as $uId) {
                $att = Attendance::create([
                    'user_id' => $uId,
                    'date' => $dateStr,
                    'clock_in' => '07:00:00',
                    'clock_out' => '15:00:00',
                ]);
                $att->breakTimes()->create([
                    'break_in' => '12:00:00',
                    'break_out' => '12:00:00',
                ]);
            }
        }
    }
}
