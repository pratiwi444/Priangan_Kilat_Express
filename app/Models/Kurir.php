<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kurir extends Model
{
    protected $table = 'kurir';
    protected $fillable = ['user_id', 'kode_kurir', 'nama', 'telepon', 'aktif'];
    protected $casts = ['aktif' => 'boolean'];
    public function user() { return $this->belongsTo(User::class); }
    public function paket() { return $this->hasMany(Paket::class); }
}