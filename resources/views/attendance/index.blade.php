@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center select-none font-sans min-h-[60vh] pt-10">

    <!-- ステータスバッジ -->
    <div class="mb-6 flex items-center justify-center h-8">
        <span class="bg-gray-200 text-gray-500 text-xs font-bold px-4 py-1 rounded-full tracking-wider">
            {{ $status }}
        </span>
    </div>

    <!-- 日付表示 -->
    <div class="mb-4 text-center text-gray-900 text-2xl font-medium tracking-wide">
        {{ $currentDate }}
    </div>

    <!-- デジタル時計 -->
    <div id="digital-clock" class="mb-12 text-center text-black text-[84px] font-black leading-none font-mono">
        08:00
    </div>

    <!-- 打刻ボタン -->
    <div class="w-full flex justify-center">
        @if($status === '勤務外')
            <form method="POST" action="/attendance/clock-in" class="m-0">
                @csrf
                <button type="submit" class="bg-black text-white text-base font-bold hover:bg-gray-800 transition tracking-[0.1em] shadow-sm w-40 h-12 rounded-lg">
                    出勤
                </button>
            </form>
        @elseif($status === '出勤中')
            <div class="flex items-center gap-6">
                <form method="POST" action="/attendance/clock-out" class="m-0">
                    @csrf
                    <button type="submit" class="bg-black text-white text-base font-bold hover:bg-gray-800 transition tracking-[0.1em] shadow-sm w-40 h-12 rounded-lg">
                        退勤
                    </button>
                </form>
                <form method="POST" action="/attendance/break" class="m-0">
                    @csrf
                    <button type="submit" class="bg-white text-black text-base font-bold border border-gray-300 hover:bg-gray-50 transition tracking-[0.1em] shadow-sm w-40 h-12 rounded-lg">
                        休憩入
                    </button>
                </form>
            </div>
        @elseif($status === '休憩中')
            <form method="POST" action="/attendance/break" class="m-0">
                @csrf
                <button type="submit" class="bg-white text-black text-base font-bold border border-gray-300 hover:bg-gray-50 transition tracking-[0.1em] shadow-sm w-40 h-12 rounded-lg">
                    休憩戻
                </button>
            </form>
        @else
            <div class="text-black text-base font-bold tracking-wider h-12 flex items-center justify-center">
                お疲れ様でした。
            </div>
        @endif
    </div>

</div>

<!-- 時計更新スクリプト -->
<script>
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('digital-clock').textContent = `${hours}:${minutes}`;
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
@endsection
