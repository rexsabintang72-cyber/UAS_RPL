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
        return Donasi::with('donatur')
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    // ======================
    // TAMBAH DONASI (ADMIN / USER LOGIN / ANONIM)
    // ======================
    public function store(Request $request)
    {
        $request->validate([
            'jenis_donasi' => 'required|in:uang,barang',
            'jumlah' => 'nullable|numeric',
            'nama_barang' => 'nullable|string',
            'jumlah_barang' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'nama' => 'nullable|string',
            'kontak' => 'nullable|string',
        ]);

        if ($request->jenis_donasi === 'uang' && !$request->jumlah) {
            return response()->json(['message' => 'Jumlah donasi uang wajib diisi'], 422);
        }

        if ($request->jenis_donasi === 'barang' && (!$request->nama_barang || !$request->jumlah_barang)) {
            return response()->json(['message' => 'Nama dan jumlah barang wajib diisi'], 422);
        }

        if (Auth::check()) {
            $donatur = Donatur::firstOrCreate(
                ['user_id' => Auth::id()],
                [
                    'nama' => Auth::user()->name,
                    'kontak' => Auth::user()->email ?? 'Tidak ada',
                    'jenis_donatur' => 'perorangan'
                ]
            );
        } else {
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
        return response()->json($donasi);
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

        if ($donasi->verifikasi_admin !== 'disetujui') {
            return response()->json([
                'message' => 'Donasi belum diverifikasi admin'
            ], 403);
        }

        $donasi->status = $request->status;
        $donasi->save();

        return response()->json($donasi->load('donatur'));
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

        if ($request->verifikasi_admin === 'ditolak') {
            $donasi->status = 'ditolak';
        }

        $donasi->save();

        return response()->json($donasi->load('donatur'));
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

        return response()->json([
            'summary' => [
                'donasi_masuk' => $donasis->sum('jumlah'),
                'bantuan_tersalurkan' => $donasis->where('status', 'sudah disalurkan')->sum('jumlah'),
                'donasi_ditolak' => $donasis->where('status', 'ditolak')->sum('jumlah'),
            ],
            'detail' => $donasis
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

        $pdf = Pdf::loadView('pdf.laporan-donasi', compact('bulan', 'tahun', 'donasis'));
        return $pdf->download("laporan_donasi_{$bulan}_{$tahun}.pdf");
    }

    // ======================
    // DONASI MILIK USER
    // ======================
    public function donasiUser()
    {
        $user = Auth::user();

        $donasi = Donasi::with('donatur')
            ->whereHas('donatur', fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json(['donasis' => $donasi]);
    }
}
