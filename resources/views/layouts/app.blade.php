<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理アプリ</title>
    <!-- ビルドした最新デザインファイルの自動読み込み -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 font-sans min-h-screen flex flex-col">

    <!-- ヘッダーエリア（ログイン状態に応じて幅と配置を自動最適化） -->
    <header class="bg-black text-white">
        @auth
            <!-- 【ログイン時】仕様書通りの最大幅1540px、両端配置 -->
            <div class="max-w-[1540px] min-w-[1400px] mx-auto px-10 h-16 flex items-center justify-between select-none">
        @else
            <!-- 【未ログイン時】画像通りロゴをシンプルに配置（幅を制限せず左寄せ） -->
            <div class="w-full px-10 h-16 flex items-center select-none">
        @endauth
            
            <!-- 左側：COACHTECH ロゴ -->
            <div class="text-2xl font-black tracking-wider flex items-center">
                COACHTECH
            </div>
            
            <!-- 右側：ナビゲーションメニュー -->
            @auth
            <nav class="flex items-center space-x-8 text-sm font-bold tracking-normal">
                @if(auth()->user()->is_admin)
                    <!-- 管理者用メニュー -->
                    <a href="{{ route('admin.attendance.list') }}" class="hover:text-gray-300">勤怠一覧</a>
                    <a href="{{ route('admin.staff.list') }}" class="hover:text-gray-300">スタッフ一覧</a>
                    <a href="{{ route('stamp_correction_request.list') }}" class="hover:text-gray-300">申請一覧</a>
                @else
                    <!-- 一般ユーザー（スタッフ）用メニュー -->
                    <a href="{{ route('attendance.index') }}" class="hover:text-gray-300">勤怠</a>
                    <a href="{{ route('attendance.list') }}" class="hover:text-gray-300">勤怠一覧</a>
                    <a href="{{ route('stamp_correction_request.list') }}" class="hover:text-gray-300">申請</a>
                    <a href="{{ route('attendance.report') }}" class="hover:text-gray-300">レポート</a>
                @endif
                
                <!-- ログアウトリンク -->
                <form method="POST" action="/logout" id="logout-form" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-gray-300 font-bold focus:outline-none">
                        ログアウト
                    </button>
                </form>
            </nav>
            @endauth
        </div>
    </header>

    <!-- メインコンテンツ（ログイン状態に合わせてコンテナ幅を最適化） -->
    @auth
        <main class="flex-grow max-w-[1540px] min-w-[1400px] mx-auto w-full px-10 py-12">
    @else
        <main class="flex-grow w-full px-10 py-12">
    @endauth
        @yield('content')
    </main>

    <footer class="text-center py-4 text-xs text-gray-400 border-t border-gray-100 mt-auto">
        &copy; 2026 coachtech. All rights reserved.
    </footer>

</body>
</html>
