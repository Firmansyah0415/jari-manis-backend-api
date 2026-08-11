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
            'data' => $siswaList->values()
        ], 200);
    }

    public function getSiswaRapor(Request $request, $id)
    {
        // Izinkan Guru ATAU Admin untuk melihat detail rapor siswa
        $userRole = $request->user()->role;
        if ($userRole !== 'guru' && $userRole !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // --- TAMBAHAN BARU: Tangkap filter tanggal, default ke hari ini ---
        $tanggal = $request->query('tanggal', date('Y-m-d'));

        // Cari data siswa berdasarkan ID
        $siswa = User::with(['kelas', 'sekolah'])->where('role', 'siswa')->find($id);

        if (!$siswa) {
            return response()->json(['message' => 'Siswa tidak ditemukan.'], 404);
        }

        // PRE-TEST & POST-TEST (Statis, karena hanya dikerjakan 1 kali)
        $preTest = \App\Models\PreTest::where('user_id', $id)->first();
        $postTest = \App\Models\PostTest::where('user_id', $id)->first(); // <--- BARU

        // 4 ZONA HARIAN (Difilter berdasarkan tanggal yang dipilih Guru)
        $recall = \App\Models\RecallMakanan::where('user_id', $id)->where('tanggal', $tanggal)->first();
        $fisik = \App\Models\AktivitasFisik::where('user_id', $id)->where('tanggal', $tanggal)->first();
        $ttd = \App\Models\MinumTtd::where('user_id', $id)->where('tanggal_minum', $tanggal)->first();
        $hygiene = \App\Models\PersonalHygiene::where('user_id', $id)->where('tanggal', $tanggal)->first();

        return response()->json([
            'message' => 'Berhasil mengambil detail rapor siswa',
            'data' => [
                'user' => $siswa,
                'tanggal_filter' => $tanggal, // <--- BARU
                'pre_test' => $preTest,
                'post_test' => $postTest,     // <--- BARU
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

        // 2 & 3. Karena di User.php kita sudah membuat Accessor getTotalSkorAttribute,
        // variabel 'total_skor' sudah OTOMATIS menempel di setiap data siswa!
        // Kita cukup mengurutkannya saja dari yang terbesar ke terkecil. Sangat hemat kode!
        $sortedSiswa = $siswaList->sortByDesc('total_skor')->values();

        return response()->json([
            'message' => 'Berhasil mengambil leaderboard',
            'data' => $sortedSiswa
        ], 200);
    }
}
