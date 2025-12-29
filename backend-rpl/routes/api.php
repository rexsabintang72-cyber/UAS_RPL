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

    // BUAT ADMIN / PETUGAS VIA API (masih pakai middleware)
    Route::post('/create-admin', [UserController::class, 'createAdmin']);
    Route::post('/create-petugas', [UserController::class, 'createPetugas']);
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
// LOGOUT & API RESOURCE
// =====================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('donatur', DonaturController::class);
    Route::apiResource('donasi', DonasiController::class);
    Route::get('laporan', [DonasiController::class, 'laporan']);
});
