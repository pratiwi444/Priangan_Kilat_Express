<?php

namespace App\Services;

use App\Enums\StatusPaket;
use App\Models\{Kurir, Paket, Pelanggan, RiwayatStatus, SetoranCod, User, Zona};
use App\Services\PusatWilayah\PusatWilayahClient;
use App\Support\Telepon;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Impor data awal dari CSV. Dipakai oleh perintah `impor:data-awal` dan halaman Impor CSV.
 * Aman dijalankan ulang: data yang sudah ada dilewati, tidak ditimpa.
 * Baris yang rusak tidak menghentikan impor; dilewati lalu dilaporkan.
 */
class ImporCsv
{
    private const PASSWORD_SEMENTARA = 'kurir12345';

    /** @var array<string, ?Zona> */
    private array $cacheZona = [];

    public function __construct(private PusatWilayahClient $wilayah) {}

    // ------------------------------------------------------------ kurir

    public function kurir(string $berkas): array
    {
        [$baris, $lap] = $this->mulai($berkas, ['kode', 'nama', 'telepon']);

        foreach ($baris as $no => $r) {
            $lap['total']++;
            try {
                if ($r['kode'] === '' || $r['nama'] === '') {
                    $this->tolak($lap, $no, 'kode atau nama kosong');
                    continue;
                }
                if (Kurir::where('kode_kurir', $r['kode'])->exists()) {
                    $lap['dilewati']++;
                    continue;
                }

                $nama = $this->rapikanNama($r['nama']);
                [$tel, $telBerubah] = $this->rapikanTelepon($r['telepon']);

                DB::transaction(function () use ($r, $nama, $tel) {
                    $user = User::create([
                        'nama' => $nama, 'username' => $r['kode'], 'password' => self::PASSWORD_SEMENTARA,
                        'role' => 'kurir', 'wajib_ganti_password' => true,
                    ]);
                    Kurir::create([
                        'user_id' => $user->id, 'kode_kurir' => $r['kode'], 'nama' => $nama,
                        'telepon' => $tel, 'aktif' => true,
                    ]);
                });

                $lap['masuk']++;
                if ($telBerubah) $lap['telepon_dirapikan']++;
                if ($nama !== $r['nama']) $lap['nama_dirapikan']++;
            } catch (\Throwable $e) {
                $this->tolak($lap, $no, $e->getMessage());
            }
        }
        return $lap;
    }

    // ------------------------------------------------------------ pelanggan

    public function pelanggan(string $berkas): array
    {
        [$baris, $lap] = $this->mulai($berkas, ['nama', 'telepon', 'alamat']);

        foreach ($baris as $no => $r) {
            $lap['total']++;
            try {
                if ($r['nama'] === '' || $r['telepon'] === '') {
                    $this->tolak($lap, $no, 'nama atau telepon kosong');
                    continue;
                }
                $nama = $this->rapikanNama($r['nama']);
                [$tel, $telBerubah] = $this->rapikanTelepon($r['telepon']);

                if (Pelanggan::where('telepon', $tel)->exists()) {
                    $lap['dilewati']++;
                    continue;
                }
                Pelanggan::create([
                    'nama' => $nama, 'telepon' => $tel,
                    'alamat' => $r['alamat'] !== '' ? mb_substr($r['alamat'], 0, 255) : null,
                ]);   // kolom "kota" di CSV tidak dipakai (tabel pelanggan hanya punya alamat)

                $lap['masuk']++;
                if ($telBerubah) $lap['telepon_dirapikan']++;
                if ($nama !== $r['nama']) $lap['nama_dirapikan']++;
            } catch (\Throwable $e) {
                $this->tolak($lap, $no, $e->getMessage());
            }
        }
        return $lap;
    }

    // ------------------------------------------------------------ paket historis


public function paket(string $berkas): array
{
    [$baris, $lap] = $this->mulai($berkas, [
        'no_resi', 'tanggal_terima', 'pengirim', 'penerima', 'telepon_penerima',
        'kota_tujuan', 'berat_kg', 'layanan', 'status', 'kode_kurir', 'nilai_cod',
    ]);

    $sudahAda = [];

    foreach ($baris as $no => $r) {
        $lap['total']++;
        $resi = strtoupper(trim($r['no_resi']));

        if ($resi === '') {
            $this->tolak($lap, $no, 'nomor resi kosong');
            continue;
        }

        if (isset($sudahAda[$resi])) {
            $lap['ganda']++;

            $lap['resi_ganda'][] = [
                'no_resi' => $resi,
                'baris_pertama' => $sudahAda[$resi],
                'baris_duplikat' => $no,
            ];

            continue;
        }

        // Simpan nomor baris pertama agar duplikat bisa dilaporkan.
        $sudahAda[$resi] = $no;

        try {
            $this->satuPaket($r, $resi, $lap);
        } catch (\Throwable $e) {
            $this->tolak($lap, $no, $e->getMessage());
        }
    }

    return $lap;
}


