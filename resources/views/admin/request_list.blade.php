@extends('layouts.app')

@section('content')
<div class="max-w-[1000px] mx-auto py-6 select-none">
    <h2 class="text-xl font-bold mb-8 border-b pb-4 tracking-wider">申請一覧（管理者用）</h2>

    <!-- 承認待ち申請の一覧表示 -->
    <div class="mb-10">
        <h3 class="text-sm font-bold text-gray-900 mb-4">承認待ちの申請</h3>
        <div class="border border-gray-300 rounded-sm overflow-hidden">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-black text-white text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 border-r border-gray-700">名前</th>
                        <th class="px-6 py-4 border-r border-gray-700">日付</th>
                        <th class="px-6 py-4 border-r border-gray-700">申請内容</th>
                        <th class="px-6 py-4 text-center">承認</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-gray-700">
                    @forelse($pendingRequests as $req)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 border-r border-gray-200 font-bold text-gray-900">{{ $req->user->name }}</td>
                            <td class="px-6 py-4 border-r border-gray-200">{{ $req->date }}</td>
                            <td class="px-6 py-4 border-r border-gray-200">{{ substr($req->clock_in, 0, 5) }} ～ {{ substr($req->clock_out, 0, 5) }}</td>
                            <td class="px-6 py-4 text-center">
                                <!-- 【PG13】修正申請承認画面への個別リンク -->
                                <a href="/admin/stamp_correction_request/approve/{{ $req->id }}" class="text-blue-500 font-bold underline hover:text-blue-700">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 font-medium">承認待ちの申請はありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 処理済み申請履歴の表示 -->
    <div>
        <h3 class="text-sm font-bold text-gray-900 mb-4">処理済みの申請履歴</h3>
        <div class="border border-gray-300 rounded-sm overflow-hidden">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-black text-white text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 border-r border-gray-700">名前</th>
                        <th class="px-6 py-4 border-r border-gray-700">日付</th>
                        <th class="px-6 py-4 border-r border-gray-700">申請内容</th>
                        <th class="px-6 py-4 text-center">ステータス</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-gray-700">
                    @forelse($processedRequests as $req)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 border-r border-gray-200 font-bold text-gray-900">{{ $req->user->name }}</td>
                            <td class="px-6 py-4 border-r border-gray-200">{{ $req->date }}</td>
                            <td class="px-6 py-4 border-r border-gray-200">{{ substr($req->clock_in, 0, 5) }} ～ {{ substr($req->clock_out, 0, 5) }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($req->status === 'approved')
                                    <span class="text-green-600 font-bold">承認済み</span>
                                @else
                                    <span class="text-red-500 font-bold">却下</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 font-medium">過去の処理履歴はありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
