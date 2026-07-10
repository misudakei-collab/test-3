<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * 会員登録処理のカスタマイズ
     */
    public function create(array $input): User
    {
        // カスタムFormRequestを手動実行し、要件通りのバリデーションを強制適用
        $request = new RegisterRequest();
        $validator = \Validator::make($input, $request->rules(), $request->messages());
        $validator->validate();

        // 会員登録後に自動ログインされ、打刻画面へ遷移するためのユーザー生成
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
