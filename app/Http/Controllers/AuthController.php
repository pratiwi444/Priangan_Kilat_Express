<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function form() { return view('auth.login'); }

public function login(Request $request)
{
    $data = $request->validate(['username' => 'required', 'password' => 'required']);
    if (!Auth::attempt($data)) {
        return back()->withErrors(['username' => 'Username atau password salah.'])->onlyInput('username');
    }
    if (Auth::user()->role === 'kurir') {          // kurir hanya lewat APK
        Auth::logout();
        return back()->withErrors(['username' => 'Kurir memakai aplikasi APK.']);
    }
    $request->session()->regenerate();
    return redirect(Auth::user()->role === 'admin' ? '/admin/paket' : '/manajer/laporan');
    }

public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
}

public function formGanti() { return view('auth.ganti-password'); }

public function ganti(Request $request)
{
    $request->validate(['password' => 'required|min:8|confirmed']);
    $request->user()->update(['password' => $request->password, 'wajib_ganti_password' => false]);
    return back()->with('ok', 'Password berhasil diganti.');
}
}
