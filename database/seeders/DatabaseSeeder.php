<?php

namespace Database\Seeders;

use App\Models\{User, Zona};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // id zona = nomor zona (1, 2, 3), karena Pusat Wilayah mengembalikan nomor zona
        $zona = [
            1 => ['Zona 1 (Bandung Raya)', 8000],
            2 => ['Zona 2 (Jawa Barat lainnya)', 12000],
            3 => ['Zona 3 (luar Jawa Barat)', 20000],
        ];
        foreach ($zona as $id => [$nama, $tarif]) {
            Zona::firstOrCreate(['id' => $id], ['nama' => $nama, 'tarif_per_kg' => $tarif]);
        }
        User::updateOrCreate(['username' => 'admin'],
            ['nama' => 'Admin Loket', 'password' => Hash::make('admin12345'), 'role' => 'admin']);
        User::updateOrCreate(['username' => 'manajer'],
            ['nama' => 'Manajer', 'password' => Hash::make('manajer12345'), 'role' => 'manajer']);
    }
}