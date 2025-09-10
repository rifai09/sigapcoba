<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\SSOService;

class Logout
{
    protected $ssoService;

    public function __construct(SSOService $ssoService)
    {
        $this->ssoService = $ssoService;
    }
    /**
     * Log the current user out of the application.
     */
    public function __invoke()
    {
        $ssoLogin = Session::get('sso_user', false);
        // 1️⃣ Log out Laravel
        Auth::guard('web')->logout();
        Session::flush();
        Session::invalidate();
        Session::regenerateToken();

        if ($ssoLogin) {
            return $this->ssoService->logout(true);
        }

        // Default Laravel logout → just go home
        return redirect('/');
    }
}
