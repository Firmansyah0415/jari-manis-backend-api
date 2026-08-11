<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AktivitasFisik extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tanggal',
        'durasi_menit',
        'kategori',
        'skor'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
