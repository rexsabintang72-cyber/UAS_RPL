<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonasiController extends Controller
{
    public function index()
    {
         return Donasi::with('donatur')->orderBy('tanggal', 'desc')->get();
    }

public function store(Request $request)
{
    $request->validate([
        'donatur_id' => 'required|exists:donaturs,id',
        'tanggal' => 'required|date',
        'jenis_donasi' => 'required',
        'jumlah' => 'required|numeric',
        'status' => 'required'
    ]);

    $donasi = Donasi::create($request->all());

    // ⬇️ INI PENTING
    return $donasi->load('donatur');
}


    public function show(Donasi $donasi)
    {
        return $donasi->load('donatur');
    }

    public function update(Request $request, Donasi $donasi)
    {
        $donasi->update($request->all());
        return $donasi;
    }

    public function destroy(Donasi $donasi)
    {
        $donasi->delete();
        return response()->noContent();
    }

    // ======================
    // LAPORAN (FIX TOTAL)
    // ======================

public function laporan(Request $request)
{
    // FILTER BULAN & TAHUN
    $bulan = $request->bulan ?? date('m');
    $tahun = $request->tahun ?? date('Y');

    // DATA DETAIL
    $donasis = Donasi::with('donatur')
        ->whereMonth('tanggal', $bulan)
        ->whereYear('tanggal', $tahun)
        ->get();

    // SUMMARY
    $donasiMasuk = $donasis->sum('jumlah');
    $tersalurkan = $donasis->where('status', 'sudah disalurkan')->sum('jumlah');
    $sisaDana = $donasiMasuk - $tersalurkan;

    // 🔥 TREND DONASI PER BULAN (1 TAHUN)
    $trend = Donasi::selectRaw('MONTH(tanggal) as bulan, SUM(jumlah) as total')
        ->whereYear('tanggal', $tahun)
        ->groupBy('bulan')
        ->orderBy('bulan')
        ->get();

    return response()->json([
        'summary' => [
            'donasi_masuk' => $donasiMasuk,
            'bantuan_tersalurkan' => $tersalurkan,
            'sisa_dana' => $sisaDana,
        ],
        'detail' => $donasis,
        'trend' => $trend
    ]);
}

}
