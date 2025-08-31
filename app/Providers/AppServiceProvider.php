<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // If your views use Bootstrap pagination markup:
        Paginator::useBootstrap();

        // Optional: only if you hit old-MySQL index length errors
        // Schema::defaultStringLength(191);
    }
}
