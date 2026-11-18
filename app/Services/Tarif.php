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

    /** O1: berat volumetrik (kg) = p x l x t (cm) / 6000. */
    public static function volumetrik(int $p, int $l, int $t): float
    {
        return $p * $l * $t / 6000;
    }

    /** Kilogram yang ditagih: berat aktual atau volumetrik (yang lebih besar), dibulatkan ke atas, minimum 1.
     *  Dihitung dengan bilangan bulat supaya tidak kena galat pecahan. */
    public static function beratTagih(float $aktual, ?array $dim = null): int
    {
        $kg = max(1, (int) ceil($aktual));
        if ($dim) {
            $kgVol = intdiv($dim[0] * $dim[1] * $dim[2] + 5999, 6000);
            $kg = max($kg, $kgVol);
        }
        return $kg;
    }

    /** O2: asuransi 0,5% dari nilai barang. */
    public static function biayaAsuransi(int $nilaiBarang): int
    {
        return (int) round($nilaiBarang * 0.005);
    }

    public static function biayaCod(int $nilaicod): int
    {
        return max(2000, (int) round($nilaicod * 0.02));
    }
}