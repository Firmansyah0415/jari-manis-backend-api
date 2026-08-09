<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalHygiene extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tanggal',
        'mandi_2x_sehari',
        'pakai_sabun',
        'sikat_gigi_pagi',
        'sikat_gigi_malam',
        'cuci_tangan_sebelum_makan',
        'cuci_tangan_setelah_bab',
        'pakai_alas_kaki',
        'pakai_pakaian_bersih',
        'handuk_pribadi_bersih',
        'cuci_tangan_luar_rumah',
        'skor_total',
        'kategori'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
