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
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Nhãn thuộc về từng user
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('note_label', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('label_id')->constrained()->cascadeOnDelete(); // Xóa label chỉ xóa liên kết ở đây, không mất note
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_label');
        Schema::dropIfExists('labels');
    }
};
