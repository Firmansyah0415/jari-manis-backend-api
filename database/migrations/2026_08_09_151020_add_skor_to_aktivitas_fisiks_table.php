<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Cek dulu, kalau kolom 'skor' BELUM ada, baru buat.
        if (!Schema::hasColumn('aktivitas_fisiks', 'skor')) {
            Schema::table('aktivitas_fisiks', function (Blueprint $table) {
                $table->integer('skor')->default(0)->after('kategori');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aktivitas_fisiks', function (Blueprint $table) {
            // Menghapus kolom jika sewaktu-waktu kita melakukan rollback
            $table->dropColumn('skor');
        });
    }
};
