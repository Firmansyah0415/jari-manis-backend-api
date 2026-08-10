<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostTest extends Model
{
    use HasFactory;

    // Mengizinkan Laravel mengisi kolom-kolom ini secara otomatis
    protected $fillable = ['user_id', 'skor', 'is_completed'];

    // Relasi balik ke tabel User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
