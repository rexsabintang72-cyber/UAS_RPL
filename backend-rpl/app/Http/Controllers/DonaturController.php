<?php

namespace App\Http\Controllers;

use App\Models\Donatur;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DonaturController extends Controller
{
    // GET semua donatur → harus login
    public function index() {
        return Donatur::all();
    }

    // POST donatur → bisa anonim
    public function store(Request $request) {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kontak' => 'required|string|max:255',
            'jenis_donatur' => 'required|string|in:perorangan,instansi,anonim',
        ]);

        $data = $request->all();

        // Jika user login, simpan user_id
        $data['user_id'] = Auth::check() ? Auth::id() : null;

        return Donatur::create($data);
    }

    // GET donatur berdasarkan id → harus login
    public function show(Donatur $donatur) {
        return $donatur;
    }

    // UPDATE donatur → harus login
    public function update(Request $request, Donatur $donatur) {
        $donatur->update($request->all());
        return $donatur;
    }

    // DELETE donatur → harus login
    public function destroy(Donatur $donatur) {
        $donatur->delete();
        return response()->noContent();
    }
}
