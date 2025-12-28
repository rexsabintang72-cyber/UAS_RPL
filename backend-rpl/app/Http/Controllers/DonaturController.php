<?php

namespace App\Http\Controllers;

use App\Models\Donatur;
use Illuminate\Http\Request;

class DonaturController extends Controller
{
    public function index() {
        return Donatur::all();
    }

    public function store(Request $request) {
        $request->validate([
            'nama' => 'required',
            'kontak' => 'required',
            'jenis_donatur' => 'required|in:perorangan,instansi',
        ]);

        return Donatur::create($request->all());
    }

    public function show(Donatur $donatur) {
        return $donatur;
    }

    public function update(Request $request, Donatur $donatur) {
        $donatur->update($request->all());
        return $donatur;
    }

    public function destroy(Donatur $donatur) {
        $donatur->delete();
        return response()->noContent();
    }
}

