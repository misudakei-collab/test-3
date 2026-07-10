@extends('layouts.app')

@section('content')
<div class="max-w-[1000px] mx-auto py-6 select-none">
    <h2 class="text-xl font-bold mb-8 border-b pb-4 tracking-wider">スタッフ一覧</h2>

    <div class="border border-gray-300 rounded-sm overflow-hidden">
        <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-black text-white text-xs font-bold uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 border-r border-gray-700">名前</th>
                    <th class="px-6 py-4 border-r border-gray-700">メールアドレス</th>
                    <th class="px-6 py-4 text-center">詳細</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-gray-700 font-medium">
                @forelse($staffs as $staff)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4 border-r border-gray-200 font-bold text-gray-900">{{ $staff->name }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">{{ $staff->email }}</td>
                        <td class="px-6 py-4 text-center">
                            <!-- 【FN042】スタッフ別勤怠一覧への遷移リンク -->
                            <a href="/admin/attendance/staff/{{ $staff->id }}" class="text-blue-500 font-bold underline hover:text-blue-700">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-400">登録されているスタッフはいません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
