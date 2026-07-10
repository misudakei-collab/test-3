@extends('layouts.app')

@section('content')
<div class="max-w-[800px] mx-auto py-12 text-center select-none">
    
    <!-- 退勤完了時のアラートメッセージ表示（FN022 完全一致） -->
    @if(session('success'))
        <div class="mb-8 p-4 bg-green-50 text-green-700 font-bold border border-green-200 rounded-sm max-w-[500px] mx-auto text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- 出勤重複などのエラー用表示 -->
    @if(session('error'))
        <div class="mb-8 p-4 bg-red-50 text-red-700 font-bold border border-red-200 rounded-sm max-w-[500px] mx-auto text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- 【FN018】日時情報取得・リアルタイム時計UI -->
    <div class="text-lg font-bold text-gray-500 mb-2 tracking-wide">
        {{ $currentDate }}
    </div>
    <div id="live-clock" class="text-6xl font-black tracking-widest text-gray-900 mb-14">
        00:00:00
    </div>

    <!-- 打刻ボタンエリア（ステータスに応じて排他制御・デザイン最適化） -->
    <div class="grid grid-cols-2 gap-x-8 gap-y-6 max-w-[550px] mx-auto">
        
        <!-- 出勤ボタン（FN020） -->
        <form method="POST" action="/attendance/clock-in">
            @csrf
            <button type="submit" 
                @if($status !== '勤務外') disabled @endif
                class="w-full h-24 text-lg font-bold rounded-sm transition-all duration-200 shadow-sm
                @if($status === '勤務外') bg-black text-white hover:bg-gray-800 active:scale-[0.98] @else bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200 shadow-none @endif">
                出勤
            </button>
        </form>

        <!-- 退勤ボタン（FN022） -->
        <form method="POST" action="/attendance/clock-out">
            @csrf
            <button type="submit" 
                @if($status !== '出勤中') disabled @endif
                class="w-full h-24 text-lg font-bold rounded-sm transition-all duration-200 shadow-sm
                @if($status === '出勤中') bg-black text-white hover:bg-gray-800 active:scale-[0.98] @else bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200 shadow-none @endif">
                退勤
            </button>
        </form>

        <!-- 休憩入 / 休憩戻ボタン（FN021 / 横いっぱいの配置） -->
        <form method="POST" action="/attendance/break" class="col-span-2">
            @csrf
            @if($status === '休憩中')
                <button type="submit" class="w-full h-24 text-lg font-bold bg-white border border-black text-black rounded-sm hover:bg-gray-50 active:scale-[0.98] transition-all duration-200 shadow-sm">
                    休憩戻
                </button>
            @else
                <button type="submit" 
                    @if($status !== '出勤中') disabled @endif
                    class="w-full h-24 text-lg font-bold rounded-sm transition-all duration-200 shadow-sm
                    @if($status === '出勤中') bg-white border border-black text-black hover:bg-gray-50 active:scale-[0.98] @else bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200 shadow-none @endif">
                    休憩入
                </button>
            @endif
        </form>

    </div>
</div>

<!-- フロントエンド側でのデジタル時計の秒刻みJavaScript -->
<script>
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('live-clock').textContent = `${hours}:${minutes}:${seconds}`;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endsection
