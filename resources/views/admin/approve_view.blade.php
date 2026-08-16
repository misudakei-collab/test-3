@extends('layouts.app')

@section('content')
<div class="max-w-[1200px] mx-auto py-6 select-none" style="font-family: sans-serif !important;">

    <!-- ページタイトル（公式見本通り、見出しは「勤怠詳細」仕様） -->
    <div class="flex items-center mb-10 pl-1" style="gap: 16px !important;">
        <div style="width: 4px !important; height: 28px !important; background-color: #000000 !important; border-radius: 9999px !important;"></div>
        <h1 class="text-2xl font-bold tracking-wider text-gray-900" style="margin: 0 !important; font-size: 24px !important;">
        勤怠詳細
    </h1>
    </div>


    <!-- 見本を完全に再現した大きなカード型の2列テーブル確認フォーム -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-md overflow-hidden max-w-[900px] mx-auto" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
        <table class="w-full text-left text-sm border-collapse">
            <tbody class="divide-y divide-gray-100 text-gray-700">
                
                <!-- 1. 名前 -->
                <tr style="height: 64px !important;">
                    <td style="width: 25% !important; padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">名前</td>
                    <td style="width: 75% !important; padding-left: 140px !important; font-size: 15px !important; font-weight: 500 !important; color: #111827 !important;">
                        {{ $requestData->user->name }}
                    </td>
                </tr>

                <!-- 2. 日付（見本の離れた余白感を完全再現） -->
                <tr style="height: 64px !important;">
                    <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">日付</td>
                    <td style="padding-left: 100px !important; font-size: 15px !important; font-weight: 500 !important; color: #111827 !important;">
                        <div class="flex items-center" style="gap: 60px !important;">
                            <span>{{ \Carbon\Carbon::parse($requestData->date)->format('Y年') }}</span>
                            <span>{{ \Carbon\Carbon::parse($requestData->date)->format('n月j日') }}</span>
                        </div>
                    </td>
                </tr>

                <!-- 3. 出勤・退勤（見本通り、入力欄ではなくプレーンな太文字テキスト） -->
                <tr style="height: 64px !important;">
                    <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">出勤・退勤</td>
                    <td style="padding-left: 100px !important; font-size: 15px !important; font-weight: 500 !important; color: #111827 !important;">
                        <div class="flex items-center" style="gap: 32px !important;">
                            <span>{{ substr($requestData->clock_in, 0, 5) }}</span>
                            <span style="font-weight: normal !important; color: #111827 !important;">～</span>
                            <span>{{ substr($requestData->clock_out, 0, 5) }}</span>
                        </div>
                    </td>
                </tr>

                <!-- 4. 休憩（申請データの休憩時間をテキスト出力） -->
                @php 
                    $breaks = json_decode($requestData->break_times, true) ?: []; 
                    $firstBreak = $breaks[0] ?? null;
                @endphp
                <tr style="height: 64px !important;">
                    <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">休憩</td>
                    <td style="padding-left: 100px !important; font-size: 15px !important; font-weight: 500 !important; color: #111827 !important;">
                        @if($firstBreak)
                            <div class="flex items-center" style="gap: 32px !important;">
                                <span>{{ substr($firstBreak['break_in'], 0, 5) }}</span>
                                <span style="font-weight: normal !important; color: #111827 !important;">～</span>
                                <span>{{ substr($firstBreak['break_out'], 0, 5) }}</span>
                            </div>
                        @else
                            <span>-</span>
                        @endif
                    </td>
                </tr>

                <!-- 5. 休憩2（空のテキスト表示枠を完全再現） -->
                <tr style="height: 64px !important;">
                    <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">休憩2</td>
                    <td style="padding-left: 100px !important; font-size: 15px !important; font-weight: 500 !important; color: #111827 !important;">
                        @if(count($breaks) > 1 && isset($breaks[1]))
                            <div class="flex items-center" style="gap: 32px !important;">
                                <span>{{ substr($breaks[1]['break_in'], 0, 5) }}</span>
                                <span style="font-weight: normal !important; color: #111827 !important;">～</span>
                                <span>{{ substr($breaks[1]['break_out'], 0, 5) }}</span>
                            </div>
                        @else
                            <span></span>
                        @endif
                    </td>
                </tr>

                <!-- 6. 備考（スタッフが書いた申請理由がそのままテキスト表示） -->
                <tr style="height: 64px !important;">
                    <td style="padding-left: 40px !important; font-weight: bold !important; color: #4b5563 !important; background-color: #fafafa !important;">備考</td>
                    <td style="padding-left: 100px !important; font-size: 15px !important; font-weight: 500 !important; color: #111827 !important;">
                        {{ $requestData->remarks }}
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

    <!-- 右下に綺麗に配置された、承認を実行するボタン（ステータスで表示変更） -->
    <div class="max-w-[900px] mx-auto flex justify-end mt-8">
        @if($requestData->status === 'pending')
            <!-- まだ承認していない時は「承認」ボタンを表示 -->
            <form method="POST" action="/admin/stamp_correction_request/approve/{{ $requestData->id }}" style="margin: 0 !important;">
                @csrf
                <button type="submit" class="bg-black text-white text-sm font-bold hover:bg-gray-800 transition tracking-wider" style="width: 110px !important; height: 38px !important; border-radius: 4px !important; letter-spacing: 0.1em !important;">
                    承認
                </button>
            </form>
        @else
            <!-- ★【見本完全一致】承認した後は、画面遷移せずグレーの「承認済み」テキストに切り替え -->
            <div class="flex items-center justify-center text-sm font-bold border border-gray-300" 
                style="width: 110px !important; height: 38px !important; border-radius: 4px !important; background-color: #e5e7eb !important; color: #4b5563 !important; font-size: 14px !important; cursor: not-allowed !important;">
                承認済み
            </div>
        @endif
    </div>


</div>
@endsection
