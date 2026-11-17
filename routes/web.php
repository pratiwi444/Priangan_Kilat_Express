<?php

use App\Http\Controllers\Admin\{PelangganController, KurirController, PaketController, SetoranController};
use App\Http\Controllers\TarifController;
use App\Http\Controllers\{AuthController, CekResiController};
use App\Http\Controllers\Manajer\LaporanController;

Route::get('/', fn () => redirect('/cek-resi'));
Route::get('/cek-resi', [CekResiController::class, 'index'])->name('cek-resi');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'form'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/ganti-password', [AuthController::class, 'formGanti']);
    Route::post('/ganti-password', [AuthController::class, 'ganti']);
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('pelanggan', PelangganController::class)->except(['show', 'destroy']);
    Route::resource('kurir', KurirController::class)->except(['show', 'destroy']);
    Route::post('kurir/{kurir}/reset-password', [KurirController::class, 'resetPassword'])->name('kurir.reset');
    Route::post('kurir/{kurir}/nonaktifkan', [KurirController::class, 'nonaktifkan'])->name('kurir.nonaktif');
    Route::get('tarif', [TarifController::class, 'index'])->name('tarif');
    Route::post('paket/cek-zona', [PaketController::class, 'cekZona'])->name('paket.cek-zona');
    Route::resource('paket', PaketController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('paket/{paket}/status', [PaketController::class, 'ubahStatus'])->name('paket.status');
    Route::post('paket/{paket}/tugaskan', [PaketController::class, 'tugaskan'])->name('paket.tugaskan');
    Route::get('setoran', [SetoranController::class, 'index'])->name('setoran.index');
    Route::post('setoran', [SetoranController::class, 'store'])->name('setoran.store');
});
Route::middleware(['auth', 'role:manajer'])->prefix('manajer')->name('manajer.')->group(function () {
    Route::get('pelanggan', [PelangganController::class, 'index'])->name('pelanggan');
    Route::get('kurir', [KurirController::class, 'index'])->name('kurir');
    Route::get('tarif', [TarifController::class, 'index'])->name('tarif');
    Route::post('tarif/{zona}', [TarifController::class, 'update'])->name('tarif.update');
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan');
});
Route::get('/', fn () => redirect('/cek-resi'));
Route::get('/cek-resi', [CekResiController::class, 'index'])->name('cek-resi');