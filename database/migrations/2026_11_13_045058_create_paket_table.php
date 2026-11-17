<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paket', function (Blueprint $table) {
            $table->id();
            $table->string('no_resi', 20)->unique();
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggan');
            $table->foreignId('zona_id')->nullable()->constrained('zona');
            $table->foreignId('kurir_id')->nullable()->constrained('kurir');
            $table->string('nama_penerima', 100);
            $table->string('telepon_penerima', 15);
            $table->string('alamat_penerima', 255)->nullable();
            $table->string('kota_tujuan', 100);
            $table->boolean('zona_manual')->default(false);
            $table->decimal('berat', 6, 2)->nullable();
            $table->boolean('perlu_terverifikasi')->default('false');
            $table->string('layanan', 10)->default('reguler');
            $table->unsignedBigInteger('tarif_per_kg')->nullable();
            $table->unsignedBigInteger('ongkir')->nullable();
            $table->boolean('is_cod')->default(false);
            $table->unsignedInteger('nilai_cod')->default(0);
            $table->unsignedInteger('biaya_cod')->default(0);
            $table->string('status', 20)->default('diterima');
            $table->unsignedInteger('jumlah_gagal')->default(0);
            $table->date('tanggal_masuk');
            $table->timestamps();

            $table->index('status');
            $table->index('tanggal_masuk');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket');
    }
};
