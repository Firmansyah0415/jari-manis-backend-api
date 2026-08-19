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

    // --- TAMBAHKAN INI AGAR LARAVEL MENGIRIM TRUE/FALSE BUKAN 1/0 ---
    protected $casts = [
        'mandi_2x_sehari' => 'boolean',
        'pakai_sabun' => 'boolean',
        'sikat_gigi_pagi' => 'boolean',
        'sikat_gigi_malam' => 'boolean',
        'cuci_tangan_sebelum_makan' => 'boolean',
        'cuci_tangan_setelah_bab' => 'boolean',
        'pakai_alas_kaki' => 'boolean',
        'pakai_pakaian_bersih' => 'boolean',
        'handuk_pribadi_bersih' => 'boolean',
        'cuci_tangan_luar_rumah' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
