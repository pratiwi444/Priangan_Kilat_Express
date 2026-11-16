<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPaket;
use App\Http\Controllers\Controller;
use App\Models\{Kurir, Paket, Pelanggan, Zona};
use App\Rules\TeleponValid;
use App\Services\PaketService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaketController extends Controller
{
    public function __construct(private PaketService $svc) {}

    public function index(Request $r)
    {
        $q = Paket::with(['kurir', 'zona'])->latest('id');

        if ($r->filled('status')) {
            $q->where('status', $r->status);
        }
        if ($r->filled('cari')) {
            $q->where(fn ($w) => $w->where('no_resi', 'like', "%{$r->cari}%")
                                   ->orWhere('nama_penerima', 'like', "%{$r->cari}%"));
        }

        return view('admin.paket.index', [
            'paket'   => $q->paginate(15)->withQueryString(),
            'ringkas' => Paket::selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status'),
            'status'  => StatusPaket::cases(),
        ]);
    }

    public function create()
    {
        return view('admin.paket.form', [
            'pelanggan' => Pelanggan::orderBy('nama')->get(),
            'zona'      => Zona::orderBy('id')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'pelanggan_id'     => 'required|exists:pelanggan,id',
            'nama_penerima'    => 'required|max:100',
            'telepon_penerima' => ['required', new TeleponValid],
            'kota_tujuan'      => 'required|max:100',
            'alamat_penerima' => 'nullable|max:255',
            'berat'            => 'required|numeric|gt:0',
            'layanan'          => 'required|in:reguler,express',
            'is_cod'           => 'nullable|boolean',
            'nilai_cod'        => 'required_if:is_cod,1|nullable|integer|min:1',
            'zona_id_manual'   => 'nullable|exists:zona,id',
        ], [
            'required'          => ':attribute wajib diisi.',
            'required_if'       => ':attribute wajib diisi bila paket COD.',
            'berat.gt'          => 'Berat harus lebih dari nol.',
            'berat.numeric'     => 'Berat harus berupa angka.',
            'nilai_cod.integer' => 'Nilai COD harus berupa angka bulat.',
        ], [
            'pelanggan_id' => 'Pengirim', 'nama_penerima' => 'Nama penerima',
            'telepon_penerima' => 'Telepon penerima', 'kota_tujuan' => 'Kota tujuan',
            'berat' => 'Berat', 'layanan' => 'Layanan', 'nilai_cod' => 'Nilai COD',
        ]);

        $paket = $this->svc->buat($d, $r->user());

        return redirect()->route('admin.paket.show', $paket)->with('ok', 'Paket tersimpan. Resi: ' . $paket->no_resi);
    }

    public function show(Paket $paket)
    {
        return view('admin.paket.show', [
            'paket' => $paket->load(['pelanggan', 'zona', 'kurir', 'riwayat.pelaku', 'setoran']),
            'kurir' => Kurir::where('aktif', true)->orderBy('kode_kurir')->get(),   // dipakai di Bab 7
        ]);
    }

    public function ubahStatus(Paket $paket, Request $r)
    {
        $d = $r->validate([
            'status'  => ['required', Rule::enum(StatusPaket::class)],
            'catatan' => 'nullable|max:255',
        ]);
        $this->svc->ubahStatus($paket, StatusPaket::from($d['status']), $r->user(), $d['catatan'] ?? null);
        return back()->with('ok', 'Status paket diperbarui.');
    }

    public function tugaskan(Paket $paket, Request $r)
    {
        $r->validate(['kurir_id' => 'required|exists:kurir,id']);
        $this->svc->tugaskan($paket, kurir::findOrFail($r->kurir_id), $r->user());
        return back()->with('ok', 'Paket ditugaskan. ');
    }

    public function cekZona(Request $r)
{
    $d = $r->validate([
        'kota_tujuan'    => 'required|max:100',
        'berat'          => 'required|numeric|gt:0',
        'layanan'        => 'required|in:reguler,express',
        'nilai_cod'      => 'nullable|integer|min:1',
        'zona_id_manual' => 'nullable|exists:zona,id',
    ], [
        'required'  => ':attribute wajib diisi untuk cek zona.',
        'berat.gt'  => 'Berat harus lebih dari nol.',
        'numeric'   => ':attribute harus berupa angka.',
    ], [
        'kota_tujuan' => 'Kota tujuan', 'berat' => 'Berat', 'layanan' => 'Layanan',
    ]);

    return response()->json($this->svc->pratinjau($d));
}

}