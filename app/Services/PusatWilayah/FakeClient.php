<?php 
namespace App\Services\PusatWilayah;

use Override;
use PhpParser\Builder\Function_;
use PhpParser\Node\Expr\FuncCall;

class FakeClient implements PusatWilayahClient
{
    #[Override]
    public Function cariZona(string $kota): ?int
    {
        $peta = ['bandung' => 1, 'cimahi' => 2, 'garut' => 2, 'surabaya' => 3];
        return $peta[strtolower(trim($kota))] ?? null;
    }
}