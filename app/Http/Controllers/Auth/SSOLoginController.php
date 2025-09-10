<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Services\SSOService;

class SSOLoginController extends Controller
{
    private $service;

    public function __construct(SSOService $service)
    {
        $this->service = $service;
    }

    public function redirectToKeycloak()
    {
        $client = new Client([
            'verify' => config('services.sso.ca_file') // same CA file you use in service
        ]);

        return Socialite::driver('sso')
            ->setHttpClient($client)
            ->redirect();
    }
    public function handleKeycloakCallback(Request $request)
    {
        $client = new Client([
            'verify' => config('services.sso.ca_file')
        ]);
    
        $socialiteUser = Socialite::driver('sso')
            ->setHttpClient($client)
            ->user();
    
        // 🔹 Token from Socialite response
        $token = $socialiteUser->token;
    
        // 🔹 User data (socialite returns array of claims too)
        $userData = [
            'nik'   => $socialiteUser->user['preferred_username'] ?? $socialiteUser->getNickname(),
            'name'  => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
        ];
    
        // 🔹 Reuse the unified SSO authentication flow
        $user = $this->service->authenticate($request, $token, $userData);
    
        if (!$user) {
            return redirect('/login')->withErrors(['sso' => 'Unable to authenticate user via SSO']);
        }
    
        return redirect()->intended(route('dashboard', absolute: true));
    }
    
}
