<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetoranCod extends Model
{
    protected $table = 'setoran_cod';
    protected $guarded = [];
    protected $casts = ['waktu' => 'datetime'];
}
