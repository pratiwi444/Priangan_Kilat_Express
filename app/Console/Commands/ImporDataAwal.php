<?php

namespace App\Console\Commands;

use App\Services\ImporCsv;
use Illuminate\Console\Command;

class ImporDataAwal extends Command
{
    protected $signature = 'impor:data-awal {folder=database/data : Folder berisi kurir.csv, pelanggan.csv, paket_historis.csv}';

    protected $description = 'Impor kurir, pelanggan, dan paket historis dari CSV (aman dijalankan ulang)';

    public function handle(ImporCsv $impor): int
    {
        $folder = base_path($this->argument('folder'));

        // urutan penting: kurir dan pelanggan dulu, supaya paket historis bisa terhubung ke keduanya
        $langkah = [
            ['Kurir',          'kurir',     'kurir.csv'],
            ['Pelanggan',      'pelanggan', 'pelanggan.csv'],
            ['Paket historis', 'paket',     'paket_historis.csv'],
        ];

        foreach ($langkah as [$judul, $jenis, $nama]) {
            $berkas = $folder . DIRECTORY_SEPARATOR . $nama;
            $this->newLine();
            $this->info("== $judul ($nama)");

            if (!is_file($berkas)) {
                $this->error("Berkas tidak ditemukan: $berkas");
                return self::FAILURE;
            }

            try {
                $lap = $impor->$jenis($berkas);
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
                return self::FAILURE;
            }

            foreach ($impor->ringkas($lap, $jenis) as [$keterangan, $jumlah]) {
                $this->line(sprintf('  %-70s %d', $keterangan, $jumlah));
            }
            foreach ($lap['tidak_valid'] as $pesan) {
                $this->warn("  $pesan");
            }
        }

        $this->newLine();
        $this->info('Impor selesai.');
        return self::SUCCESS;
    }
}
