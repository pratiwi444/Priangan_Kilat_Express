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
    // dipakai Admin (admin.kurir.index) dan Manajer (manajer.kurir): hanya lihat bagi Manajer
    public function index()
    {
        return view('admin.kurir.index', ['data' => Kurir::orderBy('kode_kurir')->get()]);
    }

    public function create()
    {
        return view('admin.kurir.form', ['k' => new Kurir]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'kode_kurir' => 'required|max:10|unique:kurir,kode_kurir|unique:users,username',
            'nama'       => 'required|max:100',
            'telepon'    => ['required', new TeleponValid],
            'password'   => 'required|min:8',                      // password sementara
        ], [
            'required'          => ':attribute wajib diisi.',
            'max'               => ':attribute maksimal :max karakter.',
            'kode_kurir.unique' => 'Kode kurir sudah dipakai.',
            'password.min'      => 'Password sementara minimal 8 karakter.',
        ], [
            'kode_kurir' => 'Kode kurir', 'nama' => 'Nama',
            'telepon' => 'Nomor telepon', 'password' => 'Password sementara',
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

    public function edit(Kurir $kurir)
    {
        return view('admin.kurir.form', ['k' => $kurir]);
    }

    // kode kurir TIDAK diubah; hanya nama dan telepon
    public function update(Request $r, Kurir $kurir)
    {
        $d = $r->validate(
            ['nama' => 'required|max:100', 'telepon' => ['required', new TeleponValid]],
            ['required' => ':attribute wajib diisi.', 'max' => ':attribute maksimal :max karakter.'],
            ['nama' => 'Nama', 'telepon' => 'Nomor telepon']
        );

        DB::transaction(function () use ($kurir, $d) {
            $kurir->update(['nama' => $d['nama'], 'telepon' => Telepon::normalisasi($d['telepon'])]);
            $kurir->user->update(['nama' => $d['nama']]);
        });

        return redirect()->route('admin.kurir.index')->with('ok', 'Data kurir diperbarui.');
    }

    public function resetPassword(Request $r, Kurir $kurir)
    {
        $r->validate(['password' => 'required|min:8'], [
            'password.required' => 'Password sementara wajib diisi.',
            'password.min'      => 'Password sementara minimal 8 karakter.',
        ]);
        // cast 'hashed' pada model User otomatis meng-hash
        $kurir->user->update(['password' => $r->password, 'wajib_ganti_password' => true]);
        $kurir->user->tokens()->delete();

        return back()->with('ok', 'Password ' . $kurir->kode_kurir . ' direset. Kurir wajib menggantinya saat login.');
    }

    public function nonaktifkan(Kurir $kurir)
    {
        // nilai status di database memakai huruf kecil ('dikirim'), sesuai enum StatusPaket
        if ($kurir->paket()->where('status', 'dikirim')->exists()) {
            return back()->withErrors(['kurir' => 'Kurir masih membawa paket Dikirim, tidak bisa dinonaktifkan.']);
        }
        $kurir->update(['aktif' => false]);
        $kurir->user->tokens()->delete();

        return back()->with('ok', 'Kurir dinonaktifkan.');
    }
}