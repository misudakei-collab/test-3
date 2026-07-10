<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('clock_in');
            $table->time('clock_out');
            $table->json('break_times')->nullable(); // 複数の休憩データを一時保存
            $table->text('remarks'); // 備考欄
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // 承認待ち、承認済み、却下
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_requests');
    }
};
