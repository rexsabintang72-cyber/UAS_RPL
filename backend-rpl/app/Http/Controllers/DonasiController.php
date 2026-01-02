<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use Illuminate\Http\Request;

class DonasiController extends Controller
{
    // Daftar status valid sesuai migration
    private $validStatus = ['diproses', 'diterima', 'sudah disalurkan'];

    // Ambil semua donasi atau filter by status
    public function index(Request $request) {
        if ($request->has('status') && in_array($request->status, $this->validStatus)) {
            $donasis = Donasi::where('status', $request->status)->with('donatur')->get();
        } else {
            $donasis = Donasi::with('donatur')->get();
        }
        return response()->json($donasis);
    }

    // Tambah donasi baru
    public function store(Request $request) {
        $request->validate([
            'donatur_id' => 'required|exists:donaturs,id',
            'tanggal' => 'required|date',
            'jenis_donasi' => 'required',
            'jumlah' => 'required|numeric',
            'status' => 'sometimes|in:diproses,diterima,sudah disalurkan',
        ]);

        $data = $request->all();

        // Jika status tidak dikirim, set default 'diproses'
        if (!isset($data['status'])) {
            $data['status'] = 'diproses';
        }

        $donasi = Donasi::create($data);
        return response()->json($donasi, 201);
    }

    // Detail donasi
    public function show(Donasi $donasi) {
        return response()->json($donasi->load('donatur'));
    }

    // Update donasi / status
    public function update(Request $request, Donasi $donasi) {
        $request->validate([
            'donatur_id' => 'sometimes|exists:donaturs,id',
            'tanggal' => 'sometimes|date',
            'jenis_donasi' => 'sometimes|string',
            'jumlah' => 'sometimes|numeric',
            'status' => 'sometimes|in:diproses,diterima,sudah disalurkan',
        ]);

        $donasi->update($request->all());
        return response()->json($donasi);
    }

    // Hapus donasi
    public function destroy(Donasi $donasi) {
        $donasi->delete();
        return response()->noContent();
    }

    // Laporan donasi
public function laporan(Request $request) {
    // Ambil tanggal dari request, default ke awal/akhir bulan sekarang
    $from = $request->from
        ? date('Y-m-d 00:00:00', strtotime($request->from))
        : date('Y-m-01 00:00:00'); // awal bulan
    $to = $request->to
        ? date('Y-m-d 23:59:59', strtotime($request->to))
        : date('Y-m-t 23:59:59');   // akhir bulan

    // Ambil semua donasi dalam range tanggal
    $donasis = Donasi::with('donatur')
        ->whereBetween('tanggal', [$from, $to])
        ->get();

    // Hitung total
    $donasiMasuk = $donasis->sum('jumlah');
    $donasiTersalurkan = $donasis->where('status', 'sudah disalurkan')->sum('jumlah');
    $sisaDana = $donasiMasuk - $donasiTersalurkan;

    return response()->json([
        'summary' => [
            'donasi_masuk' => $donasiMasuk,
            'bantuan_tersalurkan' => $donasiTersalurkan,
            'sisa_dana' => $sisaDana
        ],
        'detail' => $donasis
    ]);
}


}
