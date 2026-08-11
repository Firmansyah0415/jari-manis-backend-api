<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function getDashboardData(Request $request)
    {
        // 1. Validasi Keamanan (Hanya Admin)
        $admin = $request->user();
        if ($admin->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak. Hanya untuk Super Admin.'], 403);
        }

        // 2. Tangkap parameter filter (jika Admin memilih sekolah atau kelas tertentu)
        $sekolahId = $request->query('sekolah_id');
        $kelasId = $request->query('kelas_id');

        // 3. Tarik Data Siswa dengan Relasinya
        $siswaQuery = User::with(['kelas', 'sekolah'])->where('role', 'siswa');

        // Terapkan filter jika Admin menggunakan dropdown
        if ($sekolahId) {
            $siswaQuery->where('sekolah_id', $sekolahId);
        }
        if ($kelasId) {
            $siswaQuery->where('kelas_id', $kelasId);
        }

        // Eksekusi query untuk mendapatkan koleksi data siswa
        $siswaList = $siswaQuery->get();

        // 4. Hitung Statistik
        $totalSiswa = $siswaList->count();

        // Hitung Total Guru (Sesuaikan dengan filter sekolah jika ada)
        $guruQuery = User::where('role', 'guru');
        if ($sekolahId) {
            $guruQuery->where('sekolah_id', $sekolahId);
        }
        $totalGuru = $guruQuery->count();

        // Hitung rata-rata skor menggunakan accessor 'total_skor' (Collection Sum)
        $totalSkorGlobal = $siswaList->sum('total_skor');
        $rataRataSkor = $totalSiswa > 0 ? round($totalSkorGlobal / $totalSiswa, 1) : 0;

        // 5. Buat Leaderboard Global (Urutkan dari skor tertinggi)
        // Kita batasi Top 100 agar aplikasi Android tidak berat saat memuat
        $leaderboard = $siswaList->sortByDesc('total_skor')->values()->take(100);

        return response()->json([
            'message' => 'Berhasil mengambil data dashboard admin',
            'data' => [
                'statistik' => [
                    'total_siswa' => $totalSiswa,
                    'total_guru' => $totalGuru,
                    'rata_rata_skor' => $rataRataSkor
                ],
                'leaderboard' => $leaderboard
            ]
        ], 200);
    }


    // ==========================================
    // MESIN 1: DAFTAR SELURUH USER (GURU & SISWA)
    // ==========================================
    public function getDaftarUser(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak. Hanya untuk Super Admin.'], 403);
        }

        // Tangkap parameter filter
        $role = $request->query('role'); // 'guru' atau 'siswa'
        $sekolahId = $request->query('sekolah_id');
        $kelasId = $request->query('kelas_id');
        $dateFrom = $request->query('created_from');
        $dateTo = $request->query('created_to');

        // Tarik data guru dan siswa (kecuali admin itu sendiri)
        $query = User::with(['sekolah', 'kelas'])->whereIn('role', ['guru', 'siswa']);

        if ($role) $query->where('role', $role);
        if ($sekolahId) $query->where('sekolah_id', $sekolahId);
        if ($kelasId) $query->where('kelas_id', $kelasId);

        // Filter rentang waktu pendaftaran
        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);

        // Urutkan dari yang terbaru mendaftar
        $users = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar user',
            'data' => $users
        ], 200);
    }

    // ==========================================
    // MESIN 2: EXPORT DATA PENELITIAN KE CSV
    // ==========================================
    public function exportCsv(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Admin bisa mengekspor semua data, atau memfilternya per sekolah/kelas
        $sekolahId = $request->query('sekolah_id');
        $kelasId = $request->query('kelas_id');

        // Tarik data SISWA beserta sekolah & kelas
        // Kita hapus relasi zona dari sini karena kita akan menghitungnya langsung dari database agar 100% akurat
        $query = User::with(['sekolah', 'kelas'])->where('role', 'siswa');

        if ($sekolahId) $query->where('sekolah_id', $sekolahId);
        if ($kelasId) $query->where('kelas_id', $kelasId);

        $siswas = $query->get();

        // Siapkan Headings / Kolom Tabel sesuai standar SPSS/Excel
        $columns = [
            'ID Siswa',
            'Nama Lengkap',
            'L/P',
            'Asal Sekolah',
            'Kelas',
            'Skor Pre-Test',
            'Total Skor Recall Makanan',
            'Total Skor Aktivitas Fisik',
            'Total Skor TTD',
            'Total Skor Hygiene',
            'Skor Post-Test',
            'TOTAL SKOR KESELURUHAN',
            'Total Hari Aktif',
            'Tanggal Mendaftar'
        ];

        // Buat file CSV secara dinamis (Streaming) agar tidak membebani RAM Server
        $callback = function () use ($siswas, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns); // Tulis baris pertama (Judul Kolom)

            foreach ($siswas as $siswa) {
                // Kalkulasi menembak LANGSUNG ke Database agar dijamin 100% akurat dan update
                $skorPreTest = \App\Models\PreTest::where('user_id', $siswa->id)->sum('skor');
                $skorPostTest = \App\Models\PostTest::where('user_id', $siswa->id)->sum('skor');

                $skorRecall = \App\Models\RecallMakanan::where('user_id', $siswa->id)->sum('skor_total');
                $skorFisik = \App\Models\AktivitasFisik::where('user_id', $siswa->id)->sum('skor');
                $skorTtd = \App\Models\MinumTtd::where('user_id', $siswa->id)->sum('skor');
                $skorHygiene = \App\Models\PersonalHygiene::where('user_id', $siswa->id)->sum('skor_total');

                // Susun data per baris sesuai urutan judul kolom
                $row = [
                    $siswa->id,
                    $siswa->name,
                    $siswa->gender,
                    $siswa->sekolah ? $siswa->sekolah->nama : '-',
                    $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
                    $skorPreTest,
                    $skorRecall,
                    $skorFisik,
                    $skorTtd,
                    $skorHygiene,
                    $skorPostTest,
                    $siswa->total_skor, // Memanggil accessor dari User.php
                    $siswa->total_hari_aktif, // Memanggil accessor dari User.php
                    $siswa->created_at->format('d/m/Y')
                ];
                fputcsv($file, $row); // Tulis baris data
            }
            fclose($file);
        };

        // Header agar file yang terdownload dikenali sebagai CSV
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=Data_Penelitian_JariManis.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream($callback, 200, $headers);
    }
}
