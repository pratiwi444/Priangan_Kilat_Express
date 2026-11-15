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
    // diisi di bab 3 sampai 9
});
Route::middleware(['auth', 'role:manajer'])->prefix('manajer')->name('manajer.')->group(function () {
    // diisi di bab 4 dan 10
});