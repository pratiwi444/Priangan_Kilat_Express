<?php

namespace Database\Seeders;

use App\Models\{User, Zona};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['nama' => 'Zona 1', 'wilayah' => 'Bandung Raya', 'tarif_per_kg' => 8000],
            ['nama' => 'Zona 2', 'wilayah' => 'Jawa Barat lainnya', 'tarif_per_kg' => 12000],
            ['nama' => 'Zona 3', 'wilayah' => 'Luar Jawa Barat',    'tarif_per_kg' => 20000],
    ] as $z) {
        Zona::updateOrCreate(['nama' => $z['nama']], $z);
    }
    User::updateOrCreate(['username' => 'admin'],
        ['nama' => 'Admin Loket', 'password' => Hash::make('admin12345'), 'role' => 'admin']);
    User::updateOrCreate(['username' => 'manajer'],
        ['nama' => 'Manajer', 'password' => Hash::make('manajer12345'), 'role' => 'manajer']);
    }
}
