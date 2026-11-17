<?php

use App\Http\Controllers\Api\KurirApiController as K;
use Illuminate\Support\Facades\Route;

Route::post('/login', [K::class, 'login']);

Route::middleware(['auth:sanctum', 'role:kurir'])->group(function () {
    Route::post('/logout', [K::class, 'logout']);
    Route::post('/ganti-password', [K::class, 'gantiPassword']);

    Route::middleware('wajib.ganti')->group(function () {
        Route::get('/paket-tugas', [K::class, 'paketTugas']);
        Route::get('/paket/{paket}', [K::class, 'detail']);
        Route::post('/paket/{paket}/terkirim', [K::class, 'terkirim']);
        Route::post('/paket/{paket}/gagal-kirim', [K::class, 'gagalKirim']);
        Route::get('/cod', [K::class, 'cod']);
    });
});