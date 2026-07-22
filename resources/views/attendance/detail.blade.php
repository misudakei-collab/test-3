@extends('layouts.app')

@section('content')
<div class="max-w-[1200px] mx-auto py-6 select-none" style="font-family: sans-serif !important;">

    <!-- ページタイトル（公式見本通りの配置・縦線） -->
    <div class="flex items-center mb-10 pl-1" style="gap: 16px !important;">
        <div style="width: 4px !important; height: 28px !important; background-color: #000000 !important; border-radius: 9999px !important;"></div>
        <h2 class="text-2xl font-bold tracking-wider text-gray-900" style="margin: 0 !important; font-size: 24px !important;">
            勤怠詳細
        </h2>
    </div>

    <!-- ★【最重要】承認待ち状態(pending)であるかを判定する定義ラインを追記します -->
    @php 
        $isPending = $attendanceRequest && $attendanceRequest->status === 'pending';
    @endphp


    <!-- 申請メッセージやエラーの表示（必要に応じて表示） -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 text-green-700 font-bold border border-green-200 rounded-lg text-sm text-center max-w-[900px] mx-auto">
            {{ session('success') }}
        </div>
    @endif

    <!-- 見本を完全に再現した大きなカード型の2列テーブルフォーム（FN026） -->
    <form method="POST" action="/attendance/detail/{{ $attendance->id }}" class="max-w-[900px] mx-auto" novalidate>
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

                    <!-- 2. 日付（見本の離れた余白感を再現） -->
                    <tr style="height: 64px !important;">
                        <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">日付</td>
                        <td style="padding-left: 100px !important; font-size: 15px !important; font-weight: 500 !important; color: #111827 !important;">
                            <div class="flex items-center" style="gap: 60px !important;">
                                <span>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                                <span>{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                            </div>
                        </td>
                    </tr>

                    <!-- 3. 出勤・退勤（FN027） -->
                    <tr style="height: 72px !important;">
                        <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">出勤・退勤</td>
                        <td style="padding-left: 100px !important;">
                            <!-- ★【条件分岐ライン】承認待ち(isPendingがtrue)の場合 -->
                            @if($isPending)
                                <div class="flex items-center" style="gap: 32px !important; font-size: 15px !important; font-weight: bold !important; color: #111827 !important;">
                                    <span>{{ substr($attendanceRequest->clock_in, 0, 5) }}</span>
                                    <span style="font-weight: normal !important;">～</span>
                                    <span>{{ substr($attendanceRequest->clock_out, 0, 5) }}</span>
                                </div>
                            @else
                                <!-- ★【見本完全一致】通常のケースは、左右に綺麗な入力ボックスを横並びで配置します -->
                                <div class="flex items-center text-gray-400 font-bold" style="gap: 24px !important;">
                                    <input type="text" name="clock_in" value="{{ old('clock_in', substr($attendance->clock_in, 0, 5)) }}" 
                                        class="h-10 px-3 border border-gray-300 text-center font-medium text-gray-900 focus:outline-none" style="width: 110px !important; font-size: 15px !important; border-radius: 4px !important; box-sizing: border-box !important;">
                                    <span style="font-size: 16px !important; font-weight: normal !important; color: #111827 !important;">～</span>
                                    <input type="text" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '') }}" 
                                        class="h-10 px-3 border border-gray-300 text-center font-medium text-gray-900 focus:outline-none" style="width: 110px !important; font-size: 15px !important; border-radius: 4px !important; box-sizing: border-box !important;">
                                </div>
                                @error('clock_in') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                                @error('clock_out') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                            @endif
                        </td>
                    </tr>


                    <!-- 4. 休憩（既存の休憩データをループ出力）（FN028） -->
                    @php $breakTimes = $attendance->breakTimes; @endphp
                    <tr style="height: 72px !important;">
                        <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">休憩</td>
                        <td style="padding-left: 100px !important;">
                            <!-- ★【条件分岐ライン】承認待ち(isPendingがtrue)の場合：太文字テキスト表示 -->
                            @if($isPending)
                                @php $breaks = json_decode($attendanceRequest->break_times, true) ?: []; @endphp
                                <div class="flex items-center" style="gap: 32px !important; font-size: 15px !important; font-weight: bold !important; color: #111827 !important;">
                                    <span>{{ count($breaks) > 0 ? substr($breaks[0]['break_in'], 0, 5) : '12:00' }}</span>
                                    <span style="font-weight: normal !important;">～</span>
                                    <span>{{ count($breaks) > 0 ? substr($breaks[0]['break_out'], 0, 5) : '13:00' }}</span>
                                </div>
                            @else
                                <!-- ★通常時は組み立てていただいた美しい入力ボックスを表示 -->
                                <div class="flex items-center text-gray-400 font-bold" style="gap: 24px !important;">
                                    <input type="text" name="breaks[0][break_in]" value="{{ isset($breakTimes[0]) ? substr($breakTimes[0]->break_in, 0, 5) : '12:00' }}" 
                                        class="h-10 px-3 border border-gray-300 text-center font-medium text-gray-900 focus:outline-none" style="width: 110px !important; font-size: 15px !important; border-radius: 4px !important; box-sizing: border-box !important;">
                                    <span style="font-size: 16px !important; font-weight: normal !important; color: #111827 !important;">～</span>
                                    <input type="text" name="breaks[0][break_out]" value="{{ isset($breakTimes[0]) && $breakTimes[0]->break_out ? substr($breakTimes[0]->break_out, 0, 5) : '13:00' }}" 
                                        class="h-10 px-3 border border-gray-300 text-center font-medium text-gray-900 focus:outline-none" style="width: 110px !important; font-size: 15px !important; border-radius: 4px !important; box-sizing: border-box !important;">
                                </div>
                            @endif
                        </td>
                    </tr>

                    <!-- 5. 休憩2（見本の追加用空フィールド枠を完全再現）（FN028） -->
                    <!-- ★【条件分岐ライン】承認待ちではない(! $isPending)時だけ、この行全体を丸ごと出力します -->
                    @if(!$isPending)
                    <tr style="height: 72px !important;">
                        <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">休憩2</td>
                        <td style="padding-left: 100px !important;">
                            <!-- ★ドット表記を正しい入力フィールドに完全に補完しました -->
                            <div class="flex items-center text-gray-400 font-bold" style="gap: 24px !important;">
                                <input type="text" name="breaks[1][break_in]" class="h-10 px-3 border border-gray-300 text-center font-medium text-gray-900 focus:outline-none" style="width: 110px !important; font-size: 15px !important; border-radius: 4px !important; box-sizing: border-box !important;">
                                <span style="font-size: 16px !important; font-weight: normal !important; color: #111827 !important;">～</span>
                                <input type="text" name="breaks[1][break_out]" class="h-10 px-3 border border-gray-300 text-center font-medium text-gray-900 focus:outline-none" style="width: 110px !important; font-size: 15px !important; border-radius: 4px !important; box-sizing: border-box !important;">
                            </div>
                        </td>
                    </tr>
                    @endif


                    <!-- 6. 備考（FN029） -->
                    <tr style="height: 84px !important;">
                        <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important; vertical-align: middle !important;">備考</td>
                        <td style="padding: 12px 0 12px 100px !important; vertical-align: middle !important;">
                            <!-- ★【条件分岐ライン】承認待ちなら太文字テキスト、通常ならテキストエリアを表示 -->
                            @if($isPending)
                                <span style="font-size: 15px !important; font-weight: bold !important; color: #111827 !important;">
                                    {{ $attendanceRequest->remarks }}
                                </span>
                            @else
                                <textarea name="remarks" rows="1" 
                                    class="p-2 border border-gray-300 focus:outline-none focus:border-black font-medium text-gray-900 text-sm resize-none" style="width: 320px !important; height: 38px !important; border-radius: 4px !important; box-sizing: border-box !important;">{{ old('remarks', $attendanceRequest->remarks ?? '') }}</textarea>
                                @error('remarks') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                            @endif
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        <!-- 【公式見本完全一致】右下に綺麗に配置された黒い「修正」ボタン（FN030） -->
        <div class="flex justify-end mt-8">
            <!-- ★【条件分岐ライン】承認待ちの場合 -->
            @if($isPending)
                <!-- ボタンを完全に消去し、公式見本通りの赤文字警告テキストを出力 -->
                <span style="color: #ef4444 !important; font-size: 14px !important; font-weight: bold !important; letter-spacing: 0.05em !important;">
                    *承認待ちのため修正はできません。
                </span>
            @else
                <!-- ★まだ申請を出していない通常時は、今まで通り黒い修正ボタンを出力 -->
                <button type="submit" class="bg-black text-white text-sm font-bold hover:bg-gray-800 transition tracking-wider" style="width: 110px !important; height: 38px !important; border-radius: 4px !important; letter-spacing: 0.1em !important;">
                    修正
                </button>
            @endif
        </div>

    </form>
</div>
@endsection
