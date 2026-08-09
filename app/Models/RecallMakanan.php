<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecallMakanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tanggal',
        'skor_total',
        'kategori',
        'detail_jawaban'
    ];

    protected $casts = [
        'detail_jawaban' => 'array', // Otomatis mengubah array PHP ke JSON Database
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
