@extends('layouts.app')

@section('content')
<div class="max-w-[1200px] mx-auto py-6 select-none" style="font-family: sans-serif !important;">

    <!-- ページタイトル（公式見本通りの配置・縦線） -->
    <div class="flex items-center mb-6 pl-1" style="gap: 16px !important;">
        <div style="width: 4px !important; height: 28px !important; background-color: #000000 !important; border-radius: 9999px !important;"></div>
        <h2 class="text-2xl font-bold tracking-wider text-gray-900" style="margin: 0 !important; font-size: 24px !important;">
            {{ \Carbon\Carbon::parse($currentDate)->format('Y年n月j日') }}の勤怠
        </h2>
    </div>

    <!-- 【公式見本完全一致】日付コントロールバー（前日・翌日・カレンダーアイコン付きホワイトカード） -->
    <div class="flex justify-center mb-8">
        <div class="flex items-center bg-white border border-gray-200 shadow-sm px-4 py-1" style="height: 46px !important; border-radius: 6px !important; width: 480px !important; justify-content: space-between !important;">
            <!-- 前日ボタン -->
            <a href="?date={{ \Carbon\Carbon::parse($currentDate)->subDay()->format('Y-m-d') }}" 
               class="text-gray-400 font-bold hover:text-gray-600 transition" 
               style="text-decoration: none !important; font-size: 13px !important; display: flex; align-items: center; gap: 4px;">
               <span>←</span> 前日
            </a>
            
            <!-- カレンダーアイコン ＆ 年月日表示本体 -->
            <div class="flex items-center text-gray-900 font-bold" style="gap: 8px !important; font-size: 15px !important; tracking-wide !important;">
                <!-- カレンダーの絵文字アイコン -->
                <span style="font-size: 16px !important; color: #4b5563 !important; position: relative; top: -1px;">📅</span>
                <span>{{ \Carbon\Carbon::parse($currentDate)->format('Y/m/d') }}</span>
            </div>
            
            <!-- 翌日ボタン -->
            <a href="?date={{ \Carbon\Carbon::parse($currentDate)->addDay()->format('Y-m-d') }}" 
               class="text-gray-400 font-bold hover:text-gray-600 transition" 
               style="text-decoration: none !important; font-size: 13px !important; display: flex; align-items: center; gap: 4px;">
               翌日 <span>→</span>
            </a>
        </div>
    </div>

    <!-- 【公式デザイン】角丸・柔らかな影付きのスタイリッシュテーブル -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-md overflow-hidden max-w-[900px] mx-auto" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
        <table class="w-full text-left text-sm border-collapse">
            <!-- 見本通りのヘッダー項目 -->
            <thead class="bg-gray-50/50 border-b border-gray-200">
                <tr style="height: 52px !important;">
                    <th style="padding: 0 24px !important; font-size: 13px !important; font-weight: bold !important; color: #9ca3af !important; width: 22% !important;">名前</th>
                    <th style="padding: 0 24px !important; font-size: 13px !important; font-weight: bold !important; color: #9ca3af !important; width: 16% !important;">出勤</th>
                    <th style="padding: 0 24px !important; font-size: 13px !important; font-weight: bold !important; color: #9ca3af !important; width: 16% !important;">退勤</th>
                    <th style="padding: 0 24px !important; font-size: 13px !important; font-weight: bold !important; color: #9ca3af !important; width: 16% !important;">休憩</th>
                    <th style="padding: 0 24px !important; font-size: 13px !important; font-weight: bold !important; color: #9ca3af !important; width: 16% !important;">合計</th>
                    <th style="padding: 0 24px !important; font-size: 13px !important; font-weight: bold !important; color: #9ca3af !important; text-align: center !important; width: 14% !important;">詳細</th>
                </tr>
            </thead>
            <!-- データの行 -->
            <tbody class="divide-y divide-gray-100 text-gray-700 font-normal">
                @forelse($records as $rec)
                    <tr class="hover:bg-gray-50/50 transition duration-150" style="height: 52px !important;">
                        <!-- 名前 -->
                        <td style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #111827 !important;">
                            {{ $rec['name'] }}
                        </td>
                        <!-- 出勤 -->
                        <td style="padding: 0 24px !important; font-size: 14px !important;">
                            {{ $rec['clock_in']  }}
                        </td>
                        <!-- 退勤 -->
                        <td style="padding: 0 24px !important; font-size: 14px !important;">
                            {{ $rec['clock_out'] }}
                        </td>
                        <!-- 休憩 -->
                        <td style="padding: 0 24px !important; font-size: 14px !important;">
                            {{ $rec['break_time'] }}
                        </td>
                        <!-- 合計（実労働時間：定時の8時間など） -->
                        <td style="padding: 0 24px !important; font-size: 14px !important; font-weight: 600 !important;">
                            {{ $rec['work_time'] }}
                        </td>
                        <!-- 詳細リンク -->
                        <td style="padding: 0 24px !important; text-align: center !important;">
                            @if($rec['attendance_id'])
                                <a href="/admin/attendance/{{ $rec['attendance_id'] }}" 
                                   class="text-gray-900 font-bold hover:text-blue-600 transition underline underline-offset-4" style="font-size: 14px !important; color: #000000 !important;">
                                    詳細
                                </a>
                            @else
                                <span class="text-gray-300 font-medium select-none" style="font-size: 14px !important;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr style="height: 200px !important;">
                        <td colspan="6" style="text-align: center !important; vertical-align: middle !important; color: #6b7280 !important; font-weight: 500 !important; font-size: 15px !important; letter-spacing: 0.05em !important;">
                            本日の打刻データは登録されていません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
