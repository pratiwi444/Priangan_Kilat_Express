<?php

namespace Database\Seeders;

use App\Models\{User, Zona};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // master zona dari CSV; firstOrCreate: seeder diulang tidak mengembalikan tarif yang sudah diubah Manajer
        $fh = fopen(database_path('data/master_zona_tarif.csv'), 'r');
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), fgetcsv($fh, 0, ',', '"', ''));
        while (($r = fgetcsv($fh, 0, ',', '"', '')) !== false) {
            if ($r === [null] || count($r) !== count($header)) continue;
            $r = array_combine($header, array_map('trim', $r));
            Zona::firstOrCreate(
                ['id' => (int) $r['zona']],   // id zona = nomor zona (dipakai Pusat Wilayah)
                ['nama' => 'Zona ' . $r['zona'] . ' (' . $r['nama_zona'] . ')', 'tarif_per_kg' => (int) $r['tarif_per_kg']]
            );
        }
        fclose($fh);

        User::updateOrCreate(['username' => 'admin'],
            ['nama' => 'Admin Loket', 'password' => Hash::make('admin12345'), 'role' => 'admin']);
        User::updateOrCreate(['username' => 'manajer'],
            ['nama' => 'Manajer', 'password' => Hash::make('manajer12345'), 'role' => 'manajer']);
    }
}