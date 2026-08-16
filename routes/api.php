<?php

use App\Http\Controllers\API\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ZonaController;
use App\Http\Controllers\API\AkademikController;
use App\Http\Controllers\API\GuruController;

// ==========================================
// JALUR PUBLIK (Siapapun bisa akses)
// ==========================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// LETAKKAN DI SINI! (Di luar kurung Sanctum)
Route::get('/sekolah', [AkademikController::class, 'getSekolah']);
Route::get('/kelas/{sekolah_id}', [AkademikController::class, 'getKelasBySekolah']);


// ==========================================
// JALUR TERLINDUNGI (Hanya yang memiliki Token Valid)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Untuk Android mengambil profil yang sedang login beserta relasi sekolah dan kelasnya
    Route::get('/me', function (Request $request) {
        return response()->json($request->user()->load(['sekolah', 'kelas']));
    });
    Route::post('/profil/update', [AuthController::class, 'updateProfil']);

    // Rute Khusus Guru
    Route::get('/guru/siswa', [GuruController::class, 'getSiswaProgress']);
    Route::get('/guru/siswa/{id}/rapor', [GuruController::class, 'getSiswaRapor']);
    Route::get('/guru/leaderboard', [GuruController::class, 'getLeaderboard']);

    // ==========================================
    // AREA ADMIN (SUPER USER)
    // ==========================================
    Route::get('/admin/dashboard', [AdminController::class, 'getDashboardData']);
    Route::get('/admin/users', [AdminController::class, 'getDaftarUser']);
    Route::get('/admin/export-csv', [AdminController::class, 'exportCsv']);

    // ENDPOINT MVP JARI MANIS (4 ZONA + PRE-TEST)
    Route::post('/pre-test', [ZonaController::class, 'storePreTest']);
    Route::post('/post-test', [ZonaController::class, 'storePostTest']);
    Route::post('/recall-makanan', [ZonaController::class, 'storeRecallMakanan']);
    Route::post('/aktivitas-fisik', [ZonaController::class, 'storeAktivitasFisik']);
    Route::post('/minum-ttd', [ZonaController::class, 'storeMinumTtd']);
    Route::post('/personal-hygiene', [ZonaController::class, 'storePersonalHygiene']);

    // RUTE BARU: Mengambil Data Rapor Kesehatanku
    Route::get('/rapor', [ZonaController::class, 'getRapor']);
}); // <--- INI ADALAH PENUTUP SANCTUM