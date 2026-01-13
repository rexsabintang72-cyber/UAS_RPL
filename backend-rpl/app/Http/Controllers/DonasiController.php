<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use App\Models\Donatur;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class DonasiController extends Controller
{
    // ======================
    // GET SEMUA DONASI
    // ======================
    public function index()
{
    // tampilkan semua donasi termasuk anonim
    return Donasi::with('donatur')
        ->orderBy('tanggal', 'desc')
        ->get();
}

    // ======================
    // TAMBAH DONASI (ADMIN)
    // ======================
public function store(Request $request)
{
    // Validasi awal
    $request->validate([
        'jenis_donasi' => 'required|in:uang,barang',
        'jumlah' => 'nullable|numeric',
        'nama_barang' => 'nullable|string',
        'jumlah_barang' => 'nullable|string',
        'keterangan' => 'nullable|string',
        'nama' => 'nullable|string',
        'kontak' => 'nullable|string',
    ]);

    // Validasi spesifik jenis donasi
    if ($request->jenis_donasi === 'uang' && !$request->jumlah) {
        return response()->json(['message' => 'Jumlah donasi uang wajib diisi'], 422);
    }
    if ($request->jenis_donasi === 'barang' && (!$request->nama_barang || !$request->jumlah_barang)) {
        return response()->json(['message' => 'Nama dan jumlah barang wajib diisi'], 422);
    }

    // Tentukan donatur
    if (Auth::check()) {
        // Login → perorangan/instansi
        $donatur = Donatur::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'nama' => Auth::user()->name,
                'kontak' => Auth::user()->email ?? 'Tidak ada',
                'jenis_donatur' => 'perorangan'
            ]
        );
    } else {
        // Belum login → anonim
        $request->validate([
            'nama' => 'required|string',
            'kontak' => 'required|string',
        ]);
        $donatur = Donatur::create([
            'nama' => $request->nama,
            'kontak' => $request->kontak,
            'jenis_donatur' => 'anonim',
            'user_id' => null
        ]);
    }

    // Simpan donasi
    $donasi = Donasi::create([
        'donatur_id' => $donatur->id,
        'tanggal' => date('Y-m-d'),
        'jenis_donasi' => $request->jenis_donasi,
        'jumlah' => $request->jenis_donasi === 'uang' ? $request->jumlah : null,
        'nama_barang' => $request->jenis_donasi === 'barang' ? $request->nama_barang : null,
        'jumlah_barang' => $request->jenis_donasi === 'barang' ? $request->jumlah_barang : null,
        'keterangan' => $request->jenis_donasi === 'barang' ? $request->keterangan : null,
        'status' => 'diproses',
        'verifikasi_admin' => 'pending'
    ]);

    return response()->json([
        'message' => 'Donasi berhasil dikirim',
        'donasi' => $donasi->load('donatur')
    ]);
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
    // Menghapus Donasi
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

    // ======================
// TAMBAH DONASI UNTUK ANONIM / PUBLIC
// ======================
public function storePublic(Request $request)
{
    // validasi untuk anonim
    $request->validate([
        'jenis_donasi' => 'required|in:uang,barang',
        'jumlah' => 'nullable|numeric',
        'nama_barang' => 'nullable|string',
        'jumlah_barang' => 'nullable|string',
        'keterangan' => 'nullable|string',
        'nama' => 'required|string',
        'kontak' => 'required|string',
    ]);

    // validasi spesifik jenis donasi
    if ($request->jenis_donasi === 'uang' && !$request->jumlah) {
        return response()->json(['message' => 'Jumlah donasi uang wajib diisi'], 422);
    }
    if ($request->jenis_donasi === 'barang' && (!$request->nama_barang || !$request->jumlah_barang)) {
        return response()->json(['message' => 'Nama dan jumlah barang wajib diisi'], 422);
    }

    // buat donatur anonim
    $donatur = Donatur::create([
        'nama' => $request->nama,
        'kontak' => $request->kontak,
        'jenis_donatur' => 'anonim',
        'user_id' => null
    ]);

    // simpan donasi
    $donasi = Donasi::create([
        'donatur_id' => $donatur->id,
        'tanggal' => date('Y-m-d'),
        'jenis_donasi' => $request->jenis_donasi,
        'jumlah' => $request->jenis_donasi === 'uang' ? $request->jumlah : null,
        'nama_barang' => $request->jenis_donasi === 'barang' ? $request->nama_barang : null,
        'jumlah_barang' => $request->jenis_donasi === 'barang' ? $request->jumlah_barang : null,
        'keterangan' => $request->jenis_donasi === 'barang' ? $request->keterangan : null,
        'status' => 'diproses',
        'verifikasi_admin' => 'pending'
    ]);

    return response()->json([
        'message' => 'Donasi berhasil dikirim (anonim)',
        'donasi' => $donasi->load('donatur')
    ]);
}

// ======================
// DASHBOARD PETUGAS (TAMBAHAN)
// ======================
public function dashboardPetugas()
{
    // ambil semua donasi, termasuk anonim, urut terbaru
    $donasis = Donasi::with('donatur')->orderBy('tanggal', 'desc')->get();

    $totalDonasi = $donasis->sum('jumlah');
    $totalDisalurkan = $donasis->where('status', 'sudah disalurkan')->sum('jumlah');
    $sisaDana = $totalDonasi - $totalDisalurkan;
    $donasiDitolak = $donasis->where('status', 'ditolak')->sum('jumlah');

    return response()->json([
        'donasis' => $donasis,
        'stats' => [
            'totalDonasi' => $totalDonasi,
            'totalDisalurkan' => $totalDisalurkan,
            'sisaDana' => $sisaDana,
            'donasiDitolak' => $donasiDitolak
        ]
    ]);
}

// ======================
// LAPORAN PETUGAS (TAMBAHAN)
// ======================
public function laporanPetugas(Request $request)
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
// PELACAKAN DONASI PETUGAS (TAMBAHAN)
// ======================
public function pelacakanPetugas()
{
    $donasis = Donasi::with('donatur')->orderBy('tanggal', 'desc')->get();
    return response()->json($donasis);
}

// ======================
// DONASI MILIK USER
// ======================
public function donasiUser()
{
    $user = Auth::user();

    // Ambil donasi milik user login
    $donasi = Donasi::with('donatur')
        ->whereHas('donatur', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->orderBy('tanggal', 'desc')
        ->get();

    return response()->json([
        'donasis' => $donasi
    ]);
}

}


