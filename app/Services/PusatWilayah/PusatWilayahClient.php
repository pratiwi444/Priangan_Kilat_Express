<?php 
namespace App\Services\PusatWilayah;

interface PusatWilayahClient
{
    public function cariZona(string $kota): ?int;
}