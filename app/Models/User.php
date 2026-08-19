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

    public function tesKebugaran()
    {
        return $this->hasMany(TesKebugaran::class);
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
    protected $appends = [
        'total_skor',
        'total_hari_aktif',
        'is_post_test_done',
        'is_pre_test_kebugaran_done',
        'is_post_test_kebugaran_done'
    ];

    // 2. Buat fungsi rumus perhitungannya
    public function getTotalSkorAttribute()
    {
        if ($this->role !== 'siswa') {
            return 0;
        }

        // KITA GUNAKAN RELASI YANG SUDAH ADA DI ATAS CLASS (Lebih Cepat & Aman di Server)
        $skorPreTest = $this->preTest()->sum('skor');
        $skorRecall = $this->recallMakanan()->sum('skor_total');
        $skorFisik = $this->aktivitasFisik()->sum('skor');
        $skorTtd = $this->minumTtd()->sum('skor');
        $skorHygiene = $this->personalHygiene()->sum('skor_total');

        return $skorPreTest + $skorRecall + $skorFisik + $skorTtd + $skorHygiene;
    }

    public function getTotalHariAktifAttribute()
    {
        if ($this->role !== 'siswa') return 0;

        return $this->recallMakanan()->distinct('tanggal')->count('tanggal');
    }

    public function getIsPostTestDoneAttribute()
    {
        if ($this->role !== 'siswa') return false;

        return $this->postTest()->exists();
    }

    // 2. Tambahkan 2 fungsi baru ini di PALING BAWAH sebelum tanda kurung tutup "}"
    public function getIsPreTestKebugaranDoneAttribute()
    {
        if ($this->role !== 'siswa') return false;
        return $this->tesKebugaran()->where('tipe_tes', 'pre')->exists();
    }

    public function getIsPostTestKebugaranDoneAttribute()
    {
        if ($this->role !== 'siswa') return false;
        return $this->tesKebugaran()->where('tipe_tes', 'post')->exists();
    }
}
