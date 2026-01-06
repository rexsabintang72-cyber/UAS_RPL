<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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
    $donasiDitolak = $donasis->where('status', 'ditolak')->sum('jumlah');

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
            'donasi_ditolak' => $donasiDitolak
        ],
        'detail' => $donasis,
        'trend' => $trend
    ]);
}

public function updateStatusByPetugas(Request $request)
{
    // VALIDASI TETAP (TIDAK DIUBAH)
    $request->validate([
        'donasi_id' => 'required|exists:donasis,id',
        'status' => 'required|in:diproses,diterima,sudah disalurkan,ditolak'
    ]);

    $donasi = Donasi::findOrFail($request->donasi_id);

    // 🔒 TAMBAHAN PENGAMAN (INI YANG BARU)
    if ($donasi->verifikasi_admin !== 'disetujui') {
        return response()->json([
            'message' => 'Donasi belum diverifikasi admin',
            'donasi' => $donasi->load('donatur')
        ], 403);
    }

    // UPDATE STATUS (TETAP)
    $donasi->status = $request->status;
    $donasi->save();

    return response()->json([
        'message' => 'Status bantuan berhasil diperbarui oleh petugas',
        'donasi' => $donasi->load('donatur')
    ]);
}
public function laporanPetugas(Request $request)
{
    // default bulan Januari jika tidak dikirim query
    $bulan = $request->bulan ?? 1;
    $tahun = $request->tahun ?? date('Y');

    $donasis = Donasi::with('donatur')
        ->whereMonth('tanggal', $bulan)
        ->whereYear('tanggal', $tahun)
        ->get();

    $donasiMasuk = $donasis->sum('jumlah');
    $tersalurkan = $donasis->where('status', 'sudah disalurkan')->sum('jumlah');
    $sisaDana = $donasiMasuk - $tersalurkan;
    $donasiDitolak = $donasis->where('status', 'ditolak')->sum('jumlah');

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
            'donasi_ditolak' => $donasiDitolak
        ],
        'detail' => $donasis,
        'trend' => $trend
    ]);
}

public function laporanPdf(Request $request)
{
    $bulan = $request->bulan ?? date('m');
    $tahun = $request->tahun ?? date('Y');

    $donasis = Donasi::with('donatur')
        ->whereMonth('tanggal', $bulan)
        ->whereYear('tanggal', $tahun)
        ->get();

    $donasiMasuk = $donasis->sum('jumlah');
    $tersalurkan = $donasis->where('status', 'sudah disalurkan')->sum('jumlah');
    $sisaDana = $donasiMasuk - $tersalurkan;
    $donasiDitolak = $donasis->where('status', 'ditolak')->sum('jumlah');

    $pdf = Pdf::loadView('pdf.laporan-donasi', [
        'bulan' => $bulan,
        'tahun' => $tahun,
        'donasis' => $donasis,
        'donasiMasuk' => $donasiMasuk,
        'tersalurkan' => $tersalurkan,
        'sisaDana' => $sisaDana,
        'donasiDitolak' => $donasiDitolak
    ]);

    return $pdf->download("laporan_donasi_{$bulan}_{$tahun}.pdf");
}

public function verifikasiAdmin(Request $request, $id)
{
    $request->validate([
        'verifikasi_admin' => 'required|in:disetujui,ditolak'
    ]);

    $donasi = Donasi::findOrFail($id);

    $donasi->verifikasi_admin = $request->verifikasi_admin;

    // kalau ditolak admin → status otomatis ditolak
    if ($request->verifikasi_admin === 'ditolak') {
        $donasi->status = 'ditolak';
    }

    $donasi->save();

    return response()->json([
        'message' => 'Verifikasi admin berhasil',
        'donasi' => $donasi->load('donatur')
    ]);
}

}
