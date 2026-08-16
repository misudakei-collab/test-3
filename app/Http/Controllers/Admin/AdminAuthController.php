<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    /**
     * 【PG07】管理者専用ログイン画面の表示
     */
    public function showLogin()
    {
        return view('auth.admin-login');
    }

    /**
     * 【PG07】管理者ログイン認証処理
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(array_merge($credentials, ['is_admin' => true]))) {
            $request->session()->regenerate();
            return redirect()->route('admin.attendance.list');
        }

        return back()->withErrors([
            'email' => '管理者アカウントの認証に失敗しました。',
        ]);
    }
}
