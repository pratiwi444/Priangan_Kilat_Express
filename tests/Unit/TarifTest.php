<?php

namespace Tests\Unit;

use App\Services\Tarif;
use PHPUnit\Framework\TestCase;

class TarifTest extends TestCase
{

public function test_ongkir(): void
{
    $this->assertSame(16000, Tarif::ongkir(1.2, 8000, 'reguler'));
    $this->assertSame(24000, Tarif::ongkir(1.2, 8000, 'express'));
    $this->assertSame(8000,  Tarif::ongkir(0.3, 8000, 'reguler'));
}
public function test_berat_volumetrik_dipakai_bila_lebih_besar(): void
{
    // 50x40x30 cm = 60000 / 6000 = 10 kg, lebih besar dari aktual 2 kg
    $this->assertSame(10, Tarif::beratTagih(2, [50, 40, 30]));
    // aktual lebih besar: tetap aktual (dibulatkan ke atas)
    $this->assertSame(5, Tarif::beratTagih(4.2, [20, 20, 20]));
    // volumetrik pecahan dibulatkan ke atas: 6001 / 6000 > 1 menjadi 2 kg
    $this->assertSame(2, Tarif::beratTagih(0.5, [6001, 1, 1]));
    $this->assertSame(80000, Tarif::ongkir((float) Tarif::beratTagih(2, [50, 40, 30]), 8000, 'reguler'));
}

public function test_biaya_asuransi(): void
{
    $this->assertSame(5000, Tarif::biayaAsuransi(1000000));
    $this->assertSame(0, Tarif::biayaAsuransi(0));
}

public function test_biaya_cod(): void
{
    $this->assertSame(2000, Tarif::biayaCod(50000));
    $this->assertSame(5000, Tarif::biayaCod(250000));
}
}