<?php

namespace App\Http\Controllers;

use App\Models\Donatur;
use App\Models\Donasi;

class DashboardController extends Controller
{
    public function index()
    {
        $totalDonatur = Donatur::count();
        $totalDonasi = Donasi::count();

        $totalDanaMasuk = Donasi::sum('jumlah');

        $totalDanaTersalurkan = Donasi::where('status', 'sudah disalurkan')
            ->sum('jumlah');

        $donasiTerbaru = Donasi::with('donatur')
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_donatur' => $totalDonatur,
                'total_donasi' => $totalDonasi,
                'total_dana_masuk' => $totalDanaMasuk,
                'total_dana_tersalurkan' => $totalDanaTersalurkan,
                'sisa_dana' => $totalDanaMasuk - $totalDanaTersalurkan,
                'donasi_terbaru' => $donasiTerbaru
            ]
        ]);
    }
}
