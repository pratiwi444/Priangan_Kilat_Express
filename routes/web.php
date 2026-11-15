<?php

use App\Http\Controllers\{AuthController, CekResiController};

//Route::get('/', fn () => redirect('/cek-resi'));
//Route::get('/cek-resi', [CekResiController::class, 'index']);

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
});
Route::middleware(['auth', 'role:manajer'])->prefix('manajer')->name('manajer.')->group(function () {
    Route::get('pelanggan', [PelangganController::class, 'index'])->name('pelanggan');
    Route::get('kurir', [KurirController::class, 'index'])->name('kurir');
});