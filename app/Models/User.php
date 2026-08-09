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
}
