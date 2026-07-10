@extends('layouts.app')

@section('content')
<div class="max-w-[1000px] mx-auto py-6">
    <h2 class="text-xl font-bold mb-8 border-b pb-4 tracking-wider">申請一覧</h2>

    <div class="mb-10">
        <h3 class="text-sm font-bold text-gray-800 mb-4">承認待ちの申請</h3>
        <div class="border border-gray-300 rounded-sm overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-black text-white text-xs font-bold">
                    <tr>
                        <th class="px-6 py-4">日付</th>
                        <th class="px-6 py-4">申請内容</th>
                        <th class="px-6 py-4 text-center">詳細</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($pendingRequests as $req)
                        <tr>
                            <td class="px-6 py-4 font-bold">{{ $req->date }}</td>
                            <td class="px-6 py-4">{{ substr($req->clock_in,0,5) }} ～ {{ substr($req->clock_out,0,5) }}</td>
                            <td class="px-6 py-4 text-center"><a href="/attendance/detail/{{ $req->attendance_id }}" class="text-blue-500 font-bold underline">詳細</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">承認待ちの申請はありません。</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
