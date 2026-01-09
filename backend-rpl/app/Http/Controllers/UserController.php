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
    public function profile()
    {
        return response()->json(Auth::user());
    }

    // update profil donatur
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:100',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user->name = $request->name;

        if ($request->hasFile('foto')) {
            // hapus foto lama
            if ($user->foto) {
                Storage::delete('public/avatar/' . $user->foto);
            }

            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/avatar', $filename);

            $user->foto = $filename;
        }

        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => $user
        ]);
    }
}
