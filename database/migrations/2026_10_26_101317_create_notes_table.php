<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // Tiêu đề (bắt buộc)
            $table->longText('content')->nullable(); // Nội dung (bắt buộc nhập ban đầu)
            $table->string('color')->default('#ffffff'); // Màu nền ghi chú
            $table->boolean('is_pinned')->default(false); // Trạng thái ghim
            $table->timestamp('pinned_at')->nullable(); // Sắp xếp theo thời gian ghim
            $table->string('password_hash')->nullable(); // Mật khẩu bảo vệ ghi chú riêng biệt
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
