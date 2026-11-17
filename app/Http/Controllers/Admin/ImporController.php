<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImporCsv;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ImporController extends Controller
{
    public function index()
    {
        return view('admin.impor.index');
    }

    public function store(Request $r, ImporCsv $impor)
    {
        $r->validate([
            'jenis'  => 'required|in:kurir,pelanggan,paket',
            'berkas' => 'required|file|extensions:csv,txt|max:5120',
        ], [
            'jenis.required'     => 'Pilih jenis data.',
            'jenis.in'           => 'Jenis data tidak dikenal.',
            'berkas.required'    => 'Pilih berkas CSV.',
            'berkas.extensions'  => 'Berkas harus berformat .csv.',
            'berkas.max'         => 'Ukuran berkas maksimal 5 MB.',
        ]);

        set_time_limit(180);   // paket historis memanggil Pusat Wilayah per kota

        $jenis = $r->input('jenis');
        $path  = $r->file('berkas')->getRealPath();

        try {
            $lap = $impor->$jenis($path);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['berkas' => $e->getMessage()]);
        }

        return back()->withInput()->with('hasil', [
            'berkas'      => $r->file('berkas')->getClientOriginalName(),
            'baris'       => $impor->ringkas($lap, $jenis),
            'tidak_valid' => $lap['tidak_valid'],
        ]);
    }
}
