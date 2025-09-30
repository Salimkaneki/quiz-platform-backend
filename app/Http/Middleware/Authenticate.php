<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Pour les routes API, ne pas rediriger, laisser lever l'exception
        if ($request->is('api/*')) {
            return null;
        }

        // Pour les autres routes, rediriger vers login si défini
        return route('login', [], false) ?: '/login';
    }
}