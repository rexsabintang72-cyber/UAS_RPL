<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonaturController;
use App\Http\Controllers\DonasiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;

// =====================
// DASHBOARD ADMIN
// =====================
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return response()->json([
            'message' => 'Welcome Admin Dashboard'
        ]);
    });

    // BUAT ADMIN / PETUGAS VIA API
    Route::post('/create-admin', [UserController::class, 'createAdmin']);
    Route::post('/create-petugas', [UserController::class, 'createPetugas']);

    // Admin hanya bisa create/update/delete Donatur
    Route::post('/donatur', [DonaturController::class, 'store']);
    Route::put('/donatur/{donatur}', [DonaturController::class, 'update']);
    Route::delete('/donatur/{donatur}', [DonaturController::class, 'destroy']);

    // Admin hanya bisa create/update/delete Donasi
    Route::post('/donasi', [DonasiController::class, 'store']);
    Route::put('/donasi/{donasi}', [DonasiController::class, 'update']);
    Route::delete('/donasi/{donasi}', [DonasiController::class, 'destroy']);
});

// REGISTER ADMIN PERTAMA
Route::post('/register-admin', [AuthController::class, 'registerAdmin']);


// =====================
// DASHBOARD USER BIASA
// =====================
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    Route::get('/user/dashboard', function () {
        return response()->json([
            'message' => 'Welcome User Dashboard'
        ]);
    });
});

// =====================
// DASHBOARD UMUM (auth)
// =====================
Route::middleware('auth:sanctum')->get('/dashboard', [DashboardController::class, 'index']);

// =====================
// FILE UPLOAD & LISTING
// =====================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/upload', [FileController::class, 'upload']);
    Route::get('/files', [FileController::class, 'index']);
});

// =====================
// AUTH REGULAR USER
// =====================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// =====================
// LOGOUT
// =====================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Semua user login bisa lihat data Donatur / Donasi
    Route::get('/donatur', [DonaturController::class, 'index']);
    Route::get('/donatur/{donatur}', [DonaturController::class, 'show']);


    // Laporan bisa diakses semua user login
    Route::get('/laporan', [DonasiController::class, 'laporan']);

    Route::get('/donasi', [DonasiController::class, 'index']);
    Route::get('/donasi/{donasi}', [DonasiController::class, 'show']);
});
