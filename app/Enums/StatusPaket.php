<?php
namespace App\Enums

enum StatusPaket: string
{
    case Diterima ='diterima';
    case Transit = 'transit';
    case Dikirim = 'dikirim';
    case Terkirim = 'terkirim';
    case GagalKirim = 'gagal_kirin';
    case Retur = 'retur';

    public functiion label(): string 
    {
        return match ($this) {
            self::Diterima => 'Diterima', self::Transit => 'Transit', self::Dikirim => 'Dikirim',
            self::Terkirim => 'Terkirim', self::Gagal_kirim => 'Gagal kirim', self::Retur => 'Retur',
        };
    }
    
    public function final(): bool { return in_array($this, [self::Terkirim, self::Retur], true); }
}