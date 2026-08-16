<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/fix-gambar', function () {
    $target = storage_path('app/public/profil');


    $link = $_SERVER['DOCUMENT_ROOT'] . '/profil';

    if (!file_exists($target)) {
        return 'Folder target di storage belum ada. Pastikan sudah ada gambar yang terupload ke sistem.';
    }

    if (file_exists($link)) {
        return 'Gagal: Folder "profil" sudah ada di root. Hapus dulu via File Manager.';
    }

    try {
        symlink($target, $link);
        return 'Sukses! Symlink profil berhasil dibuat. Silakan cek gambar Anda.';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/bersihkan-cache', function () {
    try {
        Artisan::call('optimize:clear');
        return '🚀 BINGO! Seluruh Cache Server Berhasil Dihapus!';
    } catch (\Exception $e) {
        return '❌ Gagal: ' . $e->getMessage();
    }
});
