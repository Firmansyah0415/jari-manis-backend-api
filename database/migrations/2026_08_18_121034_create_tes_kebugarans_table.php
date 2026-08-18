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
        Schema::create('tes_kebugarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('tipe_tes', ['pre', 'post']); // Penanda apakah ini Pre-Test atau Post-Test

            // 5 Butir Tes Kebugaran
            $table->float('lari_12_menit')->nullable(); // Jarak dalam meter
            $table->integer('push_up')->nullable(); // Repetisi
            $table->integer('sit_up')->nullable(); // Repetisi
            $table->integer('pull_up_chining')->nullable(); // Repetisi
            $table->float('shuttle_run')->nullable(); // Waktu dalam detik

            // Hasil Akhir
            $table->integer('skor_total');
            $table->string('kategori');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tes_kebugarans');
    }
};
