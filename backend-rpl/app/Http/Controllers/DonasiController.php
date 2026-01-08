<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DonasiController extends Controller
{
    // ======================
    // GET SEMUA DONASI
    // ======================
    public function index()
    {
        // Semua donasi terbaru + relasi donatur
        return Donasi::with('donatur')
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    // ======================
    // TAMBAH DONASI (ADMIN)
    // ======================
    public function store(Request $request)
    {
        $request->validate([
            'donatur_id' => 'required|exists:donaturs,id',
            'tanggal' => 'required|date',
            'jenis_donasi' => 'required',
            'jumlah' => 'required|numeric',
            // hapus 'status' dari validasi
        ]);

        $donasi = Donasi::create([
            'donatur_id' => $request->donatur_id,
            'tanggal' => $request->tanggal,
            'jenis_donasi' => $request->jenis_donasi,
            'jumlah' => $request->jumlah,
            'status' => 'belum disalurkan', // default status untuk admin
        ]);

        return $donasi->load('donatur');
    }

    // ======================
    // DETAIL DONASI
    // ======================
    public function show(Donasi $donasi)
    {
        return $donasi->load('donatur');
    }

    // ======================
    // UPDATE DONASI
    // ======================
    public function update(Request $request, Donasi $donasi)
    {
        $donasi->update($request->all());
        return $donasi;
    }

    // ======================
    // HAPUS DONASI
    // ======================
    public function destroy(Donasi $donasi)
    {
        $donasi->delete();
        return response()->noContent();
    }

    // ======================
    // UPDATE STATUS OLEH PETUGAS
    // ======================
    public function updateStatusByPetugas(Request $request)
    {
        $request->validate([
    'donasi_id' => 'required|exists:donasis,id',
    'status' => 'required|in:belum disalurkan,sudah disalurkan,ditolak'
]);

$donasi = Donasi::findOrFail($request->donasi_id);

// hanya boleh update jika sudah diverifikasi admin
if ($donasi->verifikasi_admin !== 'disetujui') {
    return response()->json([
        'message' => 'Donasi belum diverifikasi admin',
        'donasi' => $donasi->load('donatur')
    ], 403);
}

// update status
$donasi->status = $request->status;
$donasi->save();
    }

    // ======================
    // VERIFIKASI ADMIN
    // ======================
    public function verifikasiAdmin(Request $request, $id)
    {
        $request->validate([
            'verifikasi_admin' => 'required|in:disetujui,ditolak'
        ]);

        $donasi = Donasi::findOrFail($id);

        $donasi->verifikasi_admin = $request->verifikasi_admin;

        // Kalau ditolak admin → status otomatis ditolak
        if ($request->verifikasi_admin === 'ditolak') {
            $donasi->status = 'ditolak';
        }

        $donasi->save();

        return response()->json([
            'message' => 'Verifikasi admin berhasil',
            'donasi' => $donasi->load('donatur')
        ]);
    }

    // ======================
    // LAPORAN BULANAN
    // ======================
    public function laporan(Request $request)
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

    // ======================
    // LAPORAN PDF
    // ======================
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
}
