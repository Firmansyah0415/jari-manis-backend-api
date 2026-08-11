<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Menyuntikkan akun Super Admin ke dalam database
        // Kita gunakan updateOrCreate agar jika dijalankan 2x, tidak terjadi duplikat
        User::updateOrCreate(
            ['username' => env('ADMIN_USERNAME', 'admin')], // Username untuk login
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'admin123')), // Password standar (bisa Anda ganti)
                'role' => 'admin',
                'gender' => 'L',
                'sekolah_id' => null, // Admin memantau semua sekolah, jadi tidak terikat pada 1 sekolah
                'kelas_id' => null    // Admin tidak terikat kelas
            ]
        );

        $this->command->info('Akun Super Admin berhasil dibuat!');
    }
}
