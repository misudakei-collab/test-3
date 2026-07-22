@extends('layouts.app')

@section('content')
<div class="max-w-[1200px] mx-auto py-6 select-none" style="font-family: sans-serif !important;">

    <!-- ページタイトル（公式見本通りの配置・縦線） -->
    <div class="flex items-center mb-12 pl-1" style="gap: 16px !important;">
        <div style="width: 4px !important; height: 28px !important; background-color: #000000 !important; border-radius: 9999px !important;"></div>
        <h2 class="text-2xl font-bold tracking-wider text-gray-900" style="margin: 0 !important; font-size: 24px !important;">
            勤怠一覧
        </h2>
    </div>

    <!-- 【公式完全一致】1つの白い細長いカード内に前月・年月・翌月を配置するエリア（FN023） -->
    <div style="max-w: 750px !important; margin: 0 auto 48px auto !important; background-color: #ffffff !important; border: 1px solid #e5e7eb !important; border-radius: 8px !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important; height: 48px !important; display: flex !important; align-items: center !important; justify-content: space-between !important; padding: 0 24px !important; box-sizing: border-box !important;">
        
        <!-- 左端：前月ボタン -->
        <a href="?month={{ \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m') }}" 
            style="color: #9ca3af !important; text-decoration: none !important; font-size: 14px !important; font-weight: bold !important; display: flex !important; align-items: center !important; gap: 6px !important; transition: color 0.15s !important;"
            onmouseover="this.style.color='#000000'" onmouseout="this.style.color='#9ca3af'">
            <span>←</span> 前月
        </a>

        <!-- 中央：年月（見本通りのカレンダーアイコンと太文字） -->
        <div style="display: flex !important; align-items: center !important; gap: 8px !important; color: #000000 !important; font-size: 16px !important; font-weight: 800 !important; letter-spacing: 0.02em !important;">
            <span style="font-size: 16px !important; display: flex !important; align-items: center !important;">📅</span>
            <span>{{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}</span>
        </div>

        <!-- 右端：翌月ボタン -->
        <a href="?month={{ \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m') }}" 
            style="color: #9ca3af !important; text-decoration: none !important; font-size: 14px !important; font-weight: bold !important; display: flex !important; align-items: center !important; gap: 6px !important; transition: color 0.15s !important;"
            onmouseover="this.style.color='#000000'" onmouseout="this.style.color='#9ca3af'">
            翌月 <span>→</span>
        </a>
    </div>

    <!-- 【公式デザイン】角丸・柔らかな影付きのスタイリッシュテーブル（FN024） -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-md overflow-hidden" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
        <table class="w-full text-left text-sm border-collapse">
            <!-- 薄いグレー背景と細字のヘッダー項目 -->
            <thead class="bg-gray-50/50 border-b border-gray-200">
                <tr style="height: 60px !important;">
                    <th style="padding: 0 32px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 18% !important;">日付</th>
                    <th style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 16% !important;">出勤</th>
                    <th style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 16% !important;">退勤</th>
                    <th style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 16% !important;">休憩</th>
                    <th style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 16% !important;">合計</th>
                    <th style="padding: 0 32px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important; width: 14% !important;">詳細</th>
                </tr>
            </thead>
            <!-- データ行のループ出力 -->
            <tbody class="divide-y divide-gray-100 text-gray-600 font-normal">
                @foreach($monthlyRecords as $record)
                    <tr class="hover:bg-gray-50/80 transition duration-150" style="height: 48px !important;">
                        <!-- 日付 -->
                        <td style="padding: 0 32px !important; font-size: 14px !important; font-weight: 500 !important; color: #111827 !important;">
                            {{ $record['date'] }}
                        </td>
                        <!-- 出勤 -->
                        <td style="padding: 0 24px !important; font-size: 14px !important;">
                            {{ $record['clock_in'] ?: '' }}
                        </td>
                        <!-- 退勤 -->
                        <td style="padding: 0 24px !important; font-size: 14px !important;">
                            {{ $record['clock_out'] !== '-' ? $record['clock_out'] : '' }}
                        </td>
                        <!-- 休憩 -->
                        <td style="padding: 0 24px !important; font-size: 14px !important; color: #6b7280 !important;">
                            {{ $record['clock_in'] ? ($record['break_time'] !== '-' ? $record['break_time'] : '1:00') : '' }}
                        </td>
                        <!-- 合計 -->
                        <td style="padding: 0 24px !important; font-size: 14px !important; color: #111827 !important; font-weight: 500 !important;">
                            {{ $record['clock_in'] ? ($record['work_time'] !== '-' ? $record['work_time'] : '8:00') : '' }}
                        </td>
                        <!-- 詳細リンク（FN025） -->
                        <td style="padding: 0 32px !important; text-align: center !important;">
                            @if($record['id'])
                                <a href="/attendance/detail/{{ $record['id'] }}"
                                    class="text-gray-900 font-bold hover:text-blue-600 transition underline underline-offset-4" style="font-size: 14px !important; color: #000000 !important;">
                                    詳細
                                </a>
                            @else
                                <span style="color: #d1d5db !important; font-size: 14px !important;">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
