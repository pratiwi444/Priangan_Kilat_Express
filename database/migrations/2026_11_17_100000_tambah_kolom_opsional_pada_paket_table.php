<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Fitur opsional O1 (berat volumetrik) dan O2 (asuransi). Semua kolom boleh kosong / default,
// jadi data lama (termasuk impor CSV) tidak terpengaruh.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paket', function (Blueprint $table) {
            $table->unsignedSmallInteger('panjang_cm')->nullable()->after('berat');
            $table->unsignedSmallInteger('lebar_cm')->nullable()->after('panjang_cm');
            $table->unsignedSmallInteger('tinggi_cm')->nullable()->after('lebar_cm');
            $table->decimal('berat_volumetrik', 8, 2)->nullable()->after('tinggi_cm');
            $table->unsignedSmallInteger('berat_tagih')->nullable()->after('berat_volumetrik'); // kg yang dipakai menghitung ongkir
            $table->boolean('asuransi')->default(false)->after('biaya_cod');
            $table->unsignedBigInteger('nilai_barang')->default(0)->after('asuransi');
            $table->unsignedInteger('biaya_asuransi')->default(0)->after('nilai_barang');
        });
    }

    public function down(): void
    {
        Schema::table('paket', function (Blueprint $table) {
            $table->dropColumn(['panjang_cm', 'lebar_cm', 'tinggi_cm', 'berat_volumetrik', 'berat_tagih',
                'asuransi', 'nilai_barang', 'biaya_asuransi']);
        });
    }
};