<?php
namespace App\Services\PusatWilayah;

class FakeClient implements PusatWilayahClient
{
    public function cariZona(string $kota): ?int
    {
        $peta = ['bandung' => 1, 'cimahi' => 1, 'bekasi' => 2, 'garut' => 2, 'surabaya' => 3];
        return $peta[strtolower(trim($kota))] ?? null;
    }
}