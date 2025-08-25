<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Contoh pemakaian: ->middleware('role:kepala_unit,katimker')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if (!in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}
