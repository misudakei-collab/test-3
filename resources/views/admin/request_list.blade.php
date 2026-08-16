@extends('layouts.app')

@section('content')
<div class="w-full font-sans select-none">

    <!-- ページタイトル -->
    <div class="flex items-center mb-6 gap-4">
        <div class="w-1 h-7 bg-black rounded-full"></div>
        <h1 class="text-2xl font-bold tracking-wider text-gray-900 m-0">
            申請一覧
        </h1>
    </div>

    <!-- タブ切り替えバー -->
    <div class="flex border-b border-gray-200 mb-8 pl-2 gap-10">
        <a href="?status=pending" 
           class="pb-2 text-sm font-bold no-underline transition-colors duration-150 {{ $status === 'pending' ? 'text-black border-b-2 border-black' : 'text-gray-400 hover:text-gray-600' }}"
           style="margin-bottom: -1px !important;">
            承認待ち
        </a>
        
        <a href="?status=approved" 
           class="pb-2 text-sm font-bold no-underline transition-colors duration-150 {{ $status === 'approved' ? 'text-black border-b-2 border-black' : 'text-gray-400 hover:text-gray-600' }}"
           style="margin-bottom: -1px !important;">
            承認済み
        </a>
    </div>

    <!-- 申請一覧テーブル -->
    <div class="w-full bg-white border border-gray-200 rounded-xl shadow-md overflow-hidden" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
        <table class="w-full text-left text-sm border-collapse table-fixed">
            <thead class="bg-gray-50/50 border-b border-gray-200">
                <tr class="h-[60px]">
                    <th class="px-6 font-medium text-gray-400 text-sm w-[12%]">状態</th>
                    <th class="px-6 font-medium text-gray-400 text-sm w-[15%]">名前</th>
                    <th class="px-6 font-medium text-gray-400 text-sm w-[18%]">対象日時</th>
                    <th class="px-6 font-medium text-gray-400 text-sm w-[25%]">申請理由</th>
                    <th class="px-6 font-medium text-gray-400 text-sm w-[18%]">申請日時</th>
                    <th class="px-6 font-medium text-gray-400 text-sm w-[12%] text-center">詳細</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-600 font-normal">
                @forelse($requests as $req)
                    <tr class="hover:bg-gray-50/80 transition duration-150 h-[60px]">
                        <td class="px-6 text-[15px] text-gray-500 truncate">
                            {{ $status === 'approved' ? '承認済み' : '承認待ち' }}
                        </td>
                        <td class="px-6 text-[15px] font-medium text-gray-900 truncate">
                            {{ $req->user->name }}
                        </td>
                        <td class="px-6 text-[15px] text-gray-900 truncate">
                            {{ \Carbon\Carbon::parse($req->date)->format('Y/m/d') }}
                        </td>
                        <td class="px-6 text-[15px] text-gray-500 truncate">
                            {{ $req->remarks }}
                        </td>
                        <td class="px-6 text-[15px] text-gray-900 truncate">
                            {{ $req->created_at ? $req->created_at->format('Y/m/d') : \Carbon\Carbon::parse($req->date)->format('Y/m/d') }}
                        </td>
                        <td class="px-6 text-center">
                            <a href="/admin/stamp_correction_request/approve/{{ $req->id }}" class="text-black font-bold hover:text-blue-600 transition underline underline-offset-4 text-[15px]">
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr class="h-[240px]">
                        <td colspan="6" class="text-center text-gray-500 font-medium tracking-wider text-base">
                            {{ $status === 'approved' ? '承認済みの申請はありません。' : '承認待ちの申請はありません。' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
