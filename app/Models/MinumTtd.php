<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinumTtd extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tanggal_minum',
        'sudah_minum',
        'skor'
    ];

    // --- TAMBAHKAN INI AGAR LARAVEL MENGIRIM TRUE/FALSE BUKAN 1/0 ---
    protected $casts = [
        'sudah_minum' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
