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
        Schema::create('setoran_cod', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_id')->unique()->constrained('paket');
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users');
            $table->dateTime('waktu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setoran_cod');
    }
};