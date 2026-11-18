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
        'tanggal_masuk' => 'date', 'asuransi' => 'boolean',
    ];
    /** Dipakai untuk menandai paket tertahan (O4): paket yang menyisipkan kolom transit_sejak. */
    public function scopeDenganTransitSejak($q)
    {
        return $q->select('paket.*')->addSelect(['transit_sejak' => \Illuminate\Support\Facades\DB::table('riwayat_status')
            ->selectRaw('max(waktu)')
            ->whereColumn('riwayat_status.paket_id', 'paket.id')
            ->where('riwayat_status.status_ke', 'transit')]);
    }

    /** O4: berstatus Transit lebih dari 3 hari (dihitung sejak status Transit terakhir). */
    public function getTertahanAttribute(): bool
    {
        if ($this->status !== \App\Enums\StatusPaket::Transit) return false;
        $sejak = array_key_exists('transit_sejak', $this->attributes)
            ? $this->attributes['transit_sejak']
            : \Illuminate\Support\Facades\DB::table('riwayat_status')
                ->where('paket_id', $this->id)->where('status_ke', 'transit')->max('waktu');
        return $sejak !== null && \Carbon\Carbon::parse($sejak)->lt(now()->subDays(3));
    }

    public function pelanggan() { return $this->belongsTo(Pelanggan::class); }
    public function zona() { return $this->belongsTo(Zona::class); }
    public function kurir() { return $this->belongsTo(Kurir::class); }
    public function riwayat() { return $this->hasMany(RiwayatStatus::class)->orderBy('waktu'); }
    public function setoran() { return $this->hasOne(SetoranCod::class); }
}