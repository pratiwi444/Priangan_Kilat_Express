<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kurir;
use App\Models\Paket;
use App\Models\RiwayatStatus;
use App\Models\Zona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenugasanController extends Controller
{
    public function index(Request $request)
    {
        $zonaId = $request->query('zona_id');

        $pakets = Paket::query()
            ->where(function ($q) {
                $q->where('status', 'Transit')
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'Gagal Kirim')
                          ->where('jumlah_gagal', '<', 2);
                  });
            })
            ->when($zonaId, fn ($q) => $q->where('zona_id', $zonaId))
            ->orderBy('tanggal_masuk')
            ->get();

        $kurirs = Kurir::where('aktif', true)
            ->withCount([
                'paket as jumlah_dikirim' => function ($q) {
                    $q->where('status', 'Dikirim');
                }
            ])
            ->orderBy('kode_kurir')
            ->get();

        $zonas = Zona::orderBy('id')->get();

        return view('admin.penugasan.index', compact(
            'pakets',
            'kurirs',
            'zonas',
            'zonaId'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kurir_id' => ['required', 'exists:kurir,id'],
            'paket_ids' => ['required', 'array', 'min:1'],
            'paket_ids.*' => ['required', 'exists:paket,id'],
        ]);

        $kurir = Kurir::where('aktif', true)
            ->findOrFail($data['kurir_id']);

        return DB::transaction(function () use ($data, $kurir, $request) {

            $sedang = Paket::where('kurir_id', $kurir->id)
                ->where('status', 'Dikirim')
                ->lockForUpdate()
                ->count();

            $pakets = Paket::whereIn('id', $data['paket_ids'])
                ->lockForUpdate()
                ->get();

            if ($pakets->count() !== count(array_unique($data['paket_ids']))) {
                return back()->withErrors([
                    'paket_ids' => 'Data paket tidak valid.'
                ]);
            }

            if ($sedang + $pakets->count() > 20) {
                return back()->withErrors([
                    'kurir_id' => 'Kapasitas kurir maksimal 20 paket.'
                ]);
            }

            foreach ($pakets as $p) {
                $boleh = $p->status === 'Transit'
                    || ($p->status === 'Gagal Kirim' && $p->jumlah_gagal < 2);

                if (!$boleh) {
                    return back()->withErrors([
                        'paket_ids' => "Paket {$p->no_resi} tidak dapat ditugaskan."
                    ]);
                }
            }

            foreach ($pakets as $p) {
                $dari = $p->status;

                $p->update([
                    'status' => 'Dikirim',
                    'kurir_id' => $kurir->id
                ]);

                RiwayatStatus::create([
                    'paket_id' => $p->id,
                    'pelaku_id' => $request->user()->id,
                    'status_dari' => $dari,
                    'status_ke' => 'Dikirim',
                    'catatan' => 'Ditugaskan ke ' . $kurir->kode_kurir,
                    'waktu' => now(),
                ]);
            }

            return back()->with(
                'sukses',
                $pakets->count() . ' paket berhasil ditugaskan.'
            );
        });
    }
}