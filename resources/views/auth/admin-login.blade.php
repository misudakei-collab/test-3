@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center pt-10 select-none">
    
    <h1 class="text-2xl font-bold text-gray-900 mb-12 tracking-wider">管理者ログイン</h1>

        
    <form method="POST" action="/admin/login" class="w-[500px]" novalidate>
        @csrf

        
        <div class="mb-6">
            <label for="email" class="block text-sm font-bold text-gray-800 mb-2">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" 
                class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none focus:border-black text-base">
            @error('email')
                <p class="text-red-500 text-xs mt-2 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        
        <div class="mb-14"> 
            <label for="password" class="block text-sm font-bold text-gray-800 mb-2">パスワード</label>
            <input type="password" name="password" id="password" 
                class="w-full h-11 px-3 border border-gray-400 rounded-sm focus:outline-none focus:border-black text-base">
            @error('password')
                <p class="text-red-500 text-xs mt-2 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        
        <button type="submit" class="w-full h-12 bg-black text-white text-sm font-bold rounded-sm hover:bg-gray-800 transition duration-200 tracking-wider">
            管理者としてログイン
        </button>
    </form>
</div>
@endsection
