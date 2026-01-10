<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // =========================
    // ADMIN BUAT ADMIN
    // =========================
    public function createAdmin(Request $request)
    {
        return $this->createUserWithRole($request, 'admin');
    }

    // =========================
    // ADMIN BUAT PETUGAS
    // =========================
    public function createPetugas(Request $request)
    {
        return $this->createUserWithRole($request, 'petugas');
    }

    // =========================
    // FUNCTION UTAMA
    // =========================
    private function createUserWithRole(Request $request, string $role)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role
        ]);

        return response()->json([
            'message' => ucfirst($role) . ' berhasil dibuat',
            'user' => $user
        ], 201);
    }

    // =====================================================
    // 🔥 PROFIL DONATUR (TAMBAHAN, TIDAK MERUSAK)
    // =====================================================

    // ambil data user login
// Ambil profile donatur
    // ambil data user login
public function profile()
    {
        $user = Auth::user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'foto' => $user->foto ? url('storage/avatar/' . $user->foto) : null
        ]);
    }

    // update profil
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:100',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user->name = $request->name;

        // Jika ada file baru
        if ($request->hasFile('foto')) {
    if ($user->foto && Storage::disk('public')->exists('avatar/' . $user->foto)) {
        Storage::disk('public')->delete('avatar/' . $user->foto);
    }

    $file = $request->file('foto');
    $filename = time() . '_' . $file->getClientOriginalName();
    $file->storeAs('avatar', $filename, 'public'); // pakai disk public

    $user->foto = $filename;
}

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'foto' => $user->foto ? url('storage/avatar/' . $user->foto) : null
            ]
        ]);
    }

    // hapus foto profil
public function deletePhoto()
{
    $user = Auth::user();

    if ($user->foto && Storage::disk('public')->exists('avatar/' . $user->foto)) {
        Storage::disk('public')->delete('avatar/' . $user->foto);
    }

    $user->foto = null;
    $user->save();

    return response()->json([
        'message' => 'Foto profil berhasil dihapus',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'foto' => null, // pastikan React tahu ini null
        ]
    ]);
}
}
