@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center pt-10">
    <!-- タイトル -->
    <h1 class="text-xl font-bold text-gray-900 mb-10 tracking-wider">会員登録</h1>

    <!-- 会員登録フォーム -->
    <form method="POST" action="{{ route('register') }}" class="w-[500px]" novalidate>
        @csrf

        <!-- 名前 -->
        <div class="mb-5">
            <label for="name" class="block text-sm font-bold text-gray-800 mb-2">名前</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" 
                class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none focus:border-black">
            @error('name')
                <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
        </div>

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
        <div class="mb-5">
            <label for="password" class="block text-sm font-bold text-gray-800 mb-2">パスワード</label>
            <input type="password" name="password" id="password" 
                class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none focus:border-black">
            @error('password')
                <p class="text-red-500 text-xs mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <!-- パスワード確認 -->
        <div class="mb-10">
            <label for="password_confirmation" class="block text-sm font-bold text-gray-800 mb-2">パスワード確認</label>
            <input type="password" name="password_confirmation" id="password_confirmation" 
                class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none focus:border-black">
        </div>

        <!-- 登録するボタン -->
        <button type="submit" class="w-full h-12 bg-black text-white text-sm font-bold rounded-sm hover:bg-gray-800 transition duration-200">
            登録する
        </button>
    </form>

    <!-- 下部リンク -->
    <div class="text-center mt-6 text-xs">
        <a href="/login" class="text-blue-500 underline hover:text-blue-700">ログインはこちら</a>
    </div>
</div>
@endsection
