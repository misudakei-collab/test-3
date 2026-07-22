@extends('layouts.app')

@section('content')
<div class="max-w-[1200px] mx-auto py-6 select-none" style="font-family: sans-serif !important;">

    <!-- ページタイトル（公式見本通りの配置・縦線） -->
    <div class="flex items-center mb-12 pl-1" style="gap: 16px !important;">
        <div style="width: 4px !important; height: 28px !important; background-color: #000000 !important; border-radius: 9999px !important;"></div>
        <h2 class="text-2xl font-bold tracking-wider text-gray-900" style="margin: 0 !important; font-size: 24px !important;">
            {{ \Carbon\Carbon::parse($currentDate)->format('Y年m月d日') }}の勤怠
        </h2>
    </div>

    <!-- 【公式完全一致】1つの白い細長いカード内に前日・日付・翌日を配置するエリア（FN035） -->
    <div style="max-w: 750px !important; margin: 0 auto 48px auto !important; background-color: #ffffff !important; border: 1px solid #e5e7eb !important; border-radius: 8px !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important; height: 48px !important; display: flex !important; align-items: center !important; justify-content: space-between !important; padding: 0 24px !important; box-sizing: border-box !important;">
        
        <!-- 左端：前日ボタン -->
        <a href="?date={{ \Carbon\Carbon::parse($currentDate)->subDay()->format('Y-m-d') }}" 
            style="color: #9ca3af !important; text-decoration: none !important; font-size: 14px !important; font-weight: bold !important; display: flex !important; align-items: center !important; gap: 6px !important; transition: color 0.15s !important;"
            onmouseover="this.style.color='#000000'" onmouseout="this.style.color='#9ca3af'">
            <span>←</span> 前日
        </a>

        <!-- 中央：日付（見本通りのカレンダーアイコンと太文字） -->
        <div style="display: flex !important; align-items: center !important; gap: 8px !important; color: #000000 !important; font-size: 16px !important; font-weight: 800 !important; letter-spacing: 0.02em !important;">
            <span style="font-size: 16px !important; display: flex !important; align-items: center !important;">📅</span>
            <span>{{ \Carbon\Carbon::parse($currentDate)->format('Y/m/d') }}</span>
        </div>

        <!-- 右端：翌日ボタン -->
        <a href="?date={{ \Carbon\Carbon::parse($currentDate)->addDay()->format('Y-m-d') }}" 
            style="color: #9ca3af !important; text-decoration: none !important; font-size: 14px !important; font-weight: bold !important; display: flex !important; align-items: center !important; gap: 6px !important; transition: color 0.15s !important;"
            onmouseover="this.style.color='#000000'" onmouseout="this.style.color='#9ca3af'">
            翌日 <span>→</span>
        </a>
    </div>

    <!-- 【公式デザイン】角丸・柔らかな影付きのスタイリッシュテーブル -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-md overflow-hidden" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-gray-50/50 border-b border-gray-200">
                <tr style="height: 60px !important;">
                    <th style="padding: 0 40px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important;">名前</th>
                    <th style="padding: 0 32px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important;">出勤</th>
                    <th style="padding: 0 32px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important;">退勤</th>
                    <th style="padding: 0 32px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important;">休憩</th>
                    <th style="padding: 0 32px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important;">合計</th>
                    <th style="padding: 0 40px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important;">詳細</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-600 font-normal">
                @forelse($attendances as $attendance)
                    <tr class="hover:bg-gray-50/80 transition duration-150" style="height: 60px !important;">
                        <td style="padding: 0 40px !important; font-size: 15px !important; font-weight: 500 !important; color: #111827 !important;">{{ $attendance->user->name }}</td>
                        <td style="padding: 0 32px !important; font-size: 15px !important; letter-spacing: 0.05em !important;">{{ substr($attendance->clock_in, 0, 5) }}</td>
                        <td style="padding: 0 32px !important; font-size: 15px !important; letter-spacing: 0.05em !important;">
                            {{ $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '--:--' }}
                        </td>
                        <td style="padding: 0 32px !important; font-size: 15px !important; color: #6b7280 !important; font-weight: 500 !important;">1:00</td>
                        <td style="padding: 0 32px !important; font-size: 15px !important; color: #111827 !important; font-weight: 600 !important;">8:00</td>
                        <td style="padding: 0 40px !important; text-align: center !important;">
                            <a href="/admin/attendance/{{ $attendance->id }}" 
                                class="text-gray-900 font-bold hover:text-blue-600 transition underline underline-offset-4" style="font-size: 15px !important; color: #000000 !important;">
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr style="height: 240px !important;">
                        <td colspan="6" style="text-align: center !important; vertical-align: middle !important; color: #6b7280 !important; font-weight: 500 !important; font-size: 16px !important; letter-spacing: 0.05em !important;">
                            この日の勤怠データはまだ登録されていません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
