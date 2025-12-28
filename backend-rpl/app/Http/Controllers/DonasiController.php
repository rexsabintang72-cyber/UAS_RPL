<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use Illuminate\Http\Request;

class DonasiController extends Controller
{
    public function index(Request $request) {
        if($request->has('status')){
            $donasis = Donasi::where('status', $request->status)->with('donatur')->get();
        } else {
            $donasis = Donasi::with('donatur')->get();
        }
        return $donasis;
    }

    public function store(Request $request) {
        $request->validate([
            'donatur_id' => 'required|exists:donaturs,id',
            'tanggal' => 'required|date',
            'jenis_donasi' => 'required',
            'jumlah' => 'required|numeric',
        ]);

        return Donasi::create($request->all());
    }

    public function show(Donasi $donasi) {
        return $donasi->load('donatur');
    }

    public function update(Request $request, Donasi $donasi) {
        $donasi->update($request->all());
        return $donasi;
    }

    public function destroy(Donasi $donasi) {
        $donasi->delete();
        return response()->noContent();
    }

    // Laporan
    public function laporan(Request $request) {
        $from = $request->from ?? now()->startOfMonth();
        $to = $request->to ?? now()->endOfMonth();

        $donasiMasuk = Donasi::whereBetween('tanggal', [$from, $to])->sum('jumlah');
        $donasiTersalurkan = Donasi::whereBetween('tanggal', [$from, $to])
                                    ->where('status','sudah disalurkan')
                                    ->sum('jumlah');
        $sisaDana = $donasiMasuk - $donasiTersalurkan;

        return [
            'donasi_masuk' => $donasiMasuk,
            'bantuan_tersalurkan' => $donasiTersalurkan,
            'sisa_dana' => $sisaDana
        ];
    }
}
