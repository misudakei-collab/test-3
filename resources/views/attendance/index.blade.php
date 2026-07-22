@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center select-none" style="font-family: sans-serif !important; min-height: 60vh !important; padding-top: 40px !important;">

    <!-- 1. ステータスバッジ（見本通りの丸角グレーバッジ） -->
    <div class="mb-6 flex items-center justify-center" style="height: 32px !important;">
        <span style="background-color: #e5e7eb !important; color: #6b7280 !important; font-size: 12px !important; font-weight: bold !important; padding: 4px 16px !important; border-radius: 9999px !important; letter-spacing: 0.05em !important;">
            {{ $status }}
        </span>
    </div>

    <!-- 2. 現在の日付（見本通りの大きめの曜日付きフォーマット） -->
    <div class="mb-4 text-center" style="color: #111827 !important; font-size: 24px !important; font-weight: 500 !important; letter-spacing: 0.02em !important;">
        {{ $currentDate }}
    </div>

    <!-- 3. デジタル時計（見本通りの超極太・大迫力フォント） -->
    <div id="digital-clock" class="mb-12 text-center" style="color: #000000 !important; font-size: 84px !important; font-weight: 900 !important; tracking-wide !important; line-height: 1 !important; font-family: monospace, sans-serif !important;">
        08:00
    </div>

    <!-- 4. 打刻ボタン・メッセージエリア（ステータスに応じて表示を見本に完全一致） -->
    <div class="w-full flex justify-center">
        @if($status === '勤務外')
            <!-- 【出勤前】黒い「出勤」ボタン -->
            <form method="POST" action="/attendance/clock-in" style="margin: 0 !important;">
                @csrf
                <button type="submit" class="bg-black text-white text-base font-bold hover:bg-gray-800 transition tracking-wider shadow-sm" style="width: 160px !important; height: 48px !important; border-radius: 8px !important; letter-spacing: 0.1em !important;">
                    出勤
                </button>
            </form>
        @elseif($status === '出勤中')
            <!-- 【出勤後】「退勤（黒）」と「休憩入（白）」が横並び -->
            <div class="flex items-center" style="gap: 24px !important;">
                <form method="POST" action="/attendance/clock-out" style="margin: 0 !important;">
                    @csrf
                    <button type="submit" class="bg-black text-white text-base font-bold hover:bg-gray-800 transition tracking-wider shadow-sm" style="width: 160px !important; height: 48px !important; border-radius: 8px !important; letter-spacing: 0.1em !important;">
                        退勤
                    </button>
                </form>
                <form method="POST" action="/attendance/break" style="margin: 0 !important;">
                    @csrf
                    <button type="submit" class="bg-white text-black text-base font-bold border border-gray-300 hover:bg-gray-50 transition tracking-wider shadow-sm" style="width: 160px !important; height: 48px !important; border-radius: 8px !important; letter-spacing: 0.1em !important;">
                        休憩入
                    </button>
                </form>
            </div>
        @elseif($status === '休憩中')
            <!-- 【休憩中】白い「休憩戻」ボタン -->
            <form method="POST" action="/attendance/break" style="margin: 0 !important;">
                @csrf
                <button type="submit" class="bg-white text-black text-base font-bold border border-gray-300 hover:bg-gray-50 transition tracking-wider shadow-sm" style="width: 160px !important; height: 48px !important; border-radius: 8px !important; letter-spacing: 0.1em !important;">
                    休憩戻
                </button>
            </form>
        @else
            <!-- ★【見本完全一致】退勤後はボタンを非表示にし、お疲れ様でした。のテキストを綺麗に配置 -->
            <div style="color: #000000 !important; font-size: 16px !important; font-weight: bold !important; letter-spacing: 0.05em !important; height: 48px !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                お疲れ様でした。
            </div>
        @endif
    </div>

</div>

<!-- リアルタイムでデジタル時計を1秒ごとに刻むJavaScript -->
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
