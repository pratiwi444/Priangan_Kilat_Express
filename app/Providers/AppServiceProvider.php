<?php

namespace App\Providers;

use App\Services\PusatWilayah\{FakeClient, HttpClient, PusatWilayahClient};
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PusatWilayahClient::class,
            fn () => config('pusat_wilayah.driver') === 'fake' ? new FakeClient() : new HttpClient()
        );
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();   // tautan halaman memakai gaya Bootstrap
    }
}