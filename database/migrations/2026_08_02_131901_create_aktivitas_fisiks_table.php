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
        Schema::create('aktivitas_fisiks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->integer('durasi_menit'); // Durasi aktifitas fisik (Menit)[cite: 1]
            $table->string('kategori'); // Hasil konversi rentang menit (misal: 0 menit = Sangat Kurang)[cite: 1]
            $table->integer('skor')->default(0); // Tambahkan baris ini
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivitas_fisiks');
    }
};
