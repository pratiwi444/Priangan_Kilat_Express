<?php
namespace App\Services\PusatWilayah;

use Illuminate\Support\Facades\Http;

class HttpClient implements PusatWilayahClient
{
    public function cariZona(string $kota): ?int
    {
        try {
            $res = Http::withHeaders(['X-API-Key' => config('pusat_wilayah.api_key')])   // sesuaikan nama header
                ->timeout(5)
                ->get(config('pusat_wilayah.base_url'), ['kota' => $kota]);              // sesuaikan path dan parameter
            if (!$res->successful()) return null;
            $zona = $res->json('zona');                                                  // sesuaikan nama field
            return $zona === null ? null : (int) $zona;
        } catch (\Throwable $e) {
            return null;     // layanan mati, timeout, API key salah: pakai zona manual
        }
    }
}