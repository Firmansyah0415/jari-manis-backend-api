<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PreTest;
use App\Models\RecallMakanan;
use App\Models\AktivitasFisik;
use App\Models\MinumTtd;
use App\Models\PersonalHygiene;

class ZonaController extends Controller
{
    // ==========================================
    // 1. FITUR PRE-TEST
    // ==========================================
    public function storePreTest(Request $request)
    {
        $request->validate([
            'skor' => 'required|integer'
        ]);

        // Gunakan updateOrCreate agar siswa hanya punya 1 data Pre-Test
        $preTest = PreTest::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'skor' => $request->skor,
                'is_completed' => true
            ]
        );

        return response()->json([
            'message' => 'Data Pre-Test berhasil disimpan',
            'data' => $preTest
        ], 201);
    }

    // ==========================================
    // 2. ZONA RECALL 24 JAM
    // ==========================================
    public function storeRecallMakanan(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'skor_total' => 'required|integer',
            'detail_jawaban' => 'nullable|array'
        ]);

        $skor = $request->skor_total;

        if ($skor < 60) $kategori = 'Sangat Kurang';
        elseif ($skor <= 69) $kategori = 'Kurang';
        elseif ($skor <= 79) $kategori = 'Cukup';
        elseif ($skor <= 89) $kategori = 'Baik';
        else $kategori = 'Sangat Baik';

        // MENGGUNAKAN updateOrCreate
        $recall = RecallMakanan::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'tanggal' => $request->tanggal // Kunci utamanya: User dan Tanggal
            ],
            [
                'skor_total' => $skor,
                'kategori' => $kategori,
                'detail_jawaban' => $request->detail_jawaban
            ]
        );

        return response()->json(['message' => 'Data Recall Makanan berhasil disimpan', 'data' => $recall], 201);
    }

    // ==========================================
    // 3. ZONA AKTIVITAS FISIK
    // ==========================================
    public function storeAktivitasFisik(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'durasi_menit' => 'required|integer'
        ]);

        $menit = $request->durasi_menit;

        if ($menit == 0) $kategori = 'Sangat Kurang';
        elseif ($menit <= 29) $kategori = 'Kurang';
        elseif ($menit <= 44) $kategori = 'Cukup';
        elseif ($menit <= 59) $kategori = 'Baik';
        else $kategori = 'Sangat Baik';

        // MENGGUNAKAN updateOrCreate
        $aktivitas = AktivitasFisik::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'tanggal' => $request->tanggal
            ],
            [
                'durasi_menit' => $menit,
                'kategori' => $kategori
            ]
        );

        return response()->json(['message' => 'Data Aktivitas Fisik berhasil disimpan', 'data' => $aktivitas], 201);
    }

    // ==========================================
    // 4. ZONA MINUM TTD
    // ==========================================
    public function storeMinumTtd(Request $request)
    {
        $request->validate([
            'sudah_minum' => 'required|boolean',
            'tanggal_minum' => 'required|date' // Pastikan ini required agar validasinya kuat
        ]);

        $skor = $request->sudah_minum ? 100 : 0;

        // MENGGUNAKAN updateOrCreate
        $ttd = MinumTtd::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'tanggal_minum' => $request->tanggal_minum // Nama kolomnya tanggal_minum
            ],
            [
                'sudah_minum' => $request->sudah_minum,
                'skor' => $skor
            ]
        );

        return response()->json(['message' => 'Data Minum TTD berhasil disimpan', 'data' => $ttd], 201);
    }

    // ==========================================
    // 5. ZONA PERSONAL HYGIENE
    // ==========================================
    public function storePersonalHygiene(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'mandi_2x_sehari' => 'required|boolean',
            'pakai_sabun' => 'required|boolean',
            'sikat_gigi_pagi' => 'required|boolean',
            'sikat_gigi_malam' => 'required|boolean',
            'cuci_tangan_sebelum_makan' => 'required|boolean',
            'cuci_tangan_setelah_bab' => 'required|boolean',
            'pakai_alas_kaki' => 'required|boolean',
            'pakai_pakaian_bersih' => 'required|boolean',
            'handuk_pribadi_bersih' => 'required|boolean',
            'cuci_tangan_luar_rumah' => 'required|boolean',
        ]);

        $skor = 0;
        if ($request->mandi_2x_sehari) $skor += 10;
        if ($request->pakai_sabun) $skor += 10;
        if ($request->sikat_gigi_pagi) $skor += 10;
        if ($request->sikat_gigi_malam) $skor += 10;
        if ($request->cuci_tangan_sebelum_makan) $skor += 10;
        if ($request->cuci_tangan_setelah_bab) $skor += 10;
        if ($request->pakai_alas_kaki) $skor += 10;
        if ($request->pakai_pakaian_bersih) $skor += 10;
        if ($request->handuk_pribadi_bersih) $skor += 10;
        if ($request->cuci_tangan_luar_rumah) $skor += 10;

        if ($skor <= 30) $kategori = 'Sangat Kurang';
        elseif ($skor <= 50) $kategori = 'Kurang';
        elseif ($skor <= 70) $kategori = 'Cukup';
        elseif ($skor <= 90) $kategori = 'Baik';
        else $kategori = 'Sangat Baik';

        // MENGGUNAKAN updateOrCreate
        $hygiene = PersonalHygiene::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'tanggal' => $request->tanggal
            ],
            [
                'mandi_2x_sehari' => $request->mandi_2x_sehari,
                'pakai_sabun' => $request->pakai_sabun,
                'sikat_gigi_pagi' => $request->sikat_gigi_pagi,
                'sikat_gigi_malam' => $request->sikat_gigi_malam,
                'cuci_tangan_sebelum_makan' => $request->cuci_tangan_sebelum_makan,
                'cuci_tangan_setelah_bab' => $request->cuci_tangan_setelah_bab,
                'pakai_alas_kaki' => $request->pakai_alas_kaki,
                'pakai_pakaian_bersih' => $request->pakai_pakaian_bersih,
                'handuk_pribadi_bersih' => $request->handuk_pribadi_bersih,
                'cuci_tangan_luar_rumah' => $request->cuci_tangan_luar_rumah,
                'skor_total' => $skor,
                'kategori' => $kategori
            ]
        );

        return response()->json(['message' => 'Data Personal Hygiene berhasil disimpan', 'data' => $hygiene], 201);
    }


    /**
     * Mengambil data akumulasi 4 Zona + Pre-Test milik pengguna yang sedang login
     */
    public function getRapor(Request $request)
    {
        $userId = $request->user()->id;

        // Mengambil entri terbaru dari setiap zona berdasarkan user_id
        $preTest = \App\Models\PreTest::where('user_id', $userId)->latest()->first();
        $recall = \App\Models\RecallMakanan::where('user_id', $userId)->latest()->first();
        $fisik = \App\Models\AktivitasFisik::where('user_id', $userId)->latest()->first();
        $ttd = \App\Models\MinumTtd::where('user_id', $userId)->latest()->first();
        $hygiene = \App\Models\PersonalHygiene::where('user_id', $userId)->latest()->first();

        return response()->json([
            'message' => 'Berhasil mengambil data rapor kesehatanku',
            'data' => [
                'user' => $request->user(),
                'pre_test' => $preTest,
                'recall_makanan' => $recall,
                'aktivitas_fisik' => $fisik,
                'minum_ttd' => $ttd,
                'personal_hygiene' => $hygiene
            ]
        ], 200);
    }
}
