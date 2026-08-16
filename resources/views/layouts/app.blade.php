<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理アプリ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="m-0 p-0 bg-white text-gray-900 font-sans min-h-screen flex flex-col">

    <!-- 共通ヘッダー -->
    <header class="bg-black text-white w-full h-16 flex items-center justify-center">
        <div class="w-full max-w-[1200px] px-10 flex items-center justify-between">
            
            <!-- ロゴ -->
            <div class="text-2xl font-black tracking-wider select-none">
                COACHTECH
            </div>
            
            <!-- ナビゲーションメニュー -->
            @auth
            <nav class="flex items-center gap-8 text-sm font-bold">
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.attendance.list') }}" class="text-white no-underline hover:opacity-80 transition">勤怠一覧</a>
                    <a href="{{ route('admin.staff.list') }}" class="text-white no-underline hover:opacity-80 transition">スタッフ一覧</a>
                    <a href="{{ route('admin.request_list') }}" class="text-white no-underline hover:opacity-80 transition">申請一覧</a>
                @else
                    <a href="{{ route('attendance.index') }}" class="text-white no-underline hover:opacity-80 transition">勤怠</a>
                    <a href="{{ route('attendance.list') }}" class="text-white no-underline hover:opacity-80 transition">勤怠一覧</a>
                    <a href="{{ route('attendance.request_list') }}" class="text-white no-underline hover:opacity-80 transition">申請一覧</a>
                    <a href="{{ route('attendance.report') }}" class="text-white no-underline hover:opacity-80 transition">レポート</a>
                @endif
                
                <!-- ログアウトボタン -->
                <form method="POST" action="/logout" class="inline m-0 p-0 flex items-center">
                    @csrf
                    <button type="submit" class="bg-transparent border-none text-white text-sm font-bold cursor-pointer p-0 hover:opacity-80 transition font-sans">
                        ログアウト
                    </button>
                </form>
            </nav>
            @endauth
        </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="flex-grow w-full max-w-[1200px] my-0 mx-auto p-10 box-border">
        @yield('content')
    </main>

    <!-- フッター -->
    <footer class="text-center py-5 text-xs text-gray-400 border-t border-gray-100">
        &copy; 2026 coachtech. All rights reserved.
    </footer>

</body>
</html>
