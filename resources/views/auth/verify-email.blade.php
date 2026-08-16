@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center select-none" style="font-family: sans-serif !important; min-height: 55vh !important; padding-top: 40px !important;">

    
    <div class="mb-10 text-center" style="color: #000000 !important; font-size: 15px !important; font-weight: bold !important; line-height: 1.8 !important; letter-spacing: 0.02em !important;">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </div>

    
    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 text-sm font-semibold text-green-600 text-center">
            新しい認証リンクを再送しました。
        </div>
    @endif

    
    <div class="mb-12">
        <form method="POST" action="/email/verification-notification" style="margin: 0 !important; display: inline-block;">
            @csrf
            
           <button type="submit" onclick="window.open('http://localhost:8025', '_blank');" class="border border-gray-400 bg-gray-200 text-gray-900 font-bold hover:bg-gray-300 transition" 
            style="width: 188px !important; height: 38px !important; border-radius: 4px !important; font-size: 14px !important; letter-spacing: 0.02em !important; box-sizing: border-box !important;">
                認証はこちらから
            </button>

        </form>
    </div>

    
    <div class="text-center">
        <form method="POST" action="/email/verification-notification" style="margin: 0 !important; display: inline-block;">
            @csrf
            <button type="submit" class="text-blue-500 hover:text-blue-700 font-medium underline underline-offset-4 bg-transparent border-none p-0 cursor-pointer" style="font-size: 14px !important;">
                認証メールを再送する
            </button>
        </form>
    </div>

</div>
@endsection
