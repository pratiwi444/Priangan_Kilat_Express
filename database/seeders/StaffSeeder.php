<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    
    public function run(): void
    {
        //
        User::updateOrCreate(
            ['username' => 'admin'],
            ['nama' => 'Admin', 'password' => Hash::make('admin12345'),
             'role' => 'admin', 'wajib_ganti_password' => false]
        );

        User::updateOrCreate(
            ['username' => 'manajer'],
            ['nama' => 'Manajer', 'password' => Hash::make('manajer12345'),
             'role' => 'manajer', 'wajib_ganti_password' => false]
        );
    }
}
