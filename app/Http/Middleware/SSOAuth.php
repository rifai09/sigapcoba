<?php

namespace App\Http\Middleware;

use App\Services\SSOService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SSOAuth
{
    private $service;

    public function __construct(SSOService $service)
    {
        $this->service = $service;
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            // ✅ If already authenticated
            if (Auth::check()) {
                // Only check presence if logged in via SSO
                if ($request->session()->has('sso_token')) {
                    $presence = $this->service->checkPresence($request);
                    if ($presence === null) {
                        // Token invalid or expired → force re-login via SSO
                        return redirect('/login/sso');
                    }
                }

                return $next($request);
            }

            // ✅ User not authenticated yet → try SSO login flows
            // 1. Try proxy headers
            $user = $this->service->authenticateFromProxy($request);
            if ($user) {
                return $next($request);
            }

            // 2. Try Bearer JWT
            $user = $this->service->authenticateFromBearer($request);
            if ($user) {
                return $next($request);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        // ✅ Fall back to normal login (Socialite or local form login)
        return $next($request);
    }
}
