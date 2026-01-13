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
        // Ambil donasi beserta relasi donatur
        $donasis = Donasi::with('donatur')
            ->whereIn('verifikasi_admin', ['pending', 'disetujui', 'ditolak']) // filter status
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($donasis);
    }

    // ==========================
    // UPDATE VERIFIKASI ADMIN
    // ==========================
    public function updateVerifikasi(Request $request)
    {
        // Validasi request
        $request->validate([
            'donasi_id' => 'required|exists:donasis,id',
            'verifikasi_admin' => 'required|in:pending,disetujui,ditolak'
        ]);

        $donasi = Donasi::find($request->donasi_id);

        // Safety check meski sudah ada validasi exists
        if (!$donasi) {
            return response()->json([
                'message' => 'Donasi tidak ditemukan'
            ], 404);
        }

        // Cek jika sudah diverifikasi sebelumnya
        if ($donasi->verifikasi_admin !== 'pending') {
            return response()->json([
                'message' => 'Donasi ini sudah diverifikasi sebelumnya'
            ], 400);
        }

        // Update status verifikasi admin
        $donasi->verifikasi_admin = $request->verifikasi_admin;
        $donasi->save();

        return response()->json([
            'message' => 'Verifikasi admin berhasil diperbarui',
            'donasi' => $donasi->load('donatur')
        ]);
    }

    // ==========================
    // OPTIONAL: GET DONASI BY STATUS
    // ==========================
    public function getByStatus($status)
    {
        $allowed = ['pending', 'disetujui', 'ditolak'];

        if (!in_array($status, $allowed)) {
            return response()->json([
                'message' => 'Status tidak valid'
            ], 400);
        }

        $donasis = Donasi::with('donatur')
            ->where('verifikasi_admin', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($donasis);
    }

    // ==========================
// DASHBOARD DATA UNTUK ADMIN
// ==========================
public function dashboard()
{
    // Hitung donasi berdasarkan status
    $stats = [
        'pending' => Donasi::where('verifikasi_admin', 'pending')->count(),
        'disetujui' => Donasi::where('verifikasi_admin', 'disetujui')->count(),
        'ditolak' => Donasi::where('verifikasi_admin', 'ditolak')->count(),
    ];

    // Ambil 5 donasi terbaru yang perlu verifikasi
    $recent = Donasi::with('donatur')
        ->where('verifikasi_admin', 'pending')
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    return response()->json([
        'stats' => $stats,
        'recent' => $recent
    ]);
}
}
