@extends('layouts.app')

@section('content')
<div class="w-full font-sans select-none px-10">

    <!-- ページタイトル -->
    <div class="flex items-center mb-10 gap-4 mt-6">
        <div class="w-1 h-7 bg-black rounded-full"></div>
        <h1 class="text-2xl font-bold tracking-wider text-gray-900 m-0">
            勤怠詳細
        </h1>
    </div>

    <!-- 申請詳細テーブル -->
    <div class="w-full bg-white border border-gray-200 rounded-xl shadow-md overflow-hidden mb-6">
        <table class="w-full text-left text-sm border-collapse">
            <tbody class="divide-y divide-gray-100 text-gray-700 font-normal">
                <tr class="h-14">
                    <td class="w-48 bg-gray-50/50 px-12 font-medium text-gray-400 text-sm text-center">名前</td>
                    <td class="px-12 text-gray-900 font-bold text-sm text-center">{{ $requestData->user->name }}</td>
                </tr>
                <tr class="h-14">
                    <td class="w-48 bg-gray-50/50 px-12 font-medium text-gray-400 text-sm text-center">日付</td>
                    <td class="px-12 text-gray-900 font-bold text-sm text-center">
                        <span>{{ \Carbon\Carbon::parse($requestData->date)->format('Y年') }}</span>
                        <span class="ml-6">{{ \Carbon\Carbon::parse($requestData->date)->format('n月j日') }}</span>
                    </td>
                </tr>
                <tr class="h-14">
                    <td class="w-48 bg-gray-50/50 px-12 font-medium text-gray-400 text-sm text-center">出勤・退勤</td>
                    <td class="px-12 text-gray-900 font-bold text-sm text-center">
                        <span>{{ \Carbon\Carbon::parse($requestData->clock_in)->format('H:i') }}</span>
                        <span class="mx-8 font-normal text-gray-400">~</span>
                        <span>{{ \Carbon\Carbon::parse($requestData->clock_out)->format('H:i') }}</span>
                    </td>
                </tr>
                @forelse($appliedBreaks as $index => $break)
                    <tr class="h-14">
                        <td class="w-48 bg-gray-50/50 px-12 font-medium text-gray-400 text-sm text-center">休憩{{ $index > 0 ? $index + 1 : '' }}</td>
                        <td class="px-12 text-gray-900 font-bold text-sm text-center">
                            <span>{{ \Carbon\Carbon::parse($break['break_in'])->format('H:i') }}</span>
                            <span class="mx-8 font-normal text-gray-400">~</span>
                            <span>{{ \Carbon\Carbon::parse($break['break_out'])->format('H:i') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr class="h-14">
                        <td class="w-48 bg-gray-50/50 px-12 font-medium text-gray-400 text-sm text-center">休憩</td>
                        <td class="px-12 text-gray-900 font-bold text-sm text-center">-</td>
                    </tr>
                @endforelse
                <tr class="h-20">
                    <td class="w-48 bg-gray-50/50 px-12 font-medium text-gray-400 text-sm text-center">備考</td>
                    <td class="px-12 text-gray-900 font-bold text-sm text-center">{{ $requestData->remarks }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 承認ボタン -->
    <div class="w-full flex justify-end mt-8 pr-2">
        @if($requestData->status === 'pending')
            <form method="POST" action="/admin/stamp_correction_request/approve/{{ $requestData->id }}" class="m-0">
                @csrf
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="bg-black text-white text-sm font-bold hover:bg-gray-800 transition tracking-widest shadow-sm w-28 h-10 rounded">
                    承認
                </button>
            </form>
        @else
            <div class="flex items-center justify-center bg-gray-200 text-gray-500 text-sm font-bold tracking-widest w-28 h-10 rounded select-none">
                承認済み
            </div>
        @endif
    </div>

</div>
@endsection
