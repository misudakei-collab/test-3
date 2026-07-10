<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
        /**
     * Register any application services.
     */
    public function register(): void
    {
        // ★ログイン成功時の転送処理として、今作ったファイルを安全に100%認識させます
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ★会員登録（CreateNewUser）のアクションを登録して有効化します
        Fortify::createUsersUsing(\App\Actions\Fortify\CreateNewUser::class);

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::loginView(function () {
            if (request()->is('admin/*')) {
                return view('auth.admin-login');
            }
            return view('auth.login');
        });

        // ログイン時の認証・バリデーションのカスタマイズ
        Fortify::authenticateUsing(function ($request) {
            $request->validate([
                'email' => 'required',
                'password' => 'required',
            ], [
                'email.required' => 'メールアドレスを入力してください',
                'password.required' => 'パスワードを入力してください',
            ]);

            $user = \App\Models\User::where('email', $request->email)->first();

            if (! $user || ! \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => ['ログイン情報が登録されていません'],
                ]);
            }

            return $user;
        });
    }
}