    private function satuPaket(array $r, string $resi, array &$lap): void
    {
        if (Paket::where('no_resi', $resi)->exists()) {   // impor ulang: tidak menimpa data yang sudah ada
            $lap['dilewati']++;
            return;
        }

        $status = StatusPaket::tryFrom(str_replace(' ', '_', strtolower($r['status'])));
        if (!$status) throw new InvalidArgumentException("status '{$r['status']}' tidak dikenal");

        $layanan = strtolower($r['layanan']);
        if (!in_array($layanan, ['reguler', 'express'], true)) {
            throw new InvalidArgumentException("layanan '{$r['layanan']}' tidak dikenal");
        }

        $tanggal = Carbon::parse($r['tanggal_terima'])->startOfDay();

        $berat = $r['berat_kg'] === '' ? null : (float) $r['berat_kg'];
        if ($berat !== null && $berat <= 0) $berat = null;

        $nilaiCod = $r['nilai_cod'] === '' ? 0 : (int) round((float) $r['nilai_cod']);
        $isCod = $nilaiCod > 0;

        $namaPenerima = $this->rapikanNama($r['penerima']);
        [$tel, $telBerubah] = $this->rapikanTelepon($r['telepon_penerima']);

        $kurir = $r['kode_kurir'] !== '' ? Kurir::where('kode_kurir', $r['kode_kurir'])->first() : null;
        $pelanggan = $r['pengirim'] !== ''
            ? Pelanggan::where('nama', $this->rapikanNama($r['pengirim']))->first()
            : null;

        $zona = $this->cariZona($r['kota_tujuan']);
        $tarif = $zona?->tarif_per_kg;
        $ongkir = ($zona && $berat !== null) ? Tarif::ongkir($berat, $tarif, $layanan) : null;

        $setoran = false;
        DB::transaction(function () use (
            $resi, $status, $layanan, $tanggal, $berat, $nilaiCod, $isCod, $namaPenerima, $tel,
            $kurir, $pelanggan, $zona, $tarif, $ongkir, $r, &$setoran
        ) {
            $paket = Paket::create([
                'no_resi'          => $resi,
                'pelanggan_id'     => $pelanggan?->id,
                'zona_id'          => $zona?->id,
                'kurir_id'         => $kurir?->id,
                'nama_penerima'    => $namaPenerima,
                'telepon_penerima' => $tel,
                'alamat_penerima'  => null,                    // CSV paket historis tidak punya alamat
                'kota_tujuan'      => mb_substr($r['kota_tujuan'], 0, 100),
                'zona_manual'      => false,
                'berat'            => $berat,
                'perlu_verifikasi' => $berat === null,
                'layanan'          => $layanan,
                'tarif_per_kg'     => $tarif,
                'ongkir'           => $ongkir,
                'is_cod'           => $isCod,
                'nilai_cod'        => $nilaiCod,
                'biaya_cod'        => $isCod ? Tarif::biayaCod($nilaiCod) : 0,
                'status'           => $status,
                'jumlah_gagal'     => $status === StatusPaket::Retur ? 2 : 0,
                'tanggal_masuk'    => $tanggal->toDateString(),
            ]);

            RiwayatStatus::create([
                'paket_id' => $paket->id, 'status_dari' => null, 'status_ke' => $status,
                'pelaku_id' => null, 'catatan' => 'Data awal (impor CSV)', 'waktu' => $tanggal,
            ]);

            // paket COD yang sudah Terkirim dianggap COD-nya sudah disetor
            if ($isCod && $status === StatusPaket::Terkirim) {
                SetoranCod::create(['paket_id' => $paket->id, 'dicatat_oleh' => null, 'waktu' => $tanggal]);
                $setoran = true;
            }
        });

        $lap['masuk']++;
        if ($berat === null) $lap['tanpa_berat']++;
        if ($zona === null) $lap['tanpa_zona']++;
        if ($setoran) $lap['setoran']++;
        if ($telBerubah) $lap['telepon_dirapikan']++;
        if ($namaPenerima !== $r['penerima']) $lap['nama_dirapikan']++;
    }

    // ------------------------------------------------------------ ringkasan laporan

