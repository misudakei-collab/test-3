<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理アプリ</title>
    <!-- ビルドした最新デザインファイルの自動読み込み -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="margin: 0 !important; padding: 0 !important; background-color: #ffffff !important; color: #111827 !important; font-family: sans-serif !important; min-height: 100vh !important; display: flex !important; flex-direction: column !important;">

    <!-- 【最優先強制固定】ヘッダーエリアを一番上に絶対に固定するスタイル -->
    <header style="order: -1 !important; background-color: #000000 !important; color: #ffffff !important; width: 100% !important; height: 64px !important; display: flex !important; align-items: center !important; justify-content: center !important; box-sizing: border-box !important;">
        <div style="width: 100% !important; max-w: 1200px !important; padding: 0 40px !important; display: flex !important; align-items: center !important; justify-content: space-between !important; box-sizing: border-box !important;">
            
            <!-- 左側：COACHTECH ロゴ -->
            <div style="font-size: 24px !important; font-weight: 900 !important; letter-spacing: 0.05em !important; user-select: none !important;">
                COACHTECH
            </div>
            
            <!-- 右側：ナビゲーションメニュー -->
            @auth
            <nav style="display: flex !important; align-items: center !important; gap: 32px !important; font-size: 14px !important; font-weight: bold !important;">
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.attendance.list') }}" style="color: #ffffff !important; text-decoration: none !important;">勤怠一覧</a>
                    <a href="{{ route('admin.staff.list') }}" style="color: #ffffff !important; text-decoration: none !important;">スタッフ一覧</a>
                    <a href="{{ route('admin.request_list') }}" style="color: #ffffff !important; text-decoration: none !important;">申請一覧</a>
                @else
                    <a href="{{ route('attendance.index') }}" style="color: #ffffff !important; text-decoration: none !important;">勤怠</a>
                    <a href="{{ route('attendance.list') }}" style="color: #ffffff !important; text-decoration: none !important;">勤怠一覧</a>
                    <a href="{{ route('attendance.request_list') }}" style="color: #ffffff !important; text-decoration: none !important;">申請一覧</a>
                    <a href="{{ route('attendance.report') }}" style="color: #ffffff !important; text-decoration: none !important;">レポート</a>
                @endif
                
                <!-- ログアウト -->
                <form method="POST" action="/logout" style="display: inline !important; margin: 0 !important;">
                    @csrf
                    <button type="submit" style="background: none !important; border: none !important; color: #ffffff !important; font-size: 14px !important; font-weight: bold !important; cursor: pointer !important; padding: 0 !important;">
                        ログアウト
                    </button>
                </form>
            </nav>
            @endauth
        </div>
    </header>

    <!-- メインコンテンツ -->
    <main style="flex-grow: 1 !important; width: 100% !important; max-w: 1200px !important; margin: 0 auto !important; padding: 40px 40px !important; box-sizing: border-box !important;">
        @yield('content')
    </main>

    <!-- フッター -->
    <footer style="text-align: center !important; padding: 20px 0 !important; font-size: 12px !important; color: #9ca3af !important; border-top: 1px solid #f3f4f6 !important;">
        &copy; 2026 coachtech. All rights reserved.
    </footer>

</body>
</html>
