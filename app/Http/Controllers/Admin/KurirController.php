<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kurir;
use App\Models\User;
use App\Rules\TeleponValid;
use App\Support\Telepon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KurirController extends Controller
{
    public function store(Request $r)
{
    $d = $r->validate([
        'kode_kurir' => 'required|max:10|unique:kurir,kode_kurir',
        'nama'       => 'required|max:100',
        'telepon'    => ['required', new TeleponValid],
        'password'   => 'required|min:8',            // password sementara
    ]);
    DB::transaction(function () use ($d) {
        $user = User::create([
            'nama' => $d['nama'], 'username' => $d['kode_kurir'], 'password' => $d['password'],
            'role' => 'kurir', 'wajib_ganti_password' => true,
        ]);
        Kurir::create([
            'user_id' => $user->id, 'kode_kurir' => $d['kode_kurir'], 'nama' => $d['nama'],
            'telepon' => Telepon::normalisasi($d['telepon']), 'aktif' => true,
        ]);
    });
    return redirect()->route('admin.kurir.index')->with('ok', 'Kurir ditambahkan dengan password sementara.');
}

public function resetPassword(Request $r, Kurir $kurir)
{
    $r->validate(['password' => 'required|min:8']);
    $kurir->user->update(['password' => $r->password, 'wajib_ganti_password' => true]);
    return back()->with('ok', 'Password direset. Kurir wajib menggantinya saat login.');
}

public function nonaktifkan(Kurir $kurir)
{
    if ($kurir->paket()->where('status', 'dikirim')->exists()) {
        return back()->withErrors(['kurir' => 'Kurir masih membawa paket Dikirim, tidak bisa dinonaktifkan.']);
    }
    $kurir->update(['aktif' => false]);
    return back()->with('ok', 'Kurir dinonaktifkan.');
}
}