    /** @return list<array{0: string, 1: int}> pasangan [keterangan, jumlah] untuk layar dan terminal */
    public function ringkas(array $lap, string $jenis): array
    {
        $baris = [
            ['Baris dibaca', $lap['total']],
            ['Data baru masuk', $lap['masuk']],
            ['Sudah ada (dilewati)', $lap['dilewati']],
        ];
        if ($jenis === 'paket') {
            $baris[] = ['Resi ganda digabung menjadi satu', $lap['ganda']];
            $baris[] = ['Paket tanpa berat ditandai "perlu verifikasi"', $lap['tanpa_berat']];
            $baris[] = ['Setoran COD dicatat (paket COD Terkirim)', $lap['setoran']];
            $baris[] = ['Paket tanpa zona (kota tidak dikenal / layanan tidak tersedia)', $lap['tanpa_zona']];
        }
        $baris[] = ['Nomor telepon dirapikan', $lap['telepon_dirapikan']];
        $baris[] = ['Nama dirapikan', $lap['nama_dirapikan']];
        $baris[] = ['Baris tidak valid (dilewati)', count($lap['tidak_valid'])];
        return $baris;
    }

    // ------------------------------------------------------------ pembantu

    /** Zona dari Pusat Wilayah (dicache per kota). Layanan mati atau kota tidak dikenal: null. */
    private function cariZona(string $kota): ?Zona
    {
        $k = mb_strtolower(trim($kota));
        if (!array_key_exists($k, $this->cacheZona)) {
            $no = $k === '' ? null : $this->wilayah->cariZona($kota);
            $this->cacheZona[$k] = $no !== null ? Zona::find($no) : null;
        }
        return $this->cacheZona[$k];
    }

    public function rapikanNama(string $nama): string
    {
        $nama = preg_replace('/\s+/u', ' ', trim($nama));
        return mb_convert_case(mb_strtolower($nama, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    /** @return array{0: string, 1: bool} [nomor, apakah berubah]. Tidak valid: simpan apa adanya, jangan tolak baris. */
    private function rapikanTelepon(string $mentah): array
    {
        $mentah = trim($mentah);
        $hasil = Telepon::normalisasi($mentah) ?? $mentah;
        return [mb_substr($hasil, 0, 15), $hasil !== $mentah];
    }

    private function laporanKosong(): array
    {
        return [
            'total' => 0, 'masuk' => 0, 'dilewati' => 0, 'ganda' => 0, 'tanpa_berat' => 0,
            'telepon_dirapikan' => 0, 'nama_dirapikan' => 0, 'setoran' => 0, 'tanpa_zona' => 0,
            'tidak_valid' => [],
            'resi_ganda' => [],
        ];
    }

    private function tolak(array &$lap, int $no, string $alasan): void
    {
        $lap['tidak_valid'][] = "Baris $no: $alasan";
    }

    /**
     * Baca CSV. Lempar InvalidArgumentException bila berkas tidak terbaca atau kolom wajib tidak ada
     * (misalnya jenis data dan berkas tidak cocok). Baris dengan jumlah kolom salah dicatat sebagai tidak valid.
     *
     * @return array{0: array<int, array<string,string>>, 1: array} [baris indeks nomor baris, laporan awal]
     */
    private function mulai(string $berkas, array $kolomWajib): array
    {
        $fh = @fopen($berkas, 'r');
        if (!$fh) throw new InvalidArgumentException('Berkas tidak bisa dibuka.');

        $header = fgetcsv($fh, 0, ',', '"', '');
        if (!$header || $header === [null]) {
            fclose($fh);
            throw new InvalidArgumentException('Berkas kosong atau bukan CSV.');
        }
        $header = array_map(fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $header);

        $hilang = array_diff($kolomWajib, $header);
        if ($hilang) {
            fclose($fh);
            throw new InvalidArgumentException(
                'Kolom tidak sesuai, tidak ditemukan: ' . implode(', ', $hilang) . '. Pastikan jenis data dan berkas cocok.'
            );
        }

        $lap = $this->laporanKosong();
        $baris = [];
        $no = 1;
        while (($r = fgetcsv($fh, 0, ',', '"', '')) !== false) {
            $no++;
            if ($r === [null]) continue;                       // baris kosong
            if (count($r) !== count($header)) {
                $lap['total']++;
                $this->tolak($lap, $no, 'jumlah kolom tidak sesuai');
                continue;
            }
            $baris[$no] = array_combine($header, array_map(fn ($v) => trim((string) $v), $r));
        }
        fclose($fh);

        return [$baris, $lap];
    }
}