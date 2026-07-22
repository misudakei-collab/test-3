@extends('layouts.app')

@section('content')
<div class="max-w-[1200px] mx-auto py-6 select-none" style="font-family: sans-serif !important;">

    <!-- ページタイトル（公式見本通りの配置・縦線） -->
    <div class="flex items-center mb-10 pl-1" style="gap: 16px !important;">
        <div style="width: 4px !important; height: 28px !important; background-color: #000000 !important; border-radius: 9999px !important;"></div>
        <h2 class="text-2xl font-bold tracking-wider text-gray-900" style="margin: 0 !important; font-size: 24px !important;">
            申請一覧
        </h2>
    </div>

    <!-- 【公式見本完全一致】承認待ち / 承認済みのタブ切り替え風メニュー -->
    <div style="display: flex !important; gap: 40px !important; border-bottom: 1px solid #d1d5db !important; padding-bottom: 8px !important; margin-bottom: 32px !important; padding-left: 8px !important;">
        <span style="font-size: 14px !important; font-weight: bold !important; color: #000000 !important; cursor: pointer !important; position: relative !important;">
            承認待ち
            <!-- 見本の太い下線を再現 -->
            <div style="position: absolute !important; bottom: -9px !important; left: 0 !important; width: 100% !important; height: 2px !important; background-color: #000000 !important;"></div>
        </span>
        <span style="font-size: 14px !important; font-weight: bold !important; color: #9ca3af !important; cursor: pointer !important;">
            承認済み
        </span>
    </div>

    <!-- 【公式デザイン】角丸・柔らかな影付きのスタイリッシュテーブル -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-md overflow-hidden" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
        <table class="w-full text-left text-sm border-collapse">
            <!-- 見本通りの薄いグレー背景と細字のヘッダー項目 -->
            <thead class="bg-gray-50/50 border-b border-gray-200">
                <tr style="height: 60px !important;">
                    <th style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 12% !important;">状態</th>
                    <th style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 15% !important;">名前</th>
                    <th style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 18% !important;">対象日時</th>
                    <th style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 25% !important;">申請理由</th>
                    <th style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 18% !important;">申請日時</th>
                    <th style="padding: 0 24px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important; width: 12% !important;">詳細</th>
                </tr>
            </thead>
            <!-- データの行 -->
            <tbody class="divide-y divide-gray-100 text-gray-600 font-normal">
                @forelse($pendingRequests as $req)
                    <tr class="hover:bg-gray-50/80 transition duration-150" style="height: 60px !important;">
                        <!-- 状態（承認待ち） -->
                        <td style="padding: 0 24px !important; font-size: 15px !important; color: #6b7280 !important;">
                            承認待ち
                        </td>
                        <!-- 名前 -->
                        <td style="padding: 0 24px !important; font-size: 15px !important; font-weight: 500 !important; color: #111827 !important;">
                            {{ $req->user->name }}
                        </td>
                        <!-- 対象日時（日付） -->
                        <td style="padding: 0 24px !important; font-size: 15px !important; color: #111827 !important; tracking-wide !important;">
                            {{ \Carbon\Carbon::parse($req->date)->format('Y/m/d') }}
                        </td>
                        <!-- 申請理由（備考） -->
                        <td style="padding: 0 24px !important; font-size: 15px !important; color: #4b5563 !important; max-width: 250px !important; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important;">
                            {{ $req->remarks }}
                        </td>
                        <!-- 申請日時（作成日） -->
                        <td style="padding: 0 24px !important; font-size: 15px !important; color: #111827 !important;">
                            {{ $req->created_at ? $req->created_at->format('Y/m/d') : \Carbon\Carbon::parse($req->date)->format('Y/m/d') }}
                        </td>
                        <!-- 詳細リンク -->
                        <td style="padding: 0 24px !important; text-align: center !important;">
                            <a href="/admin/stamp_correction_request/approve/{{ $req->id }}" 
                                class="text-gray-900 font-bold hover:text-blue-600 transition underline underline-offset-4" style="font-size: 15px !important; color: #000000 !important;">
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr style="height: 240px !important;">
                        <td colspan="6" style="text-align: center !important; vertical-align: middle !important; color: #6b7280 !important; font-weight: 500 !important; font-size: 16px !important; letter-spacing: 0.05em !important;">
                            承認待ちの申請はありません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
