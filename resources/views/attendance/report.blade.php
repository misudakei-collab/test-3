@extends('layouts.app')

@section('content')
<div class="max-w-[1000px] mx-auto py-4 select-none">
    
    <!-- タイトル部 -->
    <h2 class="text-xl font-bold text-gray-900 mb-2 tracking-wider">マイ勤怠レポート</h2>
    <p class="text-xs text-gray-800 font-bold mb-8">過去６ヶ月の勤怠データから集計しています。</p>

    <!-- 基本サマリーエリア -->
    <div class="mb-10">
        <h3 class="text-sm font-bold text-gray-900 mb-4">基本サマリー</h3>
        <div class="grid grid-cols-3 gap-x-6">
            <div class="bg-gray-50 border border-gray-200 rounded-sm p-5 shadow-sm">
                <span class="block text-[10px] font-bold text-gray-400 mb-2">総労働時間</span>
                <span class="text-xl font-bold text-gray-900 tracking-wide">{{ $reportData['summary']['total_work'] }}</span>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-sm p-5 shadow-sm">
                <span class="block text-[10px] font-bold text-gray-400 mb-2">総残業時間</span>
                <span class="text-xl font-bold text-gray-900 tracking-wide">{{ $reportData['summary']['total_overtime'] }}</span>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-sm p-5 shadow-sm">
                <span class="block text-[10px] font-bold text-gray-400 mb-2">平均労働時間 / 日</span>
                <span class="text-xl font-bold text-gray-900 tracking-wide">{{ $reportData['summary']['average_work'] }}</span>
            </div>
        </div>
    </div>

    <!-- 月次推移エリア（画像通りの横幅広テーブルスタイル） -->
    <div class="mb-10">
        <h3 class="text-sm font-bold text-gray-900 mb-4">月次推移（過去６ヶ月）</h3>
        <div class="border-t border-b border-gray-200 overflow-hidden">
            <table class="w-full text-left text-xs font-bold">
                <thead>
                    <tr class="text-gray-900 border-b border-gray-200">
                        <th class="py-4 w-1/4">月</th>
                        <th class="py-4 w-1/2">労働時間</th>
                        <th class="py-4 w-1/4">残業時間</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-900">
                    @foreach($reportData['monthly_trends'] as $trend)
                        <tr>
                            <td class="py-4 text-gray-500 font-bold">{{ $trend['month'] }}</td>
                            <td class="py-4">{{ $trend['work'] }}</td>
                            <td class="py-4">{{ $trend['overtime'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 今月の異常検知エリア -->
    <div>
        <h3 class="text-sm font-bold text-gray-900 mb-2">今月の異常検知</h3>
        <p class="text-[9px] text-gray-400 font-bold mb-4">基準：始業 09:00 / 終業 18:00 / 長時間労働は1日10時間超</p>
        
        <div class="grid grid-cols-3 gap-x-6">
            <div class="bg-gray-50 border border-gray-200 rounded-sm p-5 shadow-sm flex flex-col justify-between h-24">
                <span class="block text-[10px] font-bold text-gray-400">遅刻回数</span>
                <span class="text-xl font-bold text-gray-900 tracking-wide">{{ $reportData['anomalies']['late_count'] }} <span class="text-sm font-bold">回</span></span>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-sm p-5 shadow-sm flex flex-col justify-between h-24">
                <span class="block text-[10px] font-bold text-gray-400">早退回数</span>
                <span class="text-xl font-bold text-gray-900 tracking-wide">{{ $reportData['anomalies']['early_leave_count'] }} <span class="text-sm font-bold">回</span></span>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-sm p-5 shadow-sm flex flex-col justify-between h-24">
                <span class="block text-[10px] font-bold text-gray-400">長時間労働日数</span>
                <span class="text-xl font-bold text-gray-900 tracking-wide">{{ $reportData['anomalies']['overwork_count'] }} <span class="text-sm font-bold">日</span></span>
            </div>
        </div>
    </div>

</div>
@endsection
