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
    {        $request = new RegisterRequest();
        $validator = \Validator::make($input, $request->rules(), $request->messages());
        $validator->validate();        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
