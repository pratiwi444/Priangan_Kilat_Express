<?php
namespace App\Services;

use App\Enums\StatusPaket;
use App\Models\{Kurir, Paket, RiwayatStatus, User, Zona};
use App\Services\PusatWilayah\PusatWilayahClient;
use App\Support\Telepon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaketService
{
    public function __construct(private PusatWilayahClient $wilayah) {}

    public function buat(array $d, User $admin): Paket
    {
        // zona: dari Pusat Wilayah, bila kosong wajib zona manual dari Admin
        $zonaNo = $this->wilayah->cariZona($d['kota_tujuan']);
        $manual = false;
        if ($zonaNo === null) {
            if (empty($d['zona_id_manual'])) {
                throw ValidationException::withMessages([
                    'zona_id_manual' => 'Zona tidak diketahui. Pilih zona manual.',
                ]);
            }
            $zona = Zona::findOrFail($d['zona_id_manual']);
            $manual = true;
        } else {
           $zona = Zona::findOrFail($zonaNo);
        }

        $ongkir = Tarif::ongkir((float) $d['berat'], $zona->tarif_per_kg, $d['layanan']);
        $isCod  = !empty($d['is_cod']);
        $nilai  = $isCod ? (int) $d['nilai_cod'] : 0;

        return DB::transaction(function () use ($d, $admin, $zona, $manual, $ongkir, $isCod, $nilai) {
            $paket = Paket::create([
                'no_resi'          => $this->resiBaru(),
                'pelanggan_id'     => $d['pelanggan_id'],
                'zona_id'          => $zona->id,
                'nama_penerima'    => $d['nama_penerima'],
                'telepon_penerima' => Telepon::normalisasi($d['telepon_penerima']),
                'kota_tujuan'      => $d['kota_tujuan'],
                'alamat_penerima'  => $d['alamat_penerima'] ?? null, 
                'zona_manual'      => $manual,
                'berat'            => $d['berat'],
                'layanan'          => $d['layanan'],
                'tarif_per_kg'     => $zona->tarif_per_kg,   // disalin saat paket dibuat
                'ongkir'           => $ongkir,                // disalin saat paket dibuat
                'is_cod'           => $isCod,
                'nilai_cod'        => $nilai,
                'biaya_cod'        => $isCod ? Tarif::biayaCod($nilai) : 0,
                'status'           => StatusPaket::Diterima,
                'jumlah_gagal'     => 0,
                'tanggal_masuk'    => now()->toDateString(),
            ]);
            $this->catat($paket, null, StatusPaket::Diterima, $admin, 'Paket diterima di loket');
            return $paket;
        });
    }

    private function resiBaru(): string
    {
        $prefix = 'PKE-' . now()->format('ymd') . '-';
        $terakhir = Paket::where('no_resi', 'like', $prefix . '%')->lockForUpdate()->max('no_resi');
        $urut = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;
        return $prefix . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }

    public function catat(Paket $p, ?StatusPaket $dari, StatusPaket $ke, ?User $pelaku, ?string $catatan): void
    {
        RiwayatStatus::create([
            'paket_id' => $p->id, 'status_dari' => $dari, 'status_ke' => $ke,
            'pelaku_id' => $pelaku?->id, 'catatan' => $catatan, 'waktu' => now(),
        ]);
    }

    /** Satu-satunya pintu untuk mengubah status. Dipakai web (Admin) dan API (Kurir). */
public function ubahStatus(Paket $p, StatusPaket $baru, User $pelaku, ?string $catatan = null): Paket
{
    return DB::transaction(function () use ($p, $baru, $pelaku, $catatan) {
        $p = Paket::whereKey($p->id)->lockForUpdate()->firstOrFail();
        $lama = $p->status;

        if ($lama->final()) $this->tolak('Paket ' . $lama->label() . ' sudah final, tidak bisa diubah.');

        $boleh = match (true) {
            $pelaku->role === 'admin' && $lama === StatusPaket::Diterima && $baru === StatusPaket::Transit => true,
            $pelaku->role === 'admin' && $lama === StatusPaket::GagalKirim && $baru === StatusPaket::Retur => true,
            $pelaku->role === 'kurir' && $lama === StatusPaket::Dikirim
                && in_array($baru, [StatusPaket::Terkirim, StatusPaket::GagalKirim], true) => true,
            default => false,
        };
        if (!$boleh) $this->tolak('Perubahan ' . $lama->label() . ' ke ' . $baru->label() . ' tidak diizinkan untuk peran ini.');

        if ($baru === StatusPaket::GagalKirim) {
            if (blank($catatan)) $this->tolak('Catatan wajib diisi untuk Gagal Kirim.');
            $p->jumlah_gagal++;
        }
        if ($baru === StatusPaket::Retur && $p->jumlah_gagal < 2) {
            $this->tolak('Retur hanya bisa setelah paket gagal kirim 2 kali.');
        }

        $p->status = $baru;
        $p->save();
        $this->catat($p, $lama, $baru, $pelaku, $catatan);
        return $p;
    });
}

/** Hitung zona dan biaya tanpa menyimpan apa pun (untuk tombol Cek zona). */
public function pratinjau(array $d): array
{
    $no = $this->wilayah->cariZona($d['kota_tujuan']);
    $zona = $no !== null
        ? Zona::find($no)
        : (!empty($d['zona_id_manual']) ? Zona::find($d['zona_id_manual']) : null);

    if ($zona === null) return ['zona_ditemukan' => false];

    $ongkir   = Tarif::ongkir((float) $d['berat'], $zona->tarif_per_kg, $d['layanan']);
    $nilai    = (int) ($d['nilai_cod'] ?? 0);
    $biayaCod = $nilai > 0 ? Tarif::biayaCod($nilai) : 0;

    return [
        'zona_ditemukan' => true,
        'manual'         => $no === null,
        'zona'           => $zona->nama,
        'tarif_per_kg'   => $zona->tarif_per_kg,
        'ongkir'         => $ongkir,
        'biaya_cod'      => $biayaCod,
        'total'          => $ongkir + $biayaCod,
    ];
}

public function tugaskan(Paket $p, Kurir $kurir, User $admin): Paket
{
    return DB::transaction(function () use ($p, $kurir, $admin) {
        $p = Paket::whereKey($p->id)->lockForUpdate()->firstOrFail();
        $kurir = Kurir::whereKey($kurir->id)->lockForUpdate()->firstOrFail();
        $lama = $p->status;

        $bisa = $lama === StatusPaket::Transit
            || ($lama === StatusPaket::GagalKirim && $p->jumlah_gagal < 2);
        if (!$bisa) {
            $this->tolak($lama === StatusPaket::GagalKirim
                ? 'Paket sudah gagal kirim 2 kali, hanya bisa Retur.'
                : 'Hanya paket Transit atau Gagal Kirim yang bisa ditugaskan.');
        }
        if (!$kurir->aktif) $this->tolak('Kurir nonaktif tidak bisa ditugaskan.');

        $sedangDikirim = Paket::where('kurir_id', $kurir->id)->where('status', StatusPaket::Dikirim)->lockForUpdate()->count();
        if ($sedangDikirim >= 20) $this->tolak('Kurir sudah membawa 20 paket Dikirim.');

        $p->kurir_id = $kurir->id;
        $p->status = StatusPaket::Dikirim;
        $p->save();
        $this->catat($p, $lama, StatusPaket::Dikirim, $admin, 'Ditugaskan ke kurir');
        return $p;
    });
}

private function tolak(string $pesan): never
{
    throw ValidationException::withMessages(['status' => $pesan]);
}
}