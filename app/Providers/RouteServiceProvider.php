<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to redirect users after login/registration.
     */
    public const HOME = '/home';

    /**
     * Bootstrap any route services.
     */
    public function boot(): void
    {
        // Keep empty to use Laravel 11/12 default routing (bootstrap/app.php -> withRouting()).

        // If you want this provider to load routes instead, uncomment:
        /*
        $this->routes(function () {
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        });
        */
    }
}
