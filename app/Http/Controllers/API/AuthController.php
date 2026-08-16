<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // --- FITUR REGISTER ---
    public function register(Request $request)
    {
        // Validasi inputan ditambah sekolah & kelas
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:siswa,guru,admin',
            'gender' => 'required|in:L,P',
            'sekolah_id' => 'nullable|exists:sekolahs,id', // Tambahan baru
            'kelas_id' => 'nullable|exists:kelas,id'       // Tambahan baru
        ]);

        // Simpan ke database
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'gender' => $request->gender,
            'sekolah_id' => $request->sekolah_id, // Tambahan baru
            'kelas_id' => $request->kelas_id,     // Tambahan baru
        ]);

        // Buatkan token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // --- FITUR LOGIN ---
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari user berdasarkan username
        $user = User::where('username', $request->username)->first();

        // Cek kecocokan password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Username atau Password salah'
            ], 401);
        }

        // Hapus token lama agar aman (opsional tapi sangat disarankan)
        $user->tokens()->delete();

        // Buat token baru
        $token = $user->createToken('auth_token')->plainTextToken;

        // --- TAMBAHAN LOGIKA PRE-TEST ---
        $isPretestDone = false;
        if ($user->role === 'siswa') {
            // Mengecek ke tabel pre_tests berdasarkan user_id pengguna yang sedang login
            // Pastikan model PreTest tersedia (huruf besar/kecil sesuaikan dengan nama Model Anda)
            $isPretestDone = \App\Models\PreTest::where('user_id', $user->id)->exists();
        }
        // -------------------------------

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
            'is_pretest_done' => $isPretestDone // Mengirim status ke Android
        ], 200);
    }

    // --- FITUR UPDATE DATA USER ---
    public function updateProfil(Request $request)
    {
        $user = $request->user();

        // Validasi data yang masuk
        $request->validate([
            'name' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gender' => 'nullable|in:L,P',
            'sekolah_id' => 'nullable|exists:sekolahs,id',
            'kelas_id' => 'nullable|exists:kelas,id'
        ]);

        // 1. Update Nama
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        // 2. Update Username
        if ($request->filled('username')) {
            $user->username = $request->username;
        }

        // 3. Update Password
        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        // 4. Update Gender, Sekolah, dan Kelas
        if ($request->filled('gender')) {
            $user->gender = $request->gender;
        }
        if ($request->filled('sekolah_id')) {
            $user->sekolah_id = $request->sekolah_id;
        }
        if ($request->filled('kelas_id')) {
            $user->kelas_id = $request->kelas_id;
        }

        // 5. Update Foto Profil
        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete('profil/' . $user->foto_profil);
            }
            $fileName = time() . '_' . $request->file('foto_profil')->getClientOriginalName();
            $request->file('foto_profil')->storeAs('profil', $fileName, 'public');
            $user->foto_profil = $fileName;
        }

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => $user->load(['sekolah', 'kelas'])
        ], 200);
    }

    // --- FITUR LOGOUT ---
    public function logout(Request $request)
    {
        // Hapus token pengguna yang sedang memanggil request ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ], 200);
    }
}
