<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatStatus extends Model
{
    protected $table = 'riwayat_status';
    protected $guarded = [];
    protected $casts = [
        'status_dari' => \App\Enums\StatusPaket::class,
        'status_ke' => \App\Enums\StatusPaket::class,
        'waktu' => 'datetime',
    ];
    public function pelaku() {  return $this->belongsTo(User::class, 'pelaku_id'); }
}
