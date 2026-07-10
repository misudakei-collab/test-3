@extends('layouts.app')

@section('content')
<div class="max-w-[1000px] mx-auto py-6 select-none">
    <!-- 【FN035】日時変更機能 -->
    <div class="flex items-center justify-center space-x-8 mb-10">
        <a href="?date={{ \Carbon\Carbon::parse($currentDate)->subDay()->format('Y-m-d') }}" class="text-blue-500 text-sm font-bold border border-gray-300 px-4 py-1.5 rounded-sm hover:bg-gray-50 transition">&lt; 前日</a>
        <h2 class="text-xl font-bold tracking-wider text-gray-900">{{ \Carbon\Carbon::parse($currentDate)->format('Y年m月d日') }}</h2>
        <a href="?date={{ \Carbon\Carbon::parse($currentDate)->addDay()->format('Y-m-d') }}" class="text-blue-500 text-sm font-bold border border-gray-300 px-4 py-1.5 rounded-sm hover:bg-gray-50 transition">翌日 &gt;</a>
    </div>

    <!-- 【FN034】勤怠情報取得機能のテーブル -->
    <div class="border border-gray-300 rounded-sm overflow-hidden">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-black text-white text-xs font-bold uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 border-r border-gray-700">スタッフ名</th>
                    <th class="px-6 py-4 border-r border-gray-700">出勤時間</th>
                    <th class="px-6 py-4 border-r border-gray-700">退勤時間</th>
                    <th class="px-6 py-4 border-r border-gray-700">休憩回数</th>
                    <th class="px-6 py-4 text-center">詳細</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-gray-700 font-medium">
                @forelse($attendances as $attendance)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 border-r border-gray-200 font-bold text-gray-900">{{ $attendance->user->name }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">{{ substr($attendance->clock_in, 0, 5) }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">{{ $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '-' }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">{{ $attendance->breakTimes->count() }}回</td>
                        <td class="px-6 py-4 text-center">
                            <!-- 【FN036】詳細遷移機能 -->
                            <a href="/admin/attendance/{{ $attendance->id }}" class="text-blue-500 font-bold underline hover:text-blue-700">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 font-medium">この日の勤怠データはありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
