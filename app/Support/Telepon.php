<?php

namespace App\Support;

class Telepon
{
    public static function normalisasi(string $no): ?string
    {
        $no = trim($no);

        if (preg_match('/[A-Za-z]/', $no)) {
            return null;
        }

        $no = preg_replace('/[^0-9+]/', '', $no);
        $no = preg_replace('/^\+?62/', '0', $no);

        return preg_match('/^0\d{9,12}$/', $no) ? $no : null;
    }

    public static function samarkan(string $no): string
    {
        return substr($no, 0, 7) . 'xxxx';
    }
}