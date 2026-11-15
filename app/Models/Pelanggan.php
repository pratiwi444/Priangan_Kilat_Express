<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    public $timestamps = false;
    protected $table = 'pelanggan';
    protected $fillable = ['nama', 'telepon', 'alamat'];
    public function paket() { return $this->hasMany(Paket::class);}
}