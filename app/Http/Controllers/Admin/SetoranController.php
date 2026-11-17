<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPaket;
use App\Http\Controllers\Controller;
use App\Models\{Kurir, Paket, SetoranCod};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SetoranController extends Controller
{
    public function index(Request $r)
    {
        $q = Paket::with(['kurir', 'setoran'])
            ->where('is_cod', true)
            ->where('status', StatusPaket::Terkirim);
        if ($r->filled('kurir_id')) $q->where('kurir_id', $r->kurir_id);

        $paket = $q->orderBy('id')->get();

        return view('admin.setoran.index', [
            'paket' => $paket,
            'kurir' => Kurir::orderBy('kode_kurir')->get(),
            'belum' => $paket->filter(fn ($p) => !$p->setoran)->sum('nilai_cod'),
        ]);
    }

    public function store(Request $r)
    {
        $r->validate([
            'paket_id'   => 'required|array|min:1',
            'paket_id.*' => 'exists:paket,id',
        ], [
            'paket_id.required' => 'Pilih minimal satu paket.',
            'paket_id.min'      => 'Pilih minimal satu paket.',
        ]);

        DB::transaction(function () use ($r) {
            foreach (array_unique($r->paket_id) as $id) {
                $p = Paket::whereKey($id)->lockForUpdate()->firstOrFail();

                if (!$p->is_cod || $p->status !== StatusPaket::Terkirim) {
                    throw ValidationException::withMessages(['paket_id' => "Paket {$p->no_resi} bukan paket COD Terkirim."]);
                }
                if ($p->setoran()->exists()) {
                    throw ValidationException::withMessages(['paket_id' => "Paket {$p->no_resi} sudah disetor."]);
                }
                SetoranCod::create([
                    'paket_id'     => $p->id,
                    'dicatat_oleh' => $r->user()->id,
                    'waktu'        => now(),
                ]);
            }
        });

        return back()->with('ok', 'Setoran dicatat.');
    }
}