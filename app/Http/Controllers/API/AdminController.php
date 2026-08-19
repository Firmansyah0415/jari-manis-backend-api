<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function getDashboardData(Request $request)
    {
        $admin = $request->user();
        if ($admin->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak. Hanya untuk Super Admin.'], 403);
        }

        $sekolahId = $request->query('sekolah_id');
        $kelasId = $request->query('kelas_id');

        $siswaQuery = User::with(['kelas', 'sekolah'])->where('role', 'siswa');

        if ($sekolahId) $siswaQuery->where('sekolah_id', $sekolahId);
        if ($kelasId) $siswaQuery->where('kelas_id', $kelasId);

        $siswaList = $siswaQuery->get();
        $totalSiswa = $siswaList->count();

        $guruQuery = User::where('role', 'guru');
        if ($sekolahId) $guruQuery->where('sekolah_id', $sekolahId);
        $totalGuru = $guruQuery->count();

        $totalSkorGlobal = $siswaList->sum('total_skor');
        $rataRataSkor = $totalSiswa > 0 ? round($totalSkorGlobal / $totalSiswa, 1) : 0;

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

    public function getDaftarUser(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak. Hanya untuk Super Admin.'], 403);
        }

        $role = $request->query('role');
        $sekolahId = $request->query('sekolah_id');
        $kelasId = $request->query('kelas_id');
        $dateFrom = $request->query('created_from');
        $dateTo = $request->query('created_to');

        $query = User::with(['sekolah', 'kelas'])->whereIn('role', ['guru', 'siswa']);

        if ($role) $query->where('role', $role);
        if ($sekolahId) $query->where('sekolah_id', $sekolahId);
        if ($kelasId) $query->where('kelas_id', $kelasId);
        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);

        $users = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar user',
            'data' => $users
        ], 200);
    }

    // ==========================================
    // MESIN 2: EXPORT DATA PENELITIAN KE CSV (SUPER MESIN)
    // ==========================================
    public function exportCsv(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $tipe = $request->query('tipe', 'induk');
        $tanggal = $request->query('tanggal');

        $query = User::with(['sekolah', 'kelas'])->where('role', 'siswa');
        $siswas = $query->get();

        $fileName = "JariManis_" . ucfirst($tipe) . "_" . date('Ymd_His') . ".csv";

        $callback = function () use ($siswas, $tipe, $tanggal) {
            $file = fopen('php://output', 'w');

            // --- 1. TIPE: INDUK SISWA (OVERVIEW) ---
            if ($tipe === 'induk') {
                fputcsv($file, ['ID Siswa', 'Nama Lengkap', 'L/P', 'Asal Sekolah', 'Kelas', 'Total Hari Aktif', 'Total Skor Keseluruhan', 'Tanggal Mendaftar']);
                foreach ($siswas as $siswa) {
                    fputcsv($file, [
                        $siswa->id,
                        $siswa->name,
                        $siswa->gender,
                        $siswa->sekolah ? $siswa->sekolah->nama : '-',
                        $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
                        $siswa->total_hari_aktif,
                        $siswa->total_skor,
                        $siswa->created_at->format('d/m/Y')
                    ]);
                }
            }
            // --- 2. TIPE: AKADEMIK & KEBUGARAN (PRE/POST) ---
            elseif ($tipe === 'kebugaran') {
                fputcsv($file, ['ID Siswa', 'Nama Lengkap', 'Skor Pengetahuan (Pre)', 'Skor Pengetahuan (Post)', 'Lari 12M (Pre)', 'Lari 12M (Post)', 'Push Up (Pre)', 'Push Up (Post)', 'Sit Up (Pre)', 'Sit Up (Post)', 'Pull Up (Pre)', 'Pull Up (Post)', 'Shuttle Run (Pre)', 'Shuttle Run (Post)', 'Total Skor Kebugaran (Pre)', 'Total Skor Kebugaran (Post)', 'Kategori Kebugaran (Pre)', 'Kategori Kebugaran (Post)']);
                foreach ($siswas as $siswa) {
                    $preAkad = \App\Models\PreTest::where('user_id', $siswa->id)->sum('skor');
                    $postAkad = \App\Models\PostTest::where('user_id', $siswa->id)->sum('skor');

                    $preBugar = \App\Models\TesKebugaran::where('user_id', $siswa->id)->where('tipe_tes', 'pre')->first();
                    $postBugar = \App\Models\TesKebugaran::where('user_id', $siswa->id)->where('tipe_tes', 'post')->first();

                    fputcsv($file, [
                        $siswa->id,
                        $siswa->name,
                        $preAkad,
                        $postAkad,
                        $preBugar->lari_12_menit ?? '-',
                        $postBugar->lari_12_menit ?? '-',
                        $preBugar->push_up ?? '-',
                        $postBugar->push_up ?? '-',
                        $preBugar->sit_up ?? '-',
                        $postBugar->sit_up ?? '-',
                        $preBugar->pull_up_chining ?? '-',
                        $postBugar->pull_up_chining ?? '-',
                        $preBugar->shuttle_run ?? '-',
                        $postBugar->shuttle_run ?? '-',
                        $preBugar->total_skor ?? '-',
                        $postBugar->total_skor ?? '-',
                        $preBugar->kategori_hasil ?? '-',
                        $postBugar->kategori_hasil ?? '-'
                    ]);
                }
            }
            // --- 3. TIPE: LOG HARIAN (ZONA 2,3,4) ---
            elseif ($tipe === 'harian') {
                fputcsv($file, ['ID Siswa', 'Nama Lengkap', 'Tanggal Data', 'Z2: Nama Aktivitas Fisik', 'Z2: Durasi (Menit)', 'Z2: Kategori', 'Z3: Sudah Minum TTD?', 'Z4: Mandi 2x Sehari', 'Z4: Pakai Sabun', 'Z4: Sikat Gigi Pagi', 'Z4: Sikat Gigi Malam', 'Z4: Cuci Tangan Sblm Makan', 'Z4: Cuci Tangan Sth BAB', 'Z4: Pakai Alas Kaki', 'Z4: Pakaian Bersih', 'Z4: Handuk Bersih', 'Z4: Cuci Tangan dr Luar', 'Z4: Skor Total Hygiene', 'Z4: Kategori Hygiene']);
                foreach ($siswas as $siswa) {
                    $qFisik = \App\Models\AktivitasFisik::where('user_id', $siswa->id);
                    $qTtd = \App\Models\MinumTtd::where('user_id', $siswa->id);
                    $qHygiene = \App\Models\PersonalHygiene::where('user_id', $siswa->id);

                    if ($tanggal) {
                        $qFisik->whereDate('created_at', $tanggal);
                        $qTtd->where('tanggal_minum', $tanggal);
                        $qHygiene->where('tanggal', $tanggal);
                    }

                    $fisik = $qFisik->first();
                    $ttd = $qTtd->first();
                    $hygiene = $qHygiene->first();

                    fputcsv($file, [
                        $siswa->id,
                        $siswa->name,
                        $tanggal ?? 'Semua',
                        $fisik->nama_aktivitas ?? '-',
                        $fisik->durasi_menit ?? '-',
                        $fisik->kategori ?? '-',
                        isset($ttd) ? ($ttd->sudah_minum ? 'Ya' : 'Tidak') : '-',
                        isset($hygiene) ? ($hygiene->mandi_2x_sehari ? 'Ya' : 'Tidak') : '-',
                        isset($hygiene) ? ($hygiene->pakai_sabun ? 'Ya' : 'Tidak') : '-',
                        isset($hygiene) ? ($hygiene->sikat_gigi_pagi ? 'Ya' : 'Tidak') : '-',
                        isset($hygiene) ? ($hygiene->sikat_gigi_malam ? 'Ya' : 'Tidak') : '-',
                        isset($hygiene) ? ($hygiene->cuci_tangan_sebelum_makan ? 'Ya' : 'Tidak') : '-',
                        isset($hygiene) ? ($hygiene->cuci_tangan_setelah_bab ? 'Ya' : 'Tidak') : '-',
                        isset($hygiene) ? ($hygiene->pakai_alas_kaki ? 'Ya' : 'Tidak') : '-',
                        isset($hygiene) ? ($hygiene->pakai_pakaian_bersih ? 'Ya' : 'Tidak') : '-',
                        isset($hygiene) ? ($hygiene->handuk_pribadi_bersih ? 'Ya' : 'Tidak') : '-',
                        isset($hygiene) ? ($hygiene->cuci_tangan_luar_rumah ? 'Ya' : 'Tidak') : '-',
                        $hygiene->skor_total ?? '-',
                        $hygiene->kategori ?? '-'
                    ]);
                }
            }
            // --- 4. TIPE: RECALL MAKANAN (ZONA 1) ---
            elseif ($tipe === 'recall') {
                fputcsv($file, ['ID Siswa', 'Nama Lengkap', 'Tanggal Data', 'Skor Total Recall', 'Kategori Kalori', 'Detail Makanan Konsumsi (Dipisahkan Garis)']);
                foreach ($siswas as $siswa) {
                    $qRecall = \App\Models\RecallMakanan::where('user_id', $siswa->id);
                    if ($tanggal) {
                        $qRecall->whereDate('created_at', $tanggal);
                    }
                    $recalls = $qRecall->get();

                    if ($recalls->isEmpty()) {
                        fputcsv($file, [$siswa->id, $siswa->name, $tanggal ?? '-', '-', '-', 'Tidak ada data pengisian di tanggal ini']);
                    } else {
                        foreach ($recalls as $recall) {
                            $detailArr = is_string($recall->detail_jawaban) ? json_decode($recall->detail_jawaban, true) : $recall->detail_jawaban;
                            $detailStr = [];
                            if (is_array($detailArr)) {
                                foreach ($detailArr as $key => $val) {
                                    $detailStr[] = "$key: $val";
                                }
                            }
                            $formattedDetail = implode(" | ", $detailStr);

                            fputcsv($file, [
                                $siswa->id,
                                $siswa->name,
                                $recall->created_at->format('Y-m-d'),
                                $recall->skor_total ?? '-',
                                $recall->kategori ?? '-',
                                $formattedDetail
                            ]);
                        }
                    }
                }
            }

            fclose($file);
        };

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // MESIN 3: HAPUS USER & SEMUA DATANYA
    // ==========================================
    public function deleteUser(Request $request, $id)
    {
        $admin = $request->user();

        if ($admin->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'admin_password' => 'required|string'
        ], [
            'admin_password.required' => 'Password admin wajib diisi untuk verifikasi keamanan.'
        ]);

        if (!Hash::check($request->admin_password, $admin->password)) {
            return response()->json(['message' => 'Password Admin salah! Penghapusan dibatalkan.'], 401);
        }

        $targetUser = User::find($id);
        if (!$targetUser) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        if ($targetUser->id === $admin->id) {
            return response()->json(['message' => 'Anda tidak bisa menghapus akun Anda sendiri.'], 400);
        }

        if ($targetUser->foto_profil) {
            if (Storage::disk('public')->exists('profil/' . $targetUser->foto_profil)) {
                Storage::disk('public')->delete('profil/' . $targetUser->foto_profil);
            }
        }

        \App\Models\PreTest::where('user_id', $targetUser->id)->delete();
        \App\Models\PostTest::where('user_id', $targetUser->id)->delete();
        \App\Models\RecallMakanan::where('user_id', $targetUser->id)->delete();
        \App\Models\AktivitasFisik::where('user_id', $targetUser->id)->delete();
        \App\Models\MinumTtd::where('user_id', $targetUser->id)->delete();
        \App\Models\PersonalHygiene::where('user_id', $targetUser->id)->delete();
        \App\Models\TesKebugaran::where('user_id', $targetUser->id)->delete();

        $targetUser->delete();

        return response()->json(['message' => 'User beserta seluruh data dan fotonya berhasil dihapus permanen.'], 200);
    }
}
