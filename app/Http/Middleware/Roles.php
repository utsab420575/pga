<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Roles
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        // If your User model has hasAnyRole([...]) keep using it
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return $next($request);
        }

        // Fallback: single "role" column
        if (in_array($user->role ?? null, $roles, true)) {
            return $next($request);
        }

        return back()->with('Status', 'You are not allowed to access this page.');
    }
}
