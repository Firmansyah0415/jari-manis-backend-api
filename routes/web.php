<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/buat-storage', function () {
    $targetFolder = storage_path('app/public');
    // $_SERVER['DOCUMENT_ROOT'] akan otomatis mencari folder root internet Hostinger (public_html)
    $linkFolder = $_SERVER['DOCUMENT_ROOT'] . '/storage';

    if (file_exists($linkFolder)) {
        return 'Folder storage sudah ada. Hapus dulu yang lama.';
    }

    try {
        symlink($targetFolder, $linkFolder);
        return 'SUKSES! Symlink berhasil dibuat di: ' . $linkFolder;
    } catch (\Exception $e) {
        return 'GAGAL: ' . $e->getMessage();
    }
});
