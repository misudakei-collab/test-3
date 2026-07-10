@extends('layouts.app')

@section('content')
<div class="max-w-[1000px] mx-auto py-6">
    <div class="flex items-center justify-between border-b pb-4 mb-8 select-none">
        <h2 class="text-xl font-bold text-gray-900 tracking-wider">{{ $user->name }} さんの月次勤怠</h2>
        
        <!-- ★【FN045】CSVダウンロードボタン -->
        <a href="/admin/attendance/staff/{{ $user->id }}/csv?month={{ $currentMonth }}" 
            class="border border-black text-black px-4 py-2 font-bold text-xs rounded-sm hover:bg-gray-50 transition">
            CSVダウンロード
        </a>
    </div>

    <!-- 【FN044】月表示変更機能 -->
    <div class="flex items-center justify-center space-x-8 mb-10 select-none">
        <a href="?month={{ \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m') }}" 
            class="text-blue-500 text-sm font-bold border border-gray-300 px-4 py-1.5 rounded-sm hover:bg-gray-50 transition">&lt; 前月</a>
        <h3 class="text-xl font-bold tracking-wider text-gray-900">
            {{ \Carbon\Carbon::parse($currentMonth)->format('Y年m月') }}
        </h3>
        <a href="?month={{ \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m') }}" 
            class="text-blue-500 text-sm font-bold border border-gray-300 px-4 py-1.5 rounded-sm hover:bg-gray-50 transition">翌月 &gt;</a>
    </div>

    <!-- 【FN043】勤怠情報取得機能テーブル -->
    <div class="border border-gray-300 rounded-sm overflow-hidden">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-black text-white text-xs font-bold uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 border-r border-gray-700">日付</th>
                    <th class="px-6 py-4 border-r border-gray-700">出勤時間</th>
                    <th class="px-6 py-4 border-r border-gray-700">退勤時間</th>
                    <th class="px-6 py-4 border-r border-gray-700">休憩回数</th>
                    <th class="px-6 py-4 text-center">詳細</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-gray-700 font-medium">
                @foreach($monthlyRecords as $record)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3.5 bg-gray-50 border-r border-gray-200 font-bold text-gray-900">{{ $record['date'] }}</td>
                        <td class="px-6 py-3.5 border-r border-gray-200">{{ $record['clock_in'] }}</td>
                        <td class="px-6 py-3.5 border-r border-gray-200">{{ $record['clock_out'] }}</td>
                        <td class="px-6 py-3.5 border-r border-gray-200">{{ $record['break_count'] > 0 ? $record['break_count'].'回' : '' }}</td>
                        <td class="px-6 py-3.5 text-center">
                            @if($record['id'])
                                <!-- 【FN046】詳細への遷移 -->
                                <a href="/admin/attendance/{{ $record['id'] }}" class="text-blue-500 font-bold underline hover:text-blue-700">詳細</a>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
