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
public function test_biaya_cod(): void
{
    $this->assertSame(2000, Tarif::biayaCod(50000));
    $this->assertSame(5000, Tarif::biayaCod(250000));
}
}