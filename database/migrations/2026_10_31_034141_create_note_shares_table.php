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
        Schema::create('note_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Người được chia sẻ
            $table->enum('permission', ['view', 'edit'])->default('view'); // Cấp quyền: chỉ xem hoặc được sửa
            $table->timestamps();

            // Mỗi note chỉ chia sẻ cho 1 user tối đa 1 lần (tránh trùng lặp)
            $table->unique(['note_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_shares');
    }
};
