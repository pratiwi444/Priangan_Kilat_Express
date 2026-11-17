<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Support\Telepon;
use Illuminate\Http\Request;

class CekResiController extends Controller
{
    public function index(Request $r)
    {
        $hasil = null;
        $tidakAda = false;

        if ($r->filled('resi')) {
            $paket = Paket::with('riwayat')->where('no_resi', strtoupper(trim($r->resi)))->first();

            if ($paket) {
                $hasil = [
                    'no_resi' => $paket->no_resi,
                    'status'  => $paket->status->label(),
                    'telepon' => Telepon::samarkan($paket->telepon_penerima),   // disamarkan di server
                    'riwayat' => $paket->riwayat->map(fn ($h) => [              // tanpa nama petugas
                        'waktu'   => $h->waktu->format('d/m/Y H:i'),
                        'status'  => $h->status_ke->label(),
                        'catatan' => $h->catatan,
                    ]),
                ];
            } else {
                $tidakAda = true;
            }
        }

        return view('cek-resi', compact('hasil', 'tidakAda'));
    }
}