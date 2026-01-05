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

    Route::post('/create-admin', [UserController::class, 'createAdmin']);
    Route::post('/create-petugas', [UserController::class, 'createPetugas']);
});

// REGISTER ADMIN PERTAMA
Route::post('/register-admin', [AuthController::class, 'registerAdmin']);

// =====================
// DASHBOARD USER
// =====================
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    Route::get('/user/dashboard', function () {
        return response()->json([
            'message' => 'Welcome User Dashboard'
        ]);
    });
});

// =====================
// AUTH
// =====================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// =====================
// API RESOURCE (WAJIB LOGIN)
// =====================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('donatur', DonaturController::class);
    Route::apiResource('donasi', DonasiController::class);

    // LAPORAN
    Route::get('/laporan', [DonasiController::class, 'laporan']);
});
