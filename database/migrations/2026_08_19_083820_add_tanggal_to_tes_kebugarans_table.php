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
        Schema::table('tes_kebugarans', function (Blueprint $table) {
            // Menambahkan kolom tanggal. 
            // Kita beri nullable() agar data lama yang sudah ada tidak error.
            $table->date('tanggal')->nullable()->after('tipe_tes');
        });
    }

    public function down(): void
    {
        Schema::table('tes_kebugarans', function (Blueprint $table) {
            $table->dropColumn('tanggal');
        });
    }
};
