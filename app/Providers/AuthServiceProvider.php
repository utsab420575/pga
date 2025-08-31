<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // \App\Models\Post::class => \App\Policies\PostPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Example Gate (optional):
        // Gate::define('admin-only', fn ($user) => in_array('admin', $user->roles ?? []));
    }
}
