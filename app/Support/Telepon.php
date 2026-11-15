<?php
namespace App\Support;

class Telepon
{
    /** Nomor rapi (08...) atau null bila tidak valid. */
    public static function normalisasi(string $no): ?string
    {
        $no = trim($no);
        if (preg_match('/[A-Za-z]/', $no)) return null;            // berisi huruf: ditolak
        $no = preg_replace('/[^0-9+]/', '', $no);                  // buang spasi, -, (, )
        $no = preg_replace('/^\+?62/', '0', $no);                  // +62 / 62 menjadi 0
        return preg_match('/^08\d{8,11}$/', $no) ? $no : null;     // 10 sampai 13 digit
    }

    /** Untuk cek resi: 7 digit awal + xxxx (contoh dokumen 0812789). */
    public static function samarkan(string $no): string { return substr($no, 0, 7) . 'xxxx'; }
}