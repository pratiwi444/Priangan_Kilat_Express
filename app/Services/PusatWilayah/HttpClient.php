<?php
namespace App\Services\PusatWilayah;

use Illuminate\Support\Facades\Http;

class HttpClient implements PusatWilayahClient
{
    public function cariZona(string $kota): ?int
    {
        try {
            $res = Http::withHeaders(['X-API-Key' => config('pusat_wilayah.api_key')])
                ->timeout(5)
                ->get(rtrim(config('pusat_wilayah.base_url'), '/') . '/wilayah', ['kota' => $kota]);
            if (!$res->successful()) return null;
            $zona = $res->json('zona');
            return $zona === null ? null : (int) $zona;
        } catch (\Throwable $e) {
            return null;     // layanan mati, timeout, API key salah: pakai zona manual
        }
    }
}