<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Roles;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\TrustProxies;



return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // register/override aliases
        $middleware->alias([
            'roles' => Roles::class,
            'guest' => RedirectIfAuthenticated::class, // for already-logged users
            'auth'  => Authenticate::class,            // if you created the override above
            'trust.proxies' => TrustProxies::class,    // optional alias if you need it
        ]);

         // Payment API routes called by the central payment system (server to server, no CSRF token)
        $middleware->validateCsrfTokens(except: [
            '/bkash_pull',
            '/bkash_push',
            '/agrani_pull',
            '/agrani_push',
            '/upay_pull',
            '/upay_push',
        ]);

        // If you want to push custom middleware into groups:
        // $middleware->appendToGroup('web', \App\Http\Middleware\YourWebMiddleware::class);
        // $middleware->appendToGroup('api', \App\Http\Middleware\YourApiMiddleware::class);
    })
    ->withExceptions(function ($exceptions) {
        //
    })->create();

