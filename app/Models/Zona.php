<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $table = 'zona';
    protected $fillable = ['nama', 'wilayah', 'tarif_per_kg'];
    public function paket() { return $this->hasMany(Paket::class); }
}
