<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php', // ← ここに「.php」を正確に追記しました
        api: __DIR__.'/../routes/api.php', // ← 今後のAPI用にも「.php」をあらかじめ正確に指定します
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 管理者専用のチェックミドルウェア登録
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // APIリクエスト時の404エラーJSON共通化
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['error' => '勤怠情報が見つかりませんでした。'], 404);
            }
        });
    })->create();
