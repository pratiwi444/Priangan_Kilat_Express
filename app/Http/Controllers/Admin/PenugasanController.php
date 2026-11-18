<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPaket;
use App\Http\Controllers\Controller;
use App\Models\Kurir;
use App\Models\Paket;
use App\Models\Zona;
use App\Services\PaketService;
use Illuminate\Http\Request;

class PenugasanController extends Controller
{
    public function __construct(private PaketService $svc) {}

    public function index(Request $request)
    {
        $zonaId = $request->query('zona_id');

        // siap ditugaskan: Transit, atau Gagal Kirim yang belum 2 kali gagal
        $pakets = Paket::query()
            ->where(function ($q) {
                $q->where('status', StatusPaket::Transit->value)
                  ->orWhere(function ($q2) {
                      $q2->where('status', StatusPaket::GagalKirim->value)
                         ->where('jumlah_gagal', '<', 2);
                  });
            })
            ->when($zonaId, fn ($q) => $q->where('zona_id', $zonaId))
            ->orderBy('tanggal_masuk')
            ->get();

        $kurirs = Kurir::where('aktif', true)
            ->withCount(['paket as jumlah_dikirim' => fn ($q) => $q->where('status', StatusPaket::Dikirim->value)])
            ->orderBy('kode_kurir')
            ->get();

        $zonas = Zona::orderBy('id')->get();

        return view('admin.penugasan.index', compact('pakets', 'kurirs', 'zonas', 'zonaId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kurir_id'    => ['required', 'exists:kurir,id'],
            'paket_ids'   => ['required', 'array', 'min:1'],
            'paket_ids.*' => ['required', 'exists:paket,id'],
        ], [
            'kurir_id.required'  => 'Pilih kurir terlebih dahulu.',
            'paket_ids.required' => 'Pilih minimal satu paket.',
            'paket_ids.min'      => 'Pilih minimal satu paket.',
        ]);

        $jumlah = $this->svc->tugaskan($data['paket_ids'], Kurir::findOrFail($data['kurir_id']), $request->user());

        return back()->with('sukses', "$jumlah paket berhasil ditugaskan.");
    }
}