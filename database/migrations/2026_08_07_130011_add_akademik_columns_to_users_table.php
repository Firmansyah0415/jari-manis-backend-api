<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom sekolah dan kelas
            $table->foreignId('sekolah_id')->nullable()->constrained('sekolahs')->onDelete('set null');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sekolah_id']);
            $table->dropForeign(['kelas_id']);
            $table->dropColumn(['sekolah_id', 'kelas_id']);
        });
    }
};
