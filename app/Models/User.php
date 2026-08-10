<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 1. Pastikan baris ini ada

class User extends Authenticatable
{
    // 2. Tambahkan HasApiTokens di dalam class
    use HasApiTokens, HasFactory, Notifiable;

    // 3. Tambahkan username dan role ke dalam array fillable
    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'gender',
        'sekolah_id',
        'kelas_id',
        'foto_profil',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Tambahkan relasi ini di bawah
    public function preTest()
    {
        return $this->hasOne(PreTest::class);
    }

    // --- TAMBAHKAN KODE INI ---
    public function postTest()
    {
        return $this->hasOne(PostTest::class);
    }

    public function recallMakanan()
    {
        return $this->hasMany(RecallMakanan::class);
    }

    public function aktivitasFisik()
    {
        return $this->hasMany(AktivitasFisik::class);
    }

    public function minumTtd()
    {
        return $this->hasMany(MinumTtd::class);
    }

    public function personalHygiene()
    {
        return $this->hasMany(PersonalHygiene::class);
    }

    public function sekolah()
    {
        // User (Siswa/Guru) 'dimiliki oleh' satu Sekolah
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    public function kelas()
    {
        // User (Siswa) 'dimiliki oleh' satu Kelas
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    // 1. Beritahu Laravel untuk selalu menyertakan kolom buatan 'total_skor' saat API dipanggil
    protected $appends = ['total_skor', 'total_hari_aktif', 'is_post_test_done'];

    // 2. Buat fungsi rumus perhitungannya
    public function getTotalSkorAttribute()
    {
        if ($this->role !== 'siswa') {
            return 0;
        }

        $skorPreTest = \App\Models\PreTest::where('user_id', $this->id)->sum('skor');
        $skorRecall = \App\Models\RecallMakanan::where('user_id', $this->id)->sum('skor_total');

        // --- KODE BARU: Mengambil skor dari Aktivitas Fisik ---
        $skorFisik = \App\Models\AktivitasFisik::where('user_id', $this->id)->sum('skor');

        $skorTtd = \App\Models\MinumTtd::where('user_id', $this->id)->sum('skor');
        $skorHygiene = \App\Models\PersonalHygiene::where('user_id', $this->id)->sum('skor_total');

        // Kembalikan total akumulasi kelima skor
        return $skorPreTest + $skorRecall + $skorFisik + $skorTtd + $skorHygiene;
    }

    public function getTotalHariAktifAttribute()
    {
        if ($this->role !== 'siswa') return 0;

        // Kita gunakan tabel 'recall_makanan' sebagai patokan (karena wajib diisi).
        // Menghitung jumlah tanggal yang berbeda (unik) untuk mengetahui berapa hari siswa sudah aktif.
        return \App\Models\RecallMakanan::where('user_id', $this->id)->distinct('tanggal')->count('tanggal');
    }

    // FUNGSI BARU: Mengecek apakah siswa sudah mengerjakan Post-Test
    public function getIsPostTestDoneAttribute()
    {
        if ($this->role !== 'siswa') return false;

        // Mengembalikan nilai true jika ada data, false jika tidak ada
        return \App\Models\PostTest::where('user_id', $this->id)->exists();
    }
}
