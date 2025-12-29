<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
}
