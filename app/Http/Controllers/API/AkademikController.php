<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\Kelas;

class AkademikController extends Controller
{
    // Mengambil semua daftar sekolah
    public function getSekolah()
    {
        $sekolah = Sekolah::all();
        return response()->json([
            'message' => 'Sukses mengambil data sekolah',
            'data' => $sekolah
        ], 200);
    }

    // Mengambil daftar kelas berdasarkan ID sekolah yang dipilih
    public function getKelasBySekolah($sekolah_id)
    {
        $kelas = Kelas::where('sekolah_id', $sekolah_id)->get();
        return response()->json([
            'message' => 'Sukses mengambil data kelas',
            'data' => $kelas
        ], 200);
    }
}
