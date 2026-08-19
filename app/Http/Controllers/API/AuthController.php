<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator; // <-- Tambahan

class AuthController extends Controller
{
    // --- FITUR REGISTER ---
    public function register(Request $request)
    {
        // Gunakan Validator::make agar kita bisa mengontrol format pesan error
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8', // <-- DIUBAH MENJADI MINIMAL 8
            'role' => 'required|in:siswa,guru,admin',
            'gender' => 'required|in:L,P',
            'sekolah_id' => 'nullable|exists:sekolahs,id',
            'kelas_id' => 'nullable|exists:kelas,id'
        ], [
            // Kustomisasi pesan error ke Bahasa Indonesia
            'username.unique' => 'Username ini sudah dipakai, silakan cari yang lain.',
            'password.min' => 'Password terlalu pendek! Minimal harus 8 karakter.'
        ]);

        if ($validator->fails()) {
            // Mengirim HANYA kalimat error pertama agar Android mudah membacanya
            return response()->json([
                'message' => $validator->errors()->first()
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'gender' => $request->gender,
            'sekolah_id' => $request->sekolah_id,
            'kelas_id' => $request->kelas_id,
        ]);

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
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Kolom Username dan Password wajib diisi!'], 400);
        }

        $user = User::where('username', $request->username)->first();

        // Pesan Error Jika Salah Password/Username
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Username tidak ditemukan atau Password salah!'
            ], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        $isPretestDone = false;
        if ($user->role === 'siswa') {
            $isPretestDone = \App\Models\PreTest::where('user_id', $user->id)->exists();
        }

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
            'is_pretest_done' => $isPretestDone
        ], 200);
    }

    // --- FITUR UPDATE DATA USER ---
    public function updateProfil(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8', // <-- DIUBAH JUGA DI SINI (MINIMAL 8)
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gender' => 'nullable|in:L,P',
            'sekolah_id' => 'nullable|exists:sekolahs,id',
            'kelas_id' => 'nullable|exists:kelas,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        if ($request->filled('name')) $user->name = $request->name;
        if ($request->filled('username')) $user->username = $request->username;
        if ($request->filled('password')) $user->password = Hash::make($request->password);
        if ($request->filled('gender')) $user->gender = $request->gender;
        if ($request->filled('sekolah_id')) $user->sekolah_id = $request->sekolah_id;
        if ($request->filled('kelas_id')) $user->kelas_id = $request->kelas_id;

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
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil'], 200);
    }
}
