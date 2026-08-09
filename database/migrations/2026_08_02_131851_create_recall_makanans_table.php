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
        Schema::create('recall_makanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->integer('skor_total'); // Maksimal 100
            $table->string('kategori'); // Sangat Kurang, Kurang, Cukup, Baik, Sangat Baik

            // Kolom JSON untuk menampung detail input (Lauk, Nasi, Sayur, dll)
            $table->json('detail_jawaban')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recall_makanans');
    }
};
