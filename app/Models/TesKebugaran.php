<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TesKebugaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tipe_tes',
        'tanggal',
        'lari_12_menit',
        'push_up',
        'sit_up',
        'pull_up_chining',
        'shuttle_run',
        'skor_total',
        'kategori'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
