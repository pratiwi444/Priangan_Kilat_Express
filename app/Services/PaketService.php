<?php
namespace App\Services;

use App\Enums\StatusPaket;
use App\Models\{Paket, RiwayatStatus, User, Zona};
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
}