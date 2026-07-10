<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date'); // 1日1回判定用
            $table->time('clock_in'); // 出勤時刻
            $table->time('clock_out')->nullable(); // 退勤時刻
            $table->string('comment', 255)->nullable(); // 公開API仕様(FN060)の備考欄
            $table->timestamps();

            // 同じユーザーが同じ日に2回出勤登録できないように制限
            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
