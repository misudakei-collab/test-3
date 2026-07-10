@extends('layouts.app')

@section('content')
<div class="max-w-[1000px] mx-auto py-6">

    <!-- 【FN024、FN025】前月・次月切り替えナビゲーション -->
    <div class="flex items-center justify-center space-x-8 mb-10 select-none">
        <a href="?month={{ \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m') }}" 
            class="text-blue-500 text-sm font-bold border border-gray-300 px-4 py-1.5 rounded-sm hover:bg-gray-50 transition">&lt; 前月</a>
        <h3 class="text-xl font-bold tracking-wider text-gray-900">
            {{ \Carbon\Carbon::parse($currentMonth)->format('Y年m月') }}
        </h3>
        <a href="?month={{ \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m') }}" 
            class="text-blue-500 text-sm font-bold border border-gray-300 px-4 py-1.5 rounded-sm hover:bg-gray-50 transition">次月 &gt;</a>
    </div>

    <!-- 勤怠履歴データテーブル -->
    <div class="border border-gray-300 rounded-sm overflow-hidden">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-black text-white text-xs font-bold uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 border-r border-gray-700">日付</th>
                    <th class="px-6 py-4 border-r border-gray-700">出勤</th>
                    <th class="px-6 py-4 border-r border-gray-700">退勤</th>
                    <th class="px-6 py-4 border-r border-gray-700">休憩</th>
                    <th class="px-6 py-4 border-r border-gray-700">滞在</th>
                    <th class="px-6 py-4 text-center">詳細</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-gray-700 font-medium">
                @foreach($monthlyRecords as $record)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-3.5 bg-gray-50 border-r border-gray-200 font-bold text-gray-900">{{ $record['date'] }}</td>
                        <td class="px-6 py-3.5 border-r border-gray-200">{{ $record['clock_in'] }}</td>
                        <td class="px-6 py-3.5 border-r border-gray-200">{{ $record['clock_out'] }}</td>
                        <td class="px-6 py-3.5 border-r border-gray-200">{{ $record['break_time'] }}</td>
                        <td class="px-6 py-3.5 border-r border-gray-200 font-semibold text-black">{{ $record['work_time'] }}</td>
                        <td class="px-6 py-3.5 text-center">
                            @if($record['id'])
                                <a href="/attendance/detail/{{ $record['id'] }}" class="text-blue-500 font-bold underline hover:text-blue-700">詳細</a>
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
