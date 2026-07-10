<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // ログインしていない場合は管理者ログイン画面へ
        if (!auth()->check()) {
            return redirect('/admin/login');
        }

        // ログインしているが管理者でない一般スタッフの場合は、一般打刻画面へ戻してループを防ぐ
        if (!auth()->user()->is_admin) {
            return redirect('/attendance');
        }

        return $next($request);
    }
}
