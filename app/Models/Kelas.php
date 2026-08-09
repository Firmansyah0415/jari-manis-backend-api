<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas'; // Paksa nama tabel menjadi 'kelas' (jangan biarkan Laravel menebak 'kelas_s')
    protected $fillable = ['sekolah_id', 'nama_kelas'];

    // Relasi: Kelas ini milik Satu Sekolah
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
}
