
<?php

namespace App\Support;

class Tarif
{
    /**
     * Menghitung ongkos kirim.
     * Berat dibulatkan ke atas, minimum 1 kg.
     * Express mendapat tambahan 50%.
     */
    public static function ongkir(
        ?float $berat,
        ?float $tarif,
        string $layanan
    ): ?int {
        if ($berat === null || $tarif === null || $berat <= 0 || $tarif <= 0) {
            return null;
        }

        $beratKg = max(1, (int) ceil($berat));

        $ongkir = $beratKg * $tarif;

        if (strtolower($layanan) === 'express') {
            $ongkir *= 1.5;
        }

        return (int) round($ongkir);
    }

    /**
     * Biaya COD 2%, minimum Rp2.000.
     */
    public static function biayaCod(float $nilaiCod): int
    {
        if ($nilaiCod <= 0) {
            return 0;
        }

        return max(2000, (int) round($nilaiCod * 0.02));
    }
}