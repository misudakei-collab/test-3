@extends('layouts.app')

@section('content')
<div class="max-w-[600px] mx-auto pt-10">
    <h2 class="text-xl font-bold text-gray-900 mb-2 tracking-wider text-center">勤怠詳細（管理者用）</h2>
    <p class="text-xs text-gray-500 text-center mb-10">対象スタッフ: <span class="font-bold text-black">{{ $attendance->user->name }}</span> (対象日: {{ $attendance->date }})</p>

    <!-- 修正成功時のアラート表示 -->
    @if(session('message'))
        <div class="mb-6 p-4 bg-green-50 text-green-700 font-bold border border-green-200 rounded-sm text-sm text-center">
            {{ session('message') }}
        </div>
    @endif

    <form method="POST" action="/admin/attendance/{{ $attendance->id }}" class="w-full" novalidate>
        @csrf

        <!-- 出勤・退勤 -->
        <div class="grid grid-cols-2 gap-x-6 mb-6">
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-2">出勤</label>
                <input type="text" name="clock_in" value="{{ old('clock_in', substr($attendance->clock_in, 0, 5)) }}" 
                    class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none focus:border-black text-base">
                @error('clock_in') <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-2">退勤</label>
                <input type="text" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '') }}" 
                    class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none focus:border-black text-base">
                @error('clock_out') <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- 休憩時間 (一般用と完全に共通化した空フィールド1枠追加仕様) -->
        <div class="mb-10">
            <label class="block text-sm font-bold text-gray-800 mb-2">休憩時間</label>
            @foreach($attendance->breakTimes as $index => $break)
                <div class="grid grid-cols-2 gap-x-6 mb-3">
                    <input type="text" name="breaks[{{ $index }}][break_in]" value="{{ substr($break->break_in, 0, 5) }}" 
                        class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none">
                    <input type="text" name="breaks[{{ $index }}][break_out]" value="{{ $break->break_out ? substr($break->break_out, 0, 5) : '' }}" 
                        class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none">
                </div>
            @endforeach

            <!-- ★管理者用追加休憩フィールド -->
            @php $nextIndex = $attendance->breakTimes->count(); @endphp
            <div class="grid grid-cols-2 gap-x-6">
                <input type="text" name="breaks[{{ $nextIndex }}][break_in]" placeholder="新規休憩 開始"
                    class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none bg-gray-50">
                <input type="text" name="breaks[{{ $nextIndex }}][break_out]" placeholder="新規休憩 終了"
                    class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none bg-gray-50">
            </div>
            @error('breaks') <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p> @enderror
        </div>

        <!-- 直接編集・DB更新ボタン -->
        <button type="submit" class="w-full h-12 bg-black text-white text-sm font-bold rounded-sm hover:bg-gray-800 transition tracking-wider">
            勤怠データを直接修正する
        </button>
    </form>
</div>
@endsection
