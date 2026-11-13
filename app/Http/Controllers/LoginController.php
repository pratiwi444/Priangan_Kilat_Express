<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function form()
    {
        return view('auth.login');
    }

    public function proses(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);
        //pesan sengaja sama supaya semua kegagalan ga membocorkan mana yg salah
        $gagal = back()->withInput($request->only('username'))
            ->withErrors(['login' => 'Username atau password salah.']);
        
        if (!Auth::attempt($data)) {
            return $gagal;
        }

        if (Auth::user()->role == 'kurir') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors(['login' => 'Akun kurir login lewat apk android.']);
        }

        $request->session()->regenerate();

        return match (Auth::user()->role) {
            'admin' => redirect()->route('admin.beranda'),
            'manajer' => redirect()->route('manajer.beranda'),
            default => $this->keluar($request),
        };
    }

    public function keluar(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
