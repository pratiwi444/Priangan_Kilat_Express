<?php

namespace Database\Seeders;

use App\Enums\StatusPaket;
use App\Models\{Kurir, Paket, Pelanggan, RiwayatStatus, User, Zona};
use App\Services\Tarif;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    private const BATAS = 20;

    public function run(): void
    {
        $berkas = database_path('data/seed_paket_dikirim.csv');
        if (!is_file($berkas)) {
            $this->command->error("Berkas tidak ditemukan: $berkas");
            return;
        }

        $fh = fopen($berkas, 'r');
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), fgetcsv($fh, 0, ',', '"', ''));
        $baris = [];
        while (($r = fgetcsv($fh, 0, ',', '"', '')) !== false) {
            if ($r === [null] || count($r) !== count($header)) continue;
            $baris[] = array_combine($header, array_map('trim', $r));
        }
        fclose($fh);

        $admin    = User::where('role', 'admin')->first();
        $pengirim = Pelanggan::orderBy('id')->first();
        $prefix   = 'PKE-' . now()->format('ymd') . '-';
        $urut     = (int) substr((string) Paket::where('no_resi', 'like', $prefix . '%')->max('no_resi'), -4);

        $total = [];     // jumlah paket Dikirim per kurir
        $tambah = [];    // jumlah paket yang ditambah seeder

        foreach ($baris as $r) {
            $kode  = $r['kode_kurir'];
            $kurir = Kurir::where('kode_kurir', $kode)->first();
            if (!$kurir) {
                $this->command->error("Kurir $kode belum ada. Jalankan php artisan impor:data-awal dulu.");
                return;
            }
            $total[$kode] ??= Paket::where('kurir_id', $kurir->id)->where('status', StatusPaket::Dikirim)->count();

            // baris ini sudah pernah dibuat seeder: lewati (aman diulang)
            if (Paket::where('nama_penerima', $r['penerima'])->where('telepon_penerima', $r['telepon_penerima'])->exists()) continue;
            // kurir sudah membawa 20 paket Dikirim (termasuk dari data awal): berhenti menambah
            if ($total[$kode] >= self::BATAS) continue;

            $zona    = Zona::findOrFail((int) $r['zona']);
            $layanan = strtolower($r['layanan']);
            $berat   = (float) $r['berat_kg'];
            $urut++;

            $paket = Paket::create([
                'no_resi'          => $prefix . str_pad((string) $urut, 4, '0', STR_PAD_LEFT),
                'pelanggan_id'     => $pengirim?->id,
                'zona_id'          => $zona->id,
                'kurir_id'         => $kurir->id,
                'nama_penerima'    => $r['penerima'],
                'telepon_penerima' => $r['telepon_penerima'],
                'alamat_penerima'  => $r['alamat_penerima'],
                'kota_tujuan'      => $r['kota_tujuan'],
                'zona_manual'      => false,
                'berat'            => $berat,
                'perlu_verifikasi' => false,
                'layanan'          => $layanan,
                'tarif_per_kg'     => $zona->tarif_per_kg,
                'ongkir'           => Tarif::ongkir($berat, $zona->tarif_per_kg, $layanan),
                'is_cod'           => false,
                'nilai_cod'        => 0,
                'biaya_cod'        => 0,
                'status'           => StatusPaket::Dikirim,
                'jumlah_gagal'     => 0,
                'tanggal_masuk'    => now()->toDateString(),
            ]);

            $jalur = [
                [null, StatusPaket::Diterima, 'Paket diterima di loket'],
                [StatusPaket::Diterima, StatusPaket::Transit, null],
                [StatusPaket::Transit, StatusPaket::Dikirim, 'Ditugaskan ke kurir'],
            ];
            foreach ($jalur as $i => [$dari, $ke, $catatan]) {
                RiwayatStatus::create([
                    'paket_id' => $paket->id, 'status_dari' => $dari, 'status_ke' => $ke,
                    'pelaku_id' => $admin?->id, 'catatan' => $catatan, 'waktu' => now()->subMinutes(30 - $i * 10),
                ]);
            }

            $total[$kode]++;
            $tambah[$kode] = ($tambah[$kode] ?? 0) + 1;
        }

        foreach ($total as $kode => $n) {
            $this->command->info("$kode: " . ($tambah[$kode] ?? 0) . " paket ditambahkan, total Dikirim $n");
        }
    }
}