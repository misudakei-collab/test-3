@extends('layouts.app')

@section('content')
<div class="max-w-[1200px] mx-auto py-6 select-none font-sans">

    <!-- ページタイトル -->
    <div class="flex items-center mb-10 pl-1 gap-4">
        <div class="w-1 h-7 bg-black rounded-full"></div>
        <h1 class="text-2xl font-bold tracking-wider text-gray-900 m-0">
            申請一覧
        </h1>
    </div>

    @php
        $currentTab = request()->get('tab', 'pending');
    @endphp

    <!-- タブ切り替えバー -->
    <div class="flex border-b border-gray-200 mb-8 pl-2 gap-10">
        <a href="?tab=pending" 
           class="pb-2 text-sm font-bold no-underline transition-colors duration-150 {{ $currentTab === 'pending' ? 'text-black border-b-2 border-black' : 'text-gray-400 hover:text-gray-600' }}"
           style="margin-bottom: -1px !important;">
            承認待ち
        </a>
        
        <a href="?tab=approved" 
           class="pb-2 text-sm font-bold no-underline transition-colors duration-150 {{ $currentTab === 'approved' ? 'text-black border-b-2 border-black' : 'text-gray-400 hover:text-gray-600' }}"
           style="margin-bottom: -1px !important;">
            承認済み
        </a>
    </div>

    <!-- 申請一覧テーブル -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-md overflow-hidden" style="box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;">
        <table class="w-full text-left text-sm border-collapse">
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
            <tbody class="divide-y divide-gray-100 text-gray-600 font-normal">
                @php
                    $displayRequests = $currentTab === 'approved' ? $approvedRequests : $pendingRequests;
                @endphp

                @forelse($displayRequests as $req)
                    <tr class="hover:bg-gray-50/80 transition duration-150" style="height: 60px !important;">
                        <td style="padding: 0 24px !important; font-size: 15px !important; color: #6b7280 !important;">
                            {{ $currentTab === 'approved' ? '承認済み' : '承認待ち' }}
                        </td>
                        <td style="padding: 0 24px !important; font-size: 15px !important; font-weight: 500 !important; color: #111827 !important;">
                            {{ $req->user->name }}
                        </td>
                        <td style="padding: 0 24px !important; font-size: 15px !important; color: #111827 !important;">
                            {{ \Carbon\Carbon::parse($req->date)->format('Y/m/d') }}
                        </td>
                        <td style="padding: 0 24px !important; font-size: 15px !important; color: #4b5563 !important; max-width: 250px !important; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important;">
                            {{ $req->remarks }}
                        </td>
                        <td style="padding: 0 24px !important; font-size: 15px !important; color: #111827 !important;">
                            {{ $req->created_at ? $req->created_at->format('Y/m/d') : \Carbon\Carbon::parse($req->date)->format('Y/m/d') }}
                        </td>
                        <td style="padding: 0 24px !important; text-align: center !important;">
                            <a href="/attendance/detail/{{ $req->attendance_id }}" class="text-gray-900 font-bold hover:text-blue-600 transition underline underline-offset-4" style="font-size: 15px !important; color: #000000 !important;">
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr style="height: 240px !important;">
                        <td colspan="6" style="text-align: center !important; vertical-align: middle !important; color: #6b7280 !important; font-weight: 500 !important; font-size: 16px !important; letter-spacing: 0.05em !important;">
                            {{ $currentTab === 'approved' ? '承認済みの申請はありません。' : '承認待ちの申請はありません。' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
