<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

// Jalur Publik (Siapapun bisa akses)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Jalur Terlindungi (Hanya yang memiliki Token Valid yang bisa masuk)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Untuk Android mengambil profil yang sedang login
    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    });
});
