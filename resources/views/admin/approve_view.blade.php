@extends('layouts.app')

@section('content')
<div class="max-w-[600px] mx-auto bg-white border border-gray-300 rounded-sm p-8 my-6 select-none">
    <h2 class="text-xl font-bold mb-2 tracking-wider text-center">修正申請 承認確認</h2>
    <p class="text-xs text-gray-500 text-center mb-8">対象スタッフ: <span class="font-bold text-black">{{ $requestData->user->name }}</span> ({{ $requestData->date }})</p>

    <!-- 申請内容詳細の可視化 -->
    <div class="space-y-6 mb-10 text-sm">
        <div class="grid grid-cols-2 gap-x-6 border-b pb-4">
            <div>
                <span class="block text-xs font-bold text-gray-400 mb-1">修正後 出勤</span>
                <span class="text-base font-bold text-gray-900">{{ substr($requestData->clock_in, 0, 5) }}</span>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400 mb-1">修正後 退勤</span>
                <span class="text-base font-bold text-gray-900">{{ substr($requestData->clock_out, 0, 5) }}</span>
            </div>
        </div>
        
        <!-- 備考（理由） -->
        <div class="border-b pb-4">
            <span class="block text-xs font-bold text-gray-400 mb-1">申請理由・備考</span>
            <p class="text-gray-800 bg-gray-50 p-4 rounded-sm border border-gray-200 whitespace-pre-wrap font-medium">{{ $requestData->remarks }}</p>
        </div>
    </div>

    <!-- 承認ボタン（ステータスに応じて排他制御） -->
    @if($requestData->status === 'pending')
        <form method="POST" action="/admin/stamp_correction_request/approve/{{ $requestData->id }}">
            @csrf
            <button type="submit" class="w-full h-12 bg-black text-white text-sm font-bold rounded-sm hover:bg-gray-800 transition tracking-wider">
                この修正申請を承認する
            </button>
        </form>
    @else
        <div class="w-full h-12 bg-gray-100 text-gray-400 font-bold flex items-center justify-center text-sm border border-gray-200 rounded-sm">
            処理済み (ステータス: {{ $requestData->status === 'approved' ? '承認済み' : '却下' }})
        </div>
    @endif
</div>
@endsection
