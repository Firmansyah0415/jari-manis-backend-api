<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'daerah'];

    // Relasi: Satu Sekolah punya Banyak Kelas
    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
}
