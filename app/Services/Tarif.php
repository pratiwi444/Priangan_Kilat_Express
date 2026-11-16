<?php 
namespace App\Services;


class Tarif
{
    public static function ongkir(float $berat, int $tarifPeKg, string $layanan): int
    {
        $kg = max(1, (int) ceil($berat));
        $ongkir = $kg * $tarifPeKg;
        return $layanan === 'express' ? (int) round($ongkir * 1.5) : $ongkir;
    }

    public static function biayaCod(int $nilaicod): int
    {
        return max(2000, (int) round($nilaicod * 0.02));
    }
}