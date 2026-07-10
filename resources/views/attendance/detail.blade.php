@extends('layouts.app')

@section('content')
<div class="max-w-[600px] mx-auto pt-10">
    <h2 class="text-xl font-bold text-gray-900 mb-10 tracking-wider text-center">勤怠詳細</h2>

    @if($attendanceRequest && $attendanceRequest->status !== 'pending')
        <div class="mb-6 p-4 bg-red-50 text-red-700 font-bold border border-red-200 rounded-sm text-sm text-center">
            承認待ちのため修正はできません。
        </div>
    @endif

    <form method="POST" action="/attendance/detail/{{ $attendance->id }}" class="w-full" novalidate>
        @csrf
        @php $isEditable = !($attendanceRequest && $attendanceRequest->status !== 'pending'); @endphp

        <!-- 出勤・退勤 -->
        <div class="grid grid-cols-2 gap-x-6 mb-6">
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-2">出勤</label>
                <input type="text" name="clock_in" value="{{ old('clock_in', substr($attendance->clock_in, 0, 5)) }}" @if(!$isEditable) disabled @endif
                    class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none focus:border-black">
                @error('clock_in') <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-800 mb-2">退勤</label>
                <input type="text" name="clock_out" value="{{ old('clock_out', substr($attendance->clock_out, 0, 5)) }}" @if(!$isEditable) disabled @endif
                    class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none focus:border-black">
                @error('clock_out') <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- 休憩時間 (FN026 仕様) -->
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-800 mb-2">休憩時間</label>
            @foreach($attendance->breakTimes as $index => $break)
                <div class="grid grid-cols-2 gap-x-6 mb-3">
                    <input type="text" name="breaks[{{ $index }}][break_in]" value="{{ substr($break->break_in, 0, 5) }}" @if(!$isEditable) disabled @endif
                        class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none">
                    <input type="text" name="breaks[{{ $index }}][break_out]" value="{{ substr($break->break_out, 0, 5) }}" @if(!$isEditable) disabled @endif
                        class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none">
                </div>
            @endforeach

            <!-- ★追加用空フィールドが1つ常に表示される仕様 -->
            @if($isEditable)
                @php $nextIndex = $attendance->breakTimes->count(); @endphp
                <div class="grid grid-cols-2 gap-x-6">
                    <input type="text" name="breaks[{{ $nextIndex }}][break_in]" placeholder="新規休憩 開始"
                        class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none bg-gray-50">
                    <input type="text" name="breaks[{{ $nextIndex }}][break_out]" placeholder="新規休憩 終了"
                        class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none bg-gray-50">
                </div>
                @error('breaks') <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p> @enderror
            @endif
        </div>

        <!-- 備考 -->
        <div class="mb-10">
            <label for="remarks" class="block text-sm font-bold text-gray-800 mb-2">備考</label>
            <textarea name="remarks" id="remarks" rows="3" @if(!$isEditable) disabled @endif
                class="w-full p-3 border border-gray-400 rounded-sm focus:outline-none focus:border-black">{{ old('remarks', $attendanceRequest->remarks ?? '') }}</textarea>
            @error('remarks') <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p> @enderror
        </div>

        @if($isEditable)
            <button type="submit" class="w-full h-12 bg-black text-white text-sm font-bold rounded-sm hover:bg-gray-800 transition">
                修正申請を送信する
            </button>
        @endif
    </form>
</div>
@endsection
