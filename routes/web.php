<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/buat-storage', function () {
    $targetFolder = storage_path('app/public');
    $linkFolder = $_SERVER['DOCUMENT_ROOT'] . '/storage';

    $pesan = "Target Asli: $targetFolder <br> Lokasi Shortcut: $linkFolder <br><br>";

    // 1. Cek dan paksa hapus jika ada symlink/shortcut yang nyangkut
    if (file_exists($linkFolder) || is_link($linkFolder)) {
        if (is_link($linkFolder)) {
            unlink($linkFolder); // Hapus shortcut mati
            $pesan .= "✔️ Shortcut lama yang rusak berhasil dibersihkan.<br>";
        } elseif (is_dir($linkFolder)) {
            return $pesan . "❌ <b>GAGAL:</b> Ada folder asli bernama 'storage' di dalam direktori web Anda. Anda harus menghapus folder itu secara manual via File Manager Hostinger terlebih dahulu.";
        } else {
            unlink($linkFolder);
        }
    } else {
        $pesan .= "✔️ Tidak ada shortcut lama yang nyangkut.<br>";
    }

    // 2. Buat symlink baru
    try {
        symlink($targetFolder, $linkFolder);
        return $pesan . "🚀 <b>SUKSES BESAR!</b> Shortcut baru berhasil disambungkan. Silakan cek gambar di aplikasi Android Anda!";
    } catch (\Exception $e) {
        return $pesan . "❌ <b>GAGAL MEMBUAT SHORTCUT:</b> " . $e->getMessage();
    }
});
