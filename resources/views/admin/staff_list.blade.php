@extends('layouts.app')

@section('content')
<div class="max-w-[1200px] mx-auto py-6 select-none" style="font-family: sans-serif !important;">

    <!-- ページタイトル（公式見本通りの配置・縦線） -->
    <div class="flex items-center mb-6 pl-1" style="gap: 16px !important;">
        <div style="width: 4px !important; height: 28px !important; background-color: #000000 !important; border-radius: 9999px !important;"></div>
        <h1 class="text-2xl font-bold tracking-wider text-gray-900" style="margin: 0 !important; font-size: 24px !important;">
            スタッフ一覧
        </h1>
    </div>


    <!-- 【公式デザイン】角丸・柔らかな影付きのスタイリッシュテーブル -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-md overflow-hidden" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
        <table class="w-full text-left text-sm border-collapse">
            <!-- 見本に合わせた薄いグレー背景と細字のヘッダー項目 -->
            <thead class="bg-gray-50/50 border-b border-gray-200">
                <tr style="height: 60px !important;">
                    <th style="padding: 0 40px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 30% !important;">名前</th>
                    <th style="padding: 0 32px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; width: 50% !important;">メールアドレス</th>
                    <th style="padding: 0 40px !important; font-size: 14px !important; font-weight: 500 !important; color: #9ca3af !important; text-align: center !important; width: 20% !important;">月次勤怠</th>
                </tr>
            </thead>
            <!-- データの行 -->
            <tbody class="divide-y divide-gray-100 text-gray-600 font-normal">
                @forelse($staffs as $staff)
                    <tr class="hover:bg-gray-50/80 transition duration-150" style="height: 60px !important;">
                        <!-- 名前 -->
                        <td style="padding: 0 40px !important; font-size: 15px !important; font-weight: 500 !important; color: #111827 !important;">
                            {{ $staff->name }}
                        </td>
                        <!-- メールアドレス -->
                        <td style="padding: 0 32px !important; font-size: 15px !important; color: #4b5563 !important;">
                            {{ $staff->email }}
                        </td>
                        <!-- 月次勤怠リンク（見本通りの黒太文字リンク） -->
                        <td style="padding: 0 40px !important; text-align: center !important;">
                            <a href="/admin/attendance/staff/{{ $staff->id }}" 
                                class="text-gray-900 font-bold hover:text-blue-600 transition underline underline-offset-4" style="font-size: 15px !important; color: #000000 !important;">
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr style="height: 240px !important;">
                        <td colspan="3" style="text-align: center !important; vertical-align: middle !important; color: #6b7280 !important; font-weight: 500 !important; font-size: 16px !important; letter-spacing: 0.05em !important;">
                            登録されているスタッフはいません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
