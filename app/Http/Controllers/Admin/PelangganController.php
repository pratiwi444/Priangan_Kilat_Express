<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Rules\TeleponValid;
use App\Support\Telepon;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
public function index() { return view('admin.pelanggan.index', ['data' => Pelanggan::orderBy('nama')->get()]); }
public function create() { return view('admin.pelanggan.form', ['p' => new Pelanggan]); }
public function store(Request $r)
{
    $d = $this->valid($r);
    Pelanggan::create($d);
    return redirect()->route('admin.pelanggan.index')->with('ok', 'Pelanggan ditambahkan.');
}
public function edit(Pelanggan $pelanggan) { return view('admin.pelanggan.form', ['p' => $pelanggan]); }
public function update(Request $r, Pelanggan $pelanggan)
{
    $pelanggan->update($this->valid($r));
    return redirect()->route('admin.pelanggan.index')->with('ok', 'Pelanggan diperbarui.');
}
private function valid(Request $r): array
{
    $d = $r->validate(['nama' => 'required|max:100', 'telepon' => ['required', new TeleponValid]]);
    $d['telepon'] = Telepon::normalisasi($d['telepon']);   // simpan yang sudah rapi
    return $d;
}
}
