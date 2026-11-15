<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    public $timestamps = false;
    protected $table = 'paket';
    protected $guarded = [];
    protected $casts = [
        'status' => \App\Enums\StatusPaket::class,
        'is_cod' => 'boolean', 'zona_manual' => 'boolean', 'perlu_verifikasi' => 'boolean',
        'tanggal_masuk' => 'date',
    ];
    public function pelanggan() { return $this->belongsTo(Pelanggan::class); }
    public function zona() { return $this->belongsTo(Zona::class); }
    public function kurir() { return $this->belongsTo(Kurir::class); }
    public function riwayat() { return $this->hasMany(RiwayatStatus::class)->orderBy('waktu'); }
    public function setoran() { return $this->hasOne(SetoranCod::class); }
}