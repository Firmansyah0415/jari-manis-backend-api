<?php

namespace Database\Seeders;

use App\Models\Sekolah;
use App\Models\Kelas;
use Illuminate\Database\Seeder;

class AkademikSeeder extends Seeder
{
    public function run()
    {
        // 1. Buat Data Sekolah Dummy di 3 Daerah Penelitian
        $sekolah1 = Sekolah::create(['nama' => 'SMAN 1 Jeneponto', 'daerah' => 'Jeneponto']);
        $sekolah2 = Sekolah::create(['nama' => 'SMAN 1 Makale', 'daerah' => 'Toraja']);
        $sekolah3 = Sekolah::create(['nama' => 'SMAN 1 Enrekang', 'daerah' => 'Enrekang']);

        // 2. Buat Data Kelas untuk Masing-Masing Sekolah
        // Kelas untuk Jeneponto
        Kelas::create(['sekolah_id' => $sekolah1->id, 'nama_kelas' => 'X MIPA 1']);
        Kelas::create(['sekolah_id' => $sekolah1->id, 'nama_kelas' => 'X MIPA 2']);

        // Kelas untuk Toraja
        Kelas::create(['sekolah_id' => $sekolah2->id, 'nama_kelas' => 'XI IPS 1']);
        Kelas::create(['sekolah_id' => $sekolah2->id, 'nama_kelas' => 'XI IPS 2']);

        // Kelas untuk Enrekang
        Kelas::create(['sekolah_id' => $sekolah3->id, 'nama_kelas' => 'XII IPA 1']);
        Kelas::create(['sekolah_id' => $sekolah3->id, 'nama_kelas' => 'XII IPA 2']);
    }
}
