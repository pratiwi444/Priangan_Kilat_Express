<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    public $timestamps = false;
    protected $table = 'zona';
    protected $fillable = ['nama', 'tarif_per_kg'];
    public function paket() { return $this->hasMany(Paket::class); }
}