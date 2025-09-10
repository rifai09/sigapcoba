<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AnyAuth
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            return $next($request);
        }

        // If you also track Keycloak session separately:
        // if ($request->session()->has('sso_user')) {
        //     return $next($request);
        // }

        return redirect('/login');
    }
}
