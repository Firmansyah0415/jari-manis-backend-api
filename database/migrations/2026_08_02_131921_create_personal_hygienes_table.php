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
        Schema::create('personal_hygienes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');

            // 10 Indikator Kebersihan Diri
            $table->boolean('mandi_2x_sehari')->default(false);
            $table->boolean('pakai_sabun')->default(false);
            $table->boolean('sikat_gigi_pagi')->default(false);
            $table->boolean('sikat_gigi_malam')->default(false);
            $table->boolean('cuci_tangan_sebelum_makan')->default(false);
            $table->boolean('cuci_tangan_setelah_bab')->default(false);
            $table->boolean('pakai_alas_kaki')->default(false);
            $table->boolean('pakai_pakaian_bersih')->default(false);
            $table->boolean('handuk_pribadi_bersih')->default(false);
            $table->boolean('cuci_tangan_luar_rumah')->default(false);

            $table->integer('skor_total'); // Max 100
            $table->string('kategori'); // Sangat Kurang s.d Sangat Baik

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_hygienes');
    }
};
