@extends('layouts.app')

@section('content')
<div class="max-w-[1200px] mx-auto py-6 select-none" style="font-family: sans-serif !important;">

    <!-- ページタイトル -->
    <div class="flex items-center mb-6 pl-1" style="gap: 16px !important;">
        <div style="width: 4px !important; height: 28px !important; background-color: #000000 !important; border-radius: 9999px !important;"></div>
        <h1 class="text-2xl font-bold tracking-wider text-gray-900" style="margin: 0 !important; font-size: 24px !important;">
            {{ $user->name }}さんの勤怠
        </h1>
    </div>


    <!-- 日付コントロールバー（前月・翌月選択型） -->
    <div class="flex justify-center mb-8">
        <div class="flex items-center bg-white border border-gray-200 shadow-sm px-4 py-1" style="height: 46px !important; border-radius: 6px !important; width: 480px !important; justify-content: space-between !important;">
            <a href="?month={{ \Carbon\Carbon::parse($currentMonth)->subMonth()->format('Y-m') }}" 
               class="text-gray-400 font-bold hover:text-gray-600 transition" style="text-decoration: none !important; font-size: 13px !important;">
               ← 前月
            </a>
            <div class="flex items-center text-gray-900 font-bold" style="gap: 8px !important; font-size: 15px !important;">
                <span>📅 {{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}</span>
            </div>
            <a href="?month={{ \Carbon\Carbon::parse($currentMonth)->addMonth()->format('Y-m') }}" 
               class="text-gray-400 font-bold hover:text-gray-600 transition" style="text-decoration: none !important; font-size: 13px !important;">
               翌月 →
            </a>
        </div>
    </div>

    <!-- 角丸・シャドウ付きカードテーブル（月次一覧専用） -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-md overflow-hidden max-w-[900px] mx-auto" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-gray-50/50 border-b border-gray-200">
                <tr style="height: 52px !important;">
                    <th style="padding: 0 24px !important; font-size: 13px !important; font-weight: bold !important; color: #9ca3af !important; width: 20% !important;">日付</th>
                    <th style="padding: 0 24px !important; font-size: 13px !important; font-weight: bold !important; color: #9ca3af !important; width: 16% !important;">出勤</th>
                    <th style="padding: 0 24px !important; font-size: 13px !important; font-weight: bold !important; color: #9ca3af !important; width: 16% !important;">退勤</th>
                    <th style="padding: 0 24px !important; font-size: 13px !important; font-weight: bold !important; color: #9ca3af !important; width: 16% !important;">休憩</th>
                    <th style="padding: 0 24px !important; font-size: 13px !important; font-weight: bold !important; color: #9ca3af !important; width: 16% !important;">合計</th>
                    <th style="padding: 0 24px !important; font-size: 13px !important; font-weight: bold !important; color: #9ca3af !important; text-align: center !important; width: 16% !important;">詳細</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700 font-normal">
                @forelse($monthlyRecords as $rec)
                    <tr class="hover:bg-gray-50/50 transition duration-150" style="height: 52px !important;">
                        <!-- 日付 (例: 02/01(日)) -->
                        <td style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #111827 !important;">
                            {{ $rec['date'] }}
                        </td>
                        <!-- 出勤 -->
                        <td style="padding: 0 24px !important; font-size: 14px !important;">
                            {{ $rec['clock_in'] ?: '-' }}
                        </td>
                        <!-- 退勤 -->
                        <td style="padding: 0 24px !important; font-size: 14px !important;">
                            {{ $rec['clock_out'] ?: '-' }}
                        </td>
                        <!-- 休憩  -->
                        <td style="padding: 0 24px !important; font-size: 14px !important;">
                            {{ $rec['break_time'] }}
                        </td>
                        <!-- 合計 -->
                        <td style="padding: 0 24px !important; font-size: 14px !important; font-weight: 600 !important; color: #111827 !important;">
                            {{ $rec['work_time'] }}
                        </td>
                        <!-- 詳細リンク -->
                        <td style="padding: 0 24px !important; text-align: center !important;">
                            @if($rec['id'])
                                <a href="/admin/attendance/{{ $rec['id'] }}" 
                                   class="text-gray-900 font-bold hover:text-blue-600 transition underline underline-offset-4" style="font-size: 14px !important;">
                                    詳細
                                </a>
                            @else
                                <span class="text-gray-300 font-medium select-none" style="font-size: 14px !important;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr style="height: 200px !important;">
                        <td colspan="6" style="text-align: center !important; vertical-align: middle !important; color: #6b7280 !important; font-size: 15px !important;">
                            勤怠データが登録されていません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 右下に配置された黒四角型のCSV出力ボタン -->
    <div class="flex justify-end mt-6 max-w-[900px] mx-auto">
        <form action="/admin/attendance/staff/{{ $user->id }}/csv" method="POST" style="margin: 0 !important; padding: 0 !important;">
            @csrf
            <input type="hidden" name="month" value="{{ $currentMonth }}">
            <button type="submit" class="bg-black text-white text-sm font-bold flex items-center justify-center hover:bg-gray-800 transition tracking-wider border-none cursor-pointer" 
                    style="width: 110px !important; height: 38px !important; border-radius: 4px !important; letter-spacing: 0.05em !important;">
                CSV出力
            </button>
        </form>
    </div>

</div>
@endsection

