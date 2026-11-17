<?php

namespace App\Http\Controllers\Manajer;

use App\Enums\StatusPaket;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $r)
    {
        $r->validate([
            'dari'   => 'nullable|date',
            'sampai' => 'nullable|date|after_or_equal:dari',
        ], [
            'sampai.after_or_equal' => 'Tanggal "sampai" tidak boleh sebelum tanggal "dari".',
        ]);

        $dari   = $r->input('dari') ?: now()->startOfMonth()->toDateString();
        $sampai = $r->input('sampai') ?: now()->toDateString();

        // 1. Pengiriman: jumlah paket masuk pada periode, per status saat ini (enam status selalu tampil)
        $perStatus = DB::table('paket')
            ->whereBetween('tanggal_masuk', [$dari, $sampai])
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $pengiriman = collect(StatusPaket::cases())->map(fn ($s) => [
            'label'  => $s->label(),
            'jumlah' => (int) ($perStatus[$s->value] ?? 0),
        ]);

        // 2. Pendapatan per zona: ongkir + biaya COD yang tersimpan di paket
        $pendapatan = DB::table('paket')
            ->leftJoin('zona', 'zona.id', '=', 'paket.zona_id')
            ->whereBetween('paket.tanggal_masuk', [$dari, $sampai])
            ->selectRaw("coalesce(zona.nama, 'Tanpa zona') as zona,
                         count(*) as jumlah_paket,
                         sum(coalesce(paket.ongkir, 0)) as ongkir,
                         sum(coalesce(paket.biaya_cod, 0)) as biaya_cod,
                         sum(coalesce(paket.ongkir, 0) + coalesce(paket.biaya_cod, 0)) as total")
            ->groupBy('zona.id', 'zona.nama')
            ->orderByRaw('zona.id is null, zona.id')
            ->get();

        // 3. Rekap COD per kurir: paket COD Terkirim, belum dan sudah disetor
        $rekapCod = DB::table('paket')
            ->join('kurir', 'kurir.id', '=', 'paket.kurir_id')
            ->leftJoin('setoran_cod', 'setoran_cod.paket_id', '=', 'paket.id')
            ->where('paket.is_cod', true)
            ->where('paket.status', StatusPaket::Terkirim->value)
            ->selectRaw("kurir.kode_kurir, kurir.nama,
                sum(case when setoran_cod.id is null then paket.nilai_cod else 0 end) as belum_disetor,
                sum(case when setoran_cod.id is not null then paket.nilai_cod else 0 end) as sudah_disetor")
            ->groupBy('kurir.id', 'kurir.kode_kurir', 'kurir.nama')
            ->orderBy('kurir.kode_kurir')
            ->get();

        return view('manajer.laporan', compact('dari', 'sampai', 'pengiriman', 'pendapatan', 'rekapCod'));
    }
}