<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// ★最上部の並びに以下の2行を必ず追記してください
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

     /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 既存のRateLimiterなどは残したまま、以下を追記します
        \Illuminate\Support\Facades\RateLimiter::for('login', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->email.$request->ip());
        });

        // ★【最重要】管理者かどうかを判定するGate（権限）を定義して開通させます
        \Illuminate\Support\Facades\Gate::define('admin-only', function ($user) {
            return $user->is_admin === true; // または 1
        });
    }
}
