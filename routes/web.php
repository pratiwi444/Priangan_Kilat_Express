<?php

use App\Http\Controllers\LoginController;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
Route::get('/login', [LoginController::class, 'form'])->name('login');
Route::post('/login', [LoginController::class, 'proses'])
        ->middleware('throttle:5,1')   // batasi percobaan: 5x per menit
        ->name('login.proses');
});

Route::post('/logout', [LoginController::class, 'keluar'])
    ->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', fn () => view('admin.beranda'))->name('admin.beranda');
});

Route::middleware(['auth', 'role:manajer'])->group(function () {
    Route::get('/manajer', fn () => view('manajer.beranda'))->name('manajer.beranda');
});