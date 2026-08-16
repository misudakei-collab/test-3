@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center pt-10">
    <!-- タイトル -->
    <h1 class="text-xl font-bold text-gray-900 mb-10 tracking-wider">ログイン</h1>

    <!-- ログインフォーム -->
    <form method="POST" action="{{ route('login') }}" class="w-[500px]" novalidate>
        @csrf

        <!-- メールアドレス -->
        <div class="mb-5">
            <label for="email" class="block text-sm font-bold text-gray-800 mb-2">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" 
                class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none focus:border-black">
            @error('email')
                <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <!-- パスワード -->
        <div class="mb-10">
            <label for="password" class="block text-sm font-bold text-gray-800 mb-2">パスワード</label>
            <input type="password" name="password" id="password" 
                class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none focus:border-black">
            @error('password')
                <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <!-- ログインするボタン -->
        <button type="submit" class="w-full h-12 bg-black text-white text-sm font-bold rounded-sm hover:bg-gray-800 transition duration-200">
            ログインする
        </button>
    </form>

    <!-- 下部リンク -->
    <div class="text-center mt-6 text-xs">
        <a href="/register" class="text-blue-500 underline hover:text-blue-700">新規会員登録はこちら</a>
    </div>
</div>
@endsection
