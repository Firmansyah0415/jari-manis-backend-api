<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktivitas_fisiks', function (Blueprint $table) {
            // Menambah kolom nama_aktivitas setelah kolom tanggal
            $table->string('nama_aktivitas')->nullable()->after('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('aktivitas_fisiks', function (Blueprint $table) {
            $table->dropColumn('nama_aktivitas');
        });
    }
};
