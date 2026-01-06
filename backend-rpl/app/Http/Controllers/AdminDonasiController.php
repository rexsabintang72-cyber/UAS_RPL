<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Donasi;

class AdminDonasiController extends Controller
{
    // ==========================
    // GET SEMUA DONASI UNTUK VERIFIKASI
    // ==========================
    public function index()
    {
        // Ambil semua donasi beserta relasi donatur
        $donasis = Donasi::with('donatur')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($donasis);
    }

    // ==========================
    // UPDATE VERIFIKASI ADMIN
    // ==========================
    public function updateVerifikasi(Request $request)
    {
        $request->validate([
            'donasi_id' => 'required|exists:donasis,id',
            'verifikasi_admin' => 'required|in:pending,disetujui,ditolak'
        ]);

        $donasi = Donasi::find($request->donasi_id);
        $donasi->verifikasi_admin = $request->verifikasi_admin;
        $donasi->save();

        return response()->json([
            'message' => 'Verifikasi admin berhasil diperbarui',
            'donasi' => $donasi->load('donatur')
        ]);
    }
}
