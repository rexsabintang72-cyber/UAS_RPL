<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DonaturController;
use App\Http\Controllers\DonasiController;

Route::apiResource('donatur', DonaturController::class);
Route::apiResource('donasi', DonasiController::class);
Route::get('laporan', [DonasiController::class, 'laporan']);
