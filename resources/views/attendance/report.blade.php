@extends('layouts.app')

@section('content')
<div class="max-w-[1200px] mx-auto py-6 select-none" style="font-family: sans-serif !important;">

    
    <div class="flex items-center mb-2 pl-1" style="gap: 16px !important;">
        <div style="width: 4px !important; height: 28px !important; background-color: #000000 !important; border-radius: 9999px !important;"></div>
        <h1 class="text-2xl font-bold tracking-wider text-gray-900" style="margin: 0 !important; font-size: 24px !important;">
            マイ勤怠レポート
        </h1>
    </div>
    <p class="text-xs text-gray-500 font-medium mb-10 pl-5">過去6ヶ月の勤怠データから集計しています。</p>

    
    <div class="mb-12">
        <h3 class="text-base font-bold text-gray-900 mb-4 pl-1">基本サマリー</h3>
        <div class="grid grid-cols-3 gap-x-6">
            
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
                <span class="block text-xs font-bold text-gray-400 mb-2">総労働時間</span>
                <span class="text-2xl font-black text-gray-900 tracking-wide">{{ $reportData['summary']['total_work'] ?? '0h 0m' }}</span>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
                <span class="block text-xs font-bold text-gray-400 mb-2">総残業時間</span>
                <span class="text-2xl font-black text-gray-900 tracking-wide">{{ $reportData['summary']['total_overtime'] ?? '0h 0m' }}</span>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
                <span class="block text-xs font-bold text-gray-400 mb-2">平均労働時間 / 日</span>
                <span class="text-2xl font-black text-gray-900 tracking-wide">{{ $reportData['summary']['average_work'] ?? '0h 0m' }}</span>
            </div>
        </div>
    </div>

    
    <div class="mb-12">
        <h3 class="text-base font-bold text-gray-900 mb-4 pl-1">月次推移 (過去6ヶ月)</h3>
        <div class="bg-white border border-gray-200 rounded-xl shadow-md overflow-hidden" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-gray-50/50 border-b border-gray-200">
                    <tr style="height: 50px !important;">
                        <th style="padding: 0 32px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 34% !important;">月</th>
                        <th style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 33% !important;">労働時間</th>
                        <th style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 33% !important;">残業時間</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700 font-normal">
                    @foreach($reportData['monthly_trends'] as $trend)
                        <tr class="hover:bg-gray-50/50 transition duration-150" style="height: 48px !important;">
                            <td style="padding: 0 32px !important; font-size: 14px !important; font-weight: 600 !important; color: #4b5563 !important;">
                                {{ $trend['month'] }}
                            </td>
                            <td style="padding: 0 24px !important; font-size: 14px !important; font-weight: 700 !important; color: #111827 !important;">
                                {{ $trend['work'] }}
                            </td>
                            <td style="padding: 0 24px !important; font-size: 14px !important; font-weight: 700 !important; color: #111827 !important;">
                                {{ $trend['overtime'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    
    <div>
        <h3 class="text-base font-bold text-gray-900 mb-1 pl-1">今月の異常検知</h3>
        <p class="text-[11px] text-gray-400 font-semibold mb-4 pl-1" style="transform: scale(0.95); transform-origin: left center;">
            基準：遅刻 09:00超 / 早退 18:00前 / 長時間労働は1日10時間超
        </p>
        <div class="grid grid-cols-3 gap-x-6">
            
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md flex flex-col justify-between" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important; height: 110px !important;">
                <span class="block text-xs font-bold text-gray-400">遅刻回数</span>
                <div class="flex items-baseline" style="gap: 4px !important;">
                    <span class="text-2xl font-black text-gray-900">{{ $reportData['anomalies']['late_count'] ?? 0 }}</span>
                    <span class="text-xs font-bold text-gray-500">回</span>
                </div>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md flex flex-col justify-between" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important; height: 110px !important;">
                <span class="block text-xs font-bold text-gray-400">早退回数</span>
                <div class="flex items-baseline" style="gap: 4px !important;">
                    <span class="text-2xl font-black text-gray-900">{{ $reportData['anomalies']['early_leave_count'] ?? 0 }}</span>
                    <span class="text-xs font-bold text-gray-500">回</span>
                </div>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-md flex flex-col justify-between" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important; height: 110px !important;">
                <span class="block text-xs font-bold text-gray-400">長時間労働日数</span>
                <div class="flex items-baseline" style="gap: 4px !important;">
                    <span class="text-2xl font-black text-gray-900">{{ $reportData['anomalies']['overwork_count'] ?? 0 }}</span>
                    <span class="text-xs font-bold text-gray-500">日</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
