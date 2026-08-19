<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PreTest;
use App\Models\PostTest;
use App\Models\RecallMakanan;
use App\Models\AktivitasFisik;
use App\Models\MinumTtd;
use App\Models\PersonalHygiene;
use App\Models\TesKebugaran;

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
            ['skor' => $request->skor, 'is_completed' => true]
        );

        return response()->json([
            'message' => 'Data Pre-Test berhasil disimpan',
            'data' => $preTest
        ], 201);
    }

    // ==========================================
    // 6. FITUR POST-TEST
    // ==========================================
    public function storePostTest(Request $request)
    {
        $request->validate(['skor' => 'required|integer']);

        $postTest = \App\Models\PostTest::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['skor' => $request->skor, 'is_completed' => true]
        );

        return response()->json(['message' => 'Data Post-Test berhasil disimpan', 'data' => $postTest], 201);
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
    // MENGAMBIL DATA RECALL MAKANAN (UNTUK EDIT/VIEW)
    // ==========================================
    public function getRecallMakanan(Request $request)
    {
        $tanggal = $request->query('tanggal', date('Y-m-d'));

        $recall = RecallMakanan::where('user_id', $request->user()->id)
            ->where('tanggal', $tanggal)
            ->first();

        return response()->json([
            'message' => 'Berhasil mengambil data Recall Makanan',
            'data' => $recall // Akan bernilai null jika belum pernah mengisi di tanggal tsb
        ], 200);
    }

    // ==========================================
    // 3. ZONA AKTIVITAS FISIK
    // ==========================================
    public function storeAktivitasFisik(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'nama_aktivitas' => 'required|string',
            'durasi_menit' => 'required|integer'
        ]);

        $menit = $request->durasi_menit;
        $skor = 0; // Inisialisasi variabel skor

        // Tentukan Kategori sekaligus Skor-nya (Skala 0-100)
        if ($menit == 0) {
            $kategori = 'Sangat Kurang';
            $skor = 0;
        } elseif ($menit <= 29) {
            $kategori = 'Kurang';
            $skor = 25;
        } elseif ($menit <= 44) {
            $kategori = 'Cukup';
            $skor = 50;
        } elseif ($menit <= 59) {
            $kategori = 'Baik';
            $skor = 75;
        } else {
            $kategori = 'Sangat Baik';
            $skor = 100;
        }

        // MENGGUNAKAN updateOrCreate
        $aktivitas = AktivitasFisik::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'tanggal' => $request->tanggal
            ],
            [
                'nama_aktivitas' => $request->nama_aktivitas,
                'durasi_menit' => $menit,
                'kategori' => $kategori,
                'skor' => $skor // <-- KODE BARU: Menyimpan skor ke database
            ]
        );

        return response()->json(['message' => 'Data Aktivitas Fisik berhasil disimpan', 'data' => $aktivitas], 201);
    }

    // ==========================================
    // MENGAMBIL DATA AKTIVITAS FISIK (UNTUK EDIT/VIEW)
    // ==========================================
    public function getAktivitasFisik(Request $request)
    {
        $tanggal = $request->query('tanggal', date('Y-m-d'));

        $aktivitas = AktivitasFisik::where('user_id', $request->user()->id)
            ->where('tanggal', $tanggal)
            ->first();

        return response()->json([
            'message' => 'Berhasil mengambil data Aktivitas Fisik',
            'data' => $aktivitas // Akan bernilai null jika belum mengisi di tanggal tsb
        ], 200);
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
    // MENGAMBIL DATA MINUM TTD (UNTUK EDIT/VIEW)
    // ==========================================
    public function getMinumTtd(Request $request)
    {
        // Catatan: Kolom di tabel TTD bernama 'tanggal_minum', bukan 'tanggal'
        $tanggal = $request->query('tanggal', date('Y-m-d'));

        $ttd = MinumTtd::where('user_id', $request->user()->id)
            ->where('tanggal_minum', $tanggal)
            ->first();

        return response()->json([
            'message' => 'Berhasil mengambil data Minum TTD',
            'data' => $ttd // Akan bernilai null jika belum mengisi
        ], 200);
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

    // ==========================================
    // MENGAMBIL DATA PERSONAL HYGIENE (UNTUK EDIT/VIEW)
    // ==========================================
    public function getPersonalHygiene(Request $request)
    {
        $tanggal = $request->query('tanggal', date('Y-m-d'));

        $hygiene = PersonalHygiene::where('user_id', $request->user()->id)
            ->where('tanggal', $tanggal)
            ->first();

        return response()->json([
            'message' => 'Berhasil mengambil data Personal Hygiene',
            'data' => $hygiene // Akan bernilai null jika belum mengisi
        ], 200);
    }

    /**
     * Mengambil data akumulasi 4 Zona + Pre-Test & Post-Test milik pengguna
     * Berdasarkan filter tanggal (Default: Hari ini)
     */
    public function getRapor(Request $request)
    {
        $userId = $request->user()->id;

        // Menangkap request tanggal dari Android, jika kosong gunakan tanggal hari ini
        $tanggal = $request->query('tanggal', date('Y-m-d'));

        // PRE-TEST & POST-TEST PENGETAHUAN (Hanya dikerjakan 1 kali, jadi tidak difilter berdasarkan tanggal)
        $preTest = PreTest::where('user_id', $userId)->first();
        $postTest = PostTest::where('user_id', $userId)->first();

        // --- TAMBAHAN BARU: PRE-TEST & POST-TEST KEBUGARAN ---
        // (Sama seperti pengetahuan, tidak difilter tanggal agar selalu tampil di rapor)
        $preTestKebugaran = TesKebugaran::where('user_id', $userId)->where('tipe_tes', 'pre')->first();
        $postTestKebugaran = TesKebugaran::where('user_id', $userId)->where('tipe_tes', 'post')->first();

        // 4 ZONA HARIAN (Difilter secara ketat berdasarkan tanggal yang dipilih siswa)
        $recall = RecallMakanan::where('user_id', $userId)->where('tanggal', $tanggal)->first();
        $fisik = AktivitasFisik::where('user_id', $userId)->where('tanggal', $tanggal)->first();
        $ttd = MinumTtd::where('user_id', $userId)->where('tanggal_minum', $tanggal)->first();
        $hygiene = PersonalHygiene::where('user_id', $userId)->where('tanggal', $tanggal)->first();

        return response()->json([
            'message' => 'Berhasil mengambil data rapor kesehatanku',
            'data' => [
                'user' => $request->user(),
                'tanggal_filter' => $tanggal,
                'pre_test' => $preTest,
                'post_test' => $postTest,
                'pre_test_kebugaran' => $preTestKebugaran,
                'post_test_kebugaran' => $postTestKebugaran,
                'recall_makanan' => $recall,
                'aktivitas_fisik' => $fisik,
                'minum_ttd' => $ttd,
                'personal_hygiene' => $hygiene
            ]
        ], 200);
    }


    // ==========================================
    // FITUR BARU: TES KEBUGARAN (PRE & POST)
    // ==========================================
    public function storeTesKebugaran(Request $request)
    {
        $request->validate([
            'tipe_tes' => 'required|in:pre,post',
            'tanggal' => 'required|date',
            'lari_12_menit' => 'nullable|numeric',
            'push_up' => 'nullable|integer',
            'sit_up' => 'nullable|integer',
            'pull_up_chining' => 'nullable|integer',
            'shuttle_run' => 'nullable|numeric',
        ]);

        $user = $request->user();
        $gender = $user->gender; // 'L' atau 'P'

        // 1. Ambil nilai inputan (Jika kosong, anggap 0. Khusus Lari Shuttle, jika kosong anggap 999 detik alias sangat lambat)
        $lari = $request->lari_12_menit ?: 0;
        $push = $request->push_up ?: 0;
        $sit = $request->sit_up ?: 0;
        $pull = $request->pull_up_chining ?: 0;
        $shuttle = $request->shuttle_run ?: 999;

        // Inisialisasi Poin Dasar
        $pLari = 1;
        $pPush = 1;
        $pSit = 1;
        $pPull = 1;
        $pShuttle = 1;

        // 2. LOGIKA PENILAIAN LAKI-LAKI
        if ($gender == 'L') {
            // Lari
            if ($lari > 2800) $pLari = 5;
            elseif ($lari >= 2400) $pLari = 4;
            elseif ($lari >= 2000) $pLari = 3;
            elseif ($lari >= 1600) $pLari = 2;
            // Push-up & Sit-up
            if ($push > 40) $pPush = 5;
            elseif ($push >= 30) $pPush = 4;
            elseif ($push >= 20) $pPush = 3;
            elseif ($push >= 10) $pPush = 2;
            if ($sit > 40) $pSit = 5;
            elseif ($sit >= 30) $pSit = 4;
            elseif ($sit >= 20) $pSit = 3;
            elseif ($sit >= 10) $pSit = 2;
            // Pull-up
            if ($pull > 12) $pPull = 5;
            elseif ($pull >= 9) $pPull = 4;
            elseif ($pull >= 5) $pPull = 3;
            elseif ($pull >= 2) $pPull = 2;
            // Shuttle Run (Makin kecil makin bagus)
            if ($shuttle < 16) $pShuttle = 5;
            elseif ($shuttle <= 18) $pShuttle = 4;
            elseif ($shuttle <= 20) $pShuttle = 3;
            elseif ($shuttle <= 22) $pShuttle = 2;
        }
        // 3. LOGIKA PENILAIAN PEREMPUAN
        else {
            // Lari
            if ($lari > 2400) $pLari = 5;
            elseif ($lari >= 2000) $pLari = 4;
            elseif ($lari >= 1600) $pLari = 3;
            elseif ($lari >= 1200) $pLari = 2;
            // Push-up & Sit-up
            if ($push > 30) $pPush = 5;
            elseif ($push >= 20) $pPush = 4;
            elseif ($push >= 10) $pPush = 3;
            elseif ($push >= 5) $pPush = 2;
            if ($sit > 30) $pSit = 5;
            elseif ($sit >= 20) $pSit = 4;
            elseif ($sit >= 10) $pSit = 3;
            elseif ($sit >= 5) $pSit = 2;
            // Chining (Detik)
            if ($pull > 40) $pPull = 5;
            elseif ($pull >= 20) $pPull = 4;
            elseif ($pull >= 8) $pPull = 3;
            elseif ($pull >= 2) $pPull = 2;
            // Shuttle Run
            if ($shuttle < 18) $pShuttle = 5;
            elseif ($shuttle <= 20) $pShuttle = 4;
            elseif ($shuttle <= 22) $pShuttle = 3;
            elseif ($shuttle <= 24) $pShuttle = 2;
        }

        // 4. HITUNG SKOR TOTAL (Skala 100)
        $totalPoin = $pLari + $pPush + $pSit + $pPull + $pShuttle;
        $skorTotal = round(($totalPoin / 25) * 100);

        // 5. TENTUKAN KATEGORI
        if ($skorTotal > 80) $kategori = 'Sangat Baik';
        elseif ($skorTotal > 60) $kategori = 'Baik';
        elseif ($skorTotal > 40) $kategori = 'Cukup';
        elseif ($skorTotal > 20) $kategori = 'Kurang';
        else $kategori = 'Sangat Kurang';

        // 6. SIMPAN KE DATABASE (Tabel `tes_kebugarans`)
        $tes = TesKebugaran::updateOrCreate(
            [
                'user_id' => $user->id,
                'tipe_tes' => $request->tipe_tes // 'pre' atau 'post'
            ],
            [
                'tanggal' => $request->tanggal,
                'lari_12_menit' => $request->lari_12_menit,
                'push_up' => $request->push_up,
                'sit_up' => $request->sit_up,
                'pull_up_chining' => $request->pull_up_chining,
                'shuttle_run' => $request->shuttle_run,
                'skor_total' => $skorTotal,
                'kategori' => $kategori
            ]
        );

        return response()->json(['message' => 'Data Tes Kebugaran (' . strtoupper($request->tipe_tes) . ') berhasil disimpan', 'data' => $tes], 201);
    }

    // ==========================================
    // MENGAMBIL DATA TES KEBUGARAN (UNTUK EDIT/VIEW)
    // ==========================================
    public function getTesKebugaran(Request $request, $tipe_tes)
    {
        // Validasi tipe tes hanya boleh 'pre' atau 'post'
        if (!in_array($tipe_tes, ['pre', 'post'])) {
            return response()->json(['message' => 'Tipe tes tidak valid'], 400);
        }

        $tes = TesKebugaran::where('user_id', $request->user()->id)
            ->where('tipe_tes', $tipe_tes)
            ->first();

        return response()->json([
            'message' => 'Berhasil mengambil data tes kebugaran',
            'data' => $tes // Akan bernilai null jika belum pernah mengisi
        ], 200);
    }

    // ==========================================
    // FITUR BARU: LEADERBOARD AKTIVITAS FISIK
    // ==========================================
    public function getLeaderboardAktivitasFisik(Request $request)
    {
        $user = $request->user();

        // Menangkap parameter filter
        $sekolahId = $request->query('sekolah_id');
        $kelasId = $request->query('kelas_id');
        // Fitur Baru: 'lingkup' untuk mengatur scope siswa (default: 'sekolah')
        $lingkup = $request->query('lingkup', 'sekolah');

        $query = \App\Models\User::where('role', 'siswa')
            ->withSum('aktivitasFisik as total_skor_fisik', 'skor')
            ->withSum('aktivitasFisik as total_menit_fisik', 'durasi_menit');

        // --- LOGIKA PEMBATASAN DATA BERDASARKAN ROLE ---
        if ($user->role === 'siswa') {
            // Siswa SELALU HANYA BISA melihat sekolahnya sendiri
            $query->where('sekolah_id', $user->sekolah_id);

            // Jika siswa menekan tombol filter "Kelasku" di Android
            if ($lingkup === 'kelas') {
                $query->where('kelas_id', $user->kelas_id);
            }
        } elseif ($user->role === 'guru') {
            $query->where('sekolah_id', $user->sekolah_id);
            if ($kelasId) {
                $query->where('kelas_id', $kelasId);
            }
        } elseif ($user->role === 'admin') {
            if ($sekolahId) {
                $query->where('sekolah_id', $sekolahId);
            }
            if ($kelasId) {
                $query->where('kelas_id', $kelasId);
            }
        }

        // Eksekusi pengurutan
        $leaderboard = $query->orderByDesc('total_skor_fisik')
            ->orderByDesc('total_menit_fisik')
            ->get();

        // Filter: Buang siswa yang skornya 0
        $filteredData = $leaderboard->filter(function ($siswa) {
            return (int) $siswa->total_skor_fisik > 0;
        })->values();

        // Mapping data
        $finalData = $filteredData->map(function ($siswa, $index) {
            return [
                'peringkat' => $index + 1,
                'id' => $siswa->id,
                'nama' => $siswa->name,
                'kelas' => $siswa->kelas ? $siswa->kelas->nama_kelas : '-',
                'foto_profil' => $siswa->foto_profil,
                'total_skor' => (int) $siswa->total_skor_fisik,
                'total_menit' => (int) $siswa->total_menit_fisik,
            ];
        });

        return response()->json([
            'message' => 'Berhasil memuat Leaderboard Aktivitas Fisik',
            'data' => $finalData
        ], 200);
    }
}
