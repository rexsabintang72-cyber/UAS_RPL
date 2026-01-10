<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonaturController;
use App\Http\Controllers\DonasiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminDonasiController;


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
// DASHBOARD PETUGAS
// =====================
Route::middleware(['auth:sanctum', 'role:petugas'])->group(function () {
    Route::get('/petugas/dashboard', function () {
        return response()->json([
            'message' => 'Welcome Petugas Dashboard'
        ]);
    });

    // contoh route untuk update status bantuan
    Route::post('/petugas/update-bantuan', [DonasiController::class, 'updateStatusByPetugas']);
});


// =====================
// AUTH
// =====================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// =====================
// API RESOURCE (WAJIB LOGIN)
// =====================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); //ini tadi tak ubah

    Route::apiResource('donatur', DonaturController::class);
    Route::apiResource('donasi', DonasiController::class);

    // LAPORAN
    Route::get('/laporan', [DonasiController::class, 'laporan']);
});

// =====================
// LAPORAN PETUGAS
// =====================
Route::middleware(['auth:sanctum', 'role:petugas'])->group(function () {
    Route::get('/petugas/laporan', [DonasiController::class, 'laporanPetugas']);
});

//untuk pdf
Route::middleware('auth:sanctum')->get(
    '/laporan-pdf',
    [DonasiController::class, 'laporanPdf']
);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::put('/donasi/{id}/verifikasi', [DonasiController::class, 'verifikasiAdmin']);
});

// ==========================
// ROUTE ADMIN DONASI
// ==========================
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDonasiController::class, 'dashboard']);

    Route::get('/admin/donasi', [AdminDonasiController::class, 'index']);
    Route::post('/admin/donasi/verifikasi', [AdminDonasiController::class, 'updateVerifikasi']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::post('/user/profile', [UserController::class, 'updateProfile']);
});

Route::delete('/user/profile/photo', [UserController::class, 'deletePhoto'])
    ->middleware('auth:sanctum');
