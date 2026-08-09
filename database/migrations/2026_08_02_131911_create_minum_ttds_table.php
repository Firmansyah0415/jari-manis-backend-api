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
        Schema::create('minum_ttds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal_minum')->nullable(); // Bisa null jika jawabannya "Tidak"[cite: 1]
            $table->boolean('sudah_minum')->default(false); // Ya/Tidak[cite: 1]
            $table->integer('skor'); // 100 jika sudah minum, 0 jika belum minum[cite: 1]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('minum_ttds');
    }
};
