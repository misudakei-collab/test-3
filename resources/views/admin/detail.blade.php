@extends('layouts.app')

@section('content')
<div class="max-w-[1200px] mx-auto py-6 select-none" style="font-family: sans-serif !important;">

    <!-- ページタイトル（公式見本通りの配置・縦線） -->
    <div class="flex items-center mb-10 pl-1" style="gap: 16px !important;">
        <div style="width: 4px !important; height: 28px !important; background-color: #000000 !important; border-radius: 9999px !important;"></div>
        <h1 class="text-2xl font-bold tracking-wider text-gray-900" style="margin: 0 !important; font-size: 24px !important;">
            勤怠詳細
        </h1>
    </div>


    <!-- 修正成功時のアラート表示 -->
    @if(session('message'))
        <div class="mb-6 p-4 bg-green-50 text-green-700 font-bold border border-green-200 rounded-lg text-sm text-center max-w-[900px] mx-auto">
            {{ session('message') }}
        </div>
    @endif

    <form method="POST" action="/admin/attendance/{{ $attendance->id }}" class="max-w-[900px] mx-auto" novalidate>
        @csrf

        <div class="bg-white border border-gray-200 rounded-xl shadow-md overflow-hidden" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
            <table class="w-full text-left text-sm border-collapse">
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    
                    <!-- 1. 名前 -->
                    <tr style="height: 64px !important;">
                        <td style="width: 25% !important; padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">名前</td>
                        <td style="width: 75% !important; padding-left: 140px !important; font-size: 15px !important; font-weight: 500 !important; color: #111827 !important;">
                            {{ $attendance->user->name }}
                        </td>
                    </tr>

                    <!-- 2. 日付） -->
                    <tr style="height: 64px !important;">
                        <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">日付</td>
                        <td style="padding-left: 100px !important; font-size: 15px !important; font-weight: 500 !important; color: #111827 !important;">
                            <div class="flex items-center" style="gap: 60px !important;">
                                <span>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                                <span>{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                            </div>
                        </td>
                    </tr>

                    <!-- 3. 出勤・退勤 -->
                    <tr style="height: 72px !important;">
                        <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">出勤・退勤</td>
                        <td style="padding-left: 100px !important;">
                            <div class="flex items-center text-gray-400 font-bold" style="gap: 24px !important;">
                                <input type="text" name="clock_in" value="{{ old('clock_in', substr($attendance->clock_in, 0, 5)) }}" 
                                    class="h-10 px-3 border border-gray-300 rounded-md focus:outline-none focus:border-black text-center font-medium text-gray-900" style="width: 110px !important; font-size: 15px !important; border-radius: 4px !important;">
                                <span style="font-size: 16px !important; font-weight: normal !important; color: #111827 !important;">～</span>
                                <input type="text" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '') }}" 
                                    class="h-10 px-3 border border-gray-300 rounded-md focus:outline-none focus:border-black text-center font-medium text-gray-900" style="width: 110px !important; font-size: 15px !important; border-radius: 4px !important;">
                            </div>
                            @error('clock_in') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                            @error('clock_out') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </td>
                    </tr>

                    <!-- 4. 休憩 -->
                    @foreach($attendance->breakTimes as $index => $break)
                        <tr style="height: 72px !important;">
                            <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">
                                休憩{{ $index > 0 ? $index + 1 : '' }}
                            </td>
                            <td style="padding-left: 100px !important;">
                                <div class="flex items-center text-gray-400 font-bold" style="gap: 24px !important;">
                                    <input type="text" name="breaks[{{ $index }}][break_in]" value="{{ substr($break->break_in, 0, 5) }}" 
                                        class="h-10 px-3 border border-gray-300 rounded-md text-center font-medium text-gray-900 focus:outline-none" style="width: 110px !important; font-size: 15px !important; border-radius: 4px !important;">
                                    <span style="font-size: 16px !important; font-weight: normal !important; color: #111827 !important;">～</span>
                                    <input type="text" name="breaks[{{ $index }}][break_out]" value="{{ $break->break_out ? substr($break->break_out, 0, 5) : '' }}" 
                                        class="h-10 px-3 border border-gray-300 rounded-md text-center font-medium text-gray-900 focus:outline-none" style="width: 110px !important; font-size: 15px !important; border-radius: 4px !important;">
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    <!-- 5. 休憩2 -->
                    @php $nextIndex = $attendance->breakTimes->count(); @endphp
                    <tr style="height: 72px !important;">
                        <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">
                            休憩{{ $nextIndex > 0 ? $nextIndex + 1 : '2' }}
                        </td>
                        <td style="padding-left: 100px !important;">
                            <div class="flex items-center text-gray-400 font-bold" style="gap: 24px !important;">
                                <input type="text" name="breaks[{{ $nextIndex }}][break_in]" placeholder=""
                                    class="h-10 px-3 border border-gray-300 rounded-md text-center font-medium text-gray-900 focus:outline-none" style="width: 110px !important; font-size: 15px !important; border-radius: 4px !important;">
                                <span style="font-size: 16px !important; font-weight: normal !important; color: #111827 !important;">～</span>
                                <input type="text" name="breaks[{{ $nextIndex }}][break_out]" placeholder=""
                                    class="h-10 px-3 border border-gray-300 rounded-md text-center font-medium text-gray-900 focus:outline-none" style="width: 110px !important; font-size: 15px !important; border-radius: 4px !important;">
                            </div>
                        </td>
                    </tr>

                    <!-- 6. 備考 -->
                    <tr style="height: 84px !important;">
                        <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important; vertical-align: middle !important;">備考</td>
                        <td style="padding: 12px 0 12px 100px !important; vertical-align: middle !important;">
                            <textarea name="remarks" rows="1" 
                                class="p-2 border border-gray-300 focus:outline-none focus:border-black font-medium text-gray-900 text-sm resize-none" style="width: 320px !important; height: 38px !important; border-radius: 4px !important; box-sizing: border-box !important;"></textarea>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        <!-- 右下に配置された修正ボタン -->
        <div class="flex justify-end mt-8">
            <button type="submit" class="bg-black text-white text-sm font-bold hover:bg-gray-800 transition tracking-wider" style="width: 110px !important; height: 38px !important; border-radius: 4px !important; letter-spacing: 0.1em !important;">
                修正
            </button>
        </div>
    </form>
</div>
@endsection

