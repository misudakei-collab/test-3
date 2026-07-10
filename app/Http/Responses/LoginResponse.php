<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * 無限ループを回避し、指定のURLへ強制的に一発でジャンプさせる（FN017仕様）
     */
    public function toResponse($request)
    {
        $user = auth()->user();

        // 管理者ユーザー（is_adminがtrue）の場合：指定のURLへ直接強制遷移
        if ($user && $user->is_admin) {
            return redirect('/admin/attendance/list');
        }

        // 一般ユーザー（スタッフ）の場合
        return redirect('/attendance');
    }
}
