<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function getSiswaProgress(Request $request)
    {
        // 1. Ambil data guru yang sedang login
        $guru = $request->user();

        // 2. Pastikan hanya role 'guru' yang boleh mengakses API ini
        if ($guru->role !== 'guru') {
            return response()->json(['message' => 'Akses ditolak. Hanya untuk Guru.'], 403);
        }

        // 3. Tarik semua user dengan role 'siswa' yang SEKOLAH-nya sama dengan guru tersebut
        // Kita juga me-load relasi 'kelas' agar nanti bisa ditampilkan di UI Android
        $siswaList = User::where('role', 'siswa')
            ->where('sekolah_id', $guru->sekolah_id)
            ->with(['kelas']) // Bawa data nama kelasnya
            ->get();

        // 4. (Opsional) Jika guru punya kelas_id spesifik, kita bisa filter lagi
        if ($guru->kelas_id) {
            $siswaList = $siswaList->where('kelas_id', $guru->kelas_id);
        }

        return response()->json([
            'message' => 'Berhasil mengambil data siswa',
            'data' => $siswaList->values() // .values() memastikan datanya berupa array JSON yang rapi
        ], 200);
    }

    public function getSiswaRapor(Request $request, $id)
    {
        // Pastikan hanya guru yang bisa akses
        if ($request->user()->role !== 'guru') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Cari data siswa berdasarkan ID
        $siswa = User::with(['kelas', 'sekolah'])->where('role', 'siswa')->find($id);

        if (!$siswa) {
            return response()->json(['message' => 'Siswa tidak ditemukan.'], 404);
        }

        // Tarik data rapor milik siswa tersebut (sama persis dengan ZonaController)
        $preTest = \App\Models\PreTest::where('user_id', $id)->latest()->first();
        $recall = \App\Models\RecallMakanan::where('user_id', $id)->latest()->first();
        $fisik = \App\Models\AktivitasFisik::where('user_id', $id)->latest()->first();
        $ttd = \App\Models\MinumTtd::where('user_id', $id)->latest()->first();
        $hygiene = \App\Models\PersonalHygiene::where('user_id', $id)->latest()->first();

        return response()->json([
            'message' => 'Berhasil mengambil detail rapor siswa',
            'data' => [
                'user' => $siswa,
                'pre_test' => $preTest,
                'recall_makanan' => $recall,
                'aktivitas_fisik' => $fisik,
                'minum_ttd' => $ttd,
                'personal_hygiene' => $hygiene
            ]
        ], 200);
    }

    // --- FITUR LEADERBOARD (PAPAN PERINGKAT) ---
    public function getLeaderboard(Request $request)
    {
        $guru = $request->user();

        if ($guru->role !== 'guru') {
            return response()->json(['message' => 'Akses ditolak. Hanya untuk Guru.'], 403);
        }

        // 1. Tarik semua siswa di sekolah yang sama
        $siswaList = User::where('role', 'siswa')
            ->where('sekolah_id', $guru->sekolah_id)
            ->with(['kelas'])
            ->get();

        // 2. Hitung total skor untuk setiap siswa
        $leaderboard = $siswaList->map(function ($siswa) {
            $preTest = \App\Models\PreTest::where('user_id', $siswa->id)->latest()->first();
            $recall = \App\Models\RecallMakanan::where('user_id', $siswa->id)->latest()->first();
            $fisik = \App\Models\AktivitasFisik::where('user_id', $siswa->id)->latest()->first();
            $ttd = \App\Models\MinumTtd::where('user_id', $siswa->id)->latest()->first();
            $hygiene = \App\Models\PersonalHygiene::where('user_id', $siswa->id)->latest()->first();

            // Karena Aktivitas Fisik tidak menyimpan 'skor_total', kita konversi dari Kategori
            $skorFisik = 0;
            if ($fisik) {
                $skorFisik = match ($fisik->kategori) {
                    'Sangat Baik' => 100,
                    'Baik' => 80,
                    'Cukup' => 60,
                    'Kurang' => 40,
                    default => 20,
                };
            }

            // Jumlahkan seluruh skor
            $totalSkor = ($preTest->skor ?? 0) +
                ($recall->skor_total ?? 0) +
                $skorFisik +
                ($ttd->skor ?? 0) +
                ($hygiene->skor_total ?? 0);

            // Tambahkan properti 'total_skor' sementara ke object siswa
            $siswa->total_skor = $totalSkor;
            return $siswa;
        });

        // 3. Urutkan dari yang terbesar, tanpa batasan (semua siswa tampil)
        $sortedSiswa = $leaderboard->sortByDesc('total_skor')->values();

        return response()->json([
            'message' => 'Berhasil mengambil leaderboard',
            'data' => $sortedSiswa
        ], 200);
    }
}
