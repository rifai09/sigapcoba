<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class SSOService
{
    // ================================
    // AUTHENTICATION HELPERS
    // ================================
    public function authenticateFromProxy(Request $request): ?array
    {
        $proxyUser = $request->header('X-Forwarded-Preferred-Username') 
                  ?? $request->header('X-Forwarded-User');

        if (!$proxyUser) {
            return null;
        }

        $proxyToken = $request->header('X-Forwarded-Access-Token');

        $userData = [
            'nik'   => $proxyUser,
            'name'  => $request->header('X-Forwarded-Name') ?: $proxyUser,
            'email' => $request->header('X-Forwarded-Email'),
        ];

        return $this->authenticate($request, $proxyToken, $userData);
    }

    public function authenticateFromBearer(Request $request): ?array
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return null;
        }

        $token = $matches[1];
        return $this->authenticate($request, $token);
    }

    /**
     * Unified authentication flow
     */
    public function authenticate(Request $request, ?string $token = null, array $userData = []): ?array
    {
        // Skip if token was previously invalidated
        if ($token && $this->isTokenInvalidated($request, $token)) {
            return null;
        }

        $decoded = null;
        if ($token) {
            $decoded = $this->verifyToken($token);
            if (!$decoded) {
                $this->invalidateToken($request, $token);
                $this->logout(true, $request->session());
                return null;
                // throw new \Exception('Invalid SSO token');
            }

            // Merge JWT claims into userData
            $userData = array_merge($userData, $this->extractUserData($decoded));
        }

        // Must at least have nik
        if (empty($userData['nik'])) {
            return null;
        }

        $user = $this->loginUser($request, $userData);

        if ($decoded) {
            $this->storeSsoUserInRequest($request, $decoded, $token);
            $this->mapSsoSession($decoded, $request->session()->getId());
        }

        return $user->toArray();
    }

    public function extractUserData(array $decoded): array
    {
        return [
            'nik'   => $decoded['preferred_username'] ?? $decoded['sub'] ?? null,
            'name'  => $decoded['name'] ?? $decoded['preferred_username'] ?? $decoded['sub'] ?? null,
            'email' => $decoded['email'] ?? null,
        ];
    }

    public function loginUser(Request $request, array $userData, $createIfNotExists=false): User
    {
        if (empty($userData['nik'])) {
            throw new \Exception('No NIK in user data');
        }

        if ($createIfNotExists){
            $user = User::firstOrCreate(
                ['nik' => $userData['nik']],
                [
                    'name' => $userData['name'] ?? $userData['nik'], 
                    'email' => $userData['email'],
                    'nik' => $userData['nik'],
                    'password' => Hash::make(Str::random(32)),
                ]
            );
        }else{
            $user = User::where('nik', $userData['nik'])
                ->orWhere('email', $userData['email'])
                ->firstOrFail();
        }

        Auth::login($user);
        $request->merge(['sso_user' => $user->toArray()]);
        $request->session()->put('sso_user', $user->toArray());

        return $user;
    }

    public function storeSsoUserInRequest(Request $request, array $decoded, string $token): void
    {
        $sessionData = [
            'sso_user'      => $decoded,
            'sso_token'     => $token,
            'lastPresence'  => now()->timestamp,
        ];

        $request->merge(['sso_user' => $decoded]);
        $request->session()->put($sessionData);
    }


    public function mapSsoSession(array $decoded, string $laravelSessionId): void
    {
        $ssoSid = $decoded['sid'] ?? $decoded['session_state'] ?? null;
        if (!$ssoSid) {
            return;
        }

        DB::table('sso_session_map')->updateOrInsert(
            ['sso_sid' => $ssoSid],
            ['laravel_session_id' => $laravelSessionId, 'updated_at' => now()]
        );
    }
    
    /**
     * Heartbeat check: verify token presence.
     * Params override session values if provided.
     */
    public function checkPresence(
        Request $request,
        ?string $token = null,
        ?int $lastPresence = null,
        ?array $ssoUser = null
    ): ?array {
        $session = $request->session();

        $sessionToken   = $token        ?? $session->get('sso_token');
        $lastPresence   = $lastPresence ?? $session->get('lastPresence');
        $ssoUser        = $ssoUser      ?? $session->get('sso_user');
        $invalidated    = $session->get('invalidated_token');

        // Prefer fresh tokens from proxy or bearer headers
        $proxyToken  = $request->header('X-Forwarded-Access-Token');
        $bearerToken = null;
        if ($authHeader = $request->header('Authorization')) {
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $bearerToken = $matches[1];
            }
        }

        $newToken = $proxyToken ?? $bearerToken ?? null;

        // Ignore if it's the same as last invalid token
        if ($newToken && $invalidated && $newToken === $invalidated) {
            $newToken = null;
        }

        // If a new token is found, and it's different → reauthenticate
        if ($newToken && $newToken !== $sessionToken) {
            $decoded = $this->verifyToken($newToken);
            if ($decoded) {
                $userData = $this->extractUserData($decoded);
                $this->loginUser($request, $userData);
                $this->storeSsoUserInRequest($request, $decoded, $newToken);
                $this->mapSsoSession($decoded, $request->session()->getId());
                return $decoded;
            } else {
                $this->invalidateToken($request, $newToken);
                return null;
            }
        }

        // No token at all
        if (!$sessionToken || !$lastPresence) {
            return null;
        }

        // Still within idle interval
        $interval = config('services.sso.idle_seconds');
        $elapsed  = now()->timestamp - $lastPresence;
        if ($elapsed < $interval) {
            return $ssoUser;
        }

        // Verify session token again
        if ($this->isTokenInvalidated($request, $sessionToken)) {
            $this->logout(false, $session);
            return null;
        }

        $decoded = $this->verifyToken($sessionToken);
        if (!$decoded) {
            $this->invalidateToken($request, $sessionToken);
            $this->logout(false, $session);
            return null;
        }

        // If decoded user changed → redo login
        if (($ssoUser['sub'] ?? null) !== ($decoded['sub'] ?? null)) {
            $userData = $this->extractUserData($decoded);
            $this->loginUser($request, $userData);
        }

        $this->storeSsoUserInRequest($request, $decoded, $sessionToken);

        return $decoded;
    }

    // ================================
    // INVALIDATION HANDLING
    // ================================
    public function invalidateToken(Request $request, string $token)
    {
        $session = $request->session();
        $session->put('invalidated_token', $token);
    }

    public function logout($redirect=true, $session=null){
        Auth::logout();
        if ($session){
            $session->flush();
            $session->invalidate();
            $session->regenerateToken();
        }else{
            Session::flush();
            Session::invalidate();
            Session::regenerateToken();
        }

        $logoutUrl = config('app.url') . '/oauth2/sign_out?rd=/';

        if ($redirect){
          return Redirect::away($logoutUrl);
        }else{
          return $logoutUrl;
        }
    }

    private function isTokenInvalidated(Request $request, string $token): bool
    {
        return $request->session()->get('invalidated_token') === $token;
    }
    
    /**
     * Verify token using public key (local validation).
     */
    public function verifyTokenPublicKey(string $token): ?array
    {
        try {
            $publicKey = config('services.sso.public_key');
            return (array) JWT::decode($token, new Key($publicKey, 'RS256'));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verify token by calling remote protected URL (userinfo endpoint).
     */
    public function verifyTokenUrl(string $token): ?array
    {
        $verifyUrl = config('services.sso.verify_url');
        $caFile    = config('services.sso.ca_file');
        $ip        = config('services.sso.ip');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verifyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$token}"
        ]);

        if ($caFile && file_exists($caFile)) {
            curl_setopt($ch, CURLOPT_CAINFO, $caFile);
        }

        if ($ip && !empty($ip)) {
            $urlParts = parse_url($verifyUrl);
            $host     = $urlParts['host'] ?? null;
            $scheme   = $urlParts['scheme'] ?? 'http';
            $port     = $urlParts['port'] ?? ($scheme === 'https' ? 443 : 80);

            if ($host) {
                curl_setopt($ch, CURLOPT_RESOLVE, ["$host:$port:$ip"]);
            }
        }

        $response   = curl_exec($ch);
        $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorMsg   = curl_error($ch);
        $errorNo    = curl_errno($ch);

        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $userinfo = json_decode($response, true);
            return $userinfo ?: true; // return parsed JSON or true if not JSON
        }

        // log errors if needed
        logger()->error("SSO verifyUrl failed", ['error' => $errorMsg, 'code' => $httpCode]);

        return null;
    }

    /**
     * Wrapper: Verify using both methods.
     * First public key, then remote URL.
     */
    public function verifyToken(string $token): ?array
    {
        $decoded = $this->verifyTokenPublicKey($token);
        if (!$decoded) {
            return null;
        }

        $remoteCheck = $this->verifyTokenUrl($token);
        if (!$remoteCheck) {
            return null;
        }

        $decoded = json_decode(json_encode($decoded), true);

        $issuer = config('services.sso.issuer');
        if (!$this->checkIssuer($decoded['iss'] ?? null, $issuer)) {
            return null;
        }

        $requiredRole = config('services.sso.role');
        $roles        = $decoded['realm_access']['roles'] ?? [];
        if (!$this->checkRole($requiredRole, $roles)) {
            return null;
        }

        // Merge decoded claims and remote userinfo if both valid
        return array_merge($decoded, is_array($remoteCheck) ? $remoteCheck : []);
    }

    private function checkRole(string $role, array $roles): bool
    {
        if ($role && !in_array($role, $roles)) {
            logger()->warning("SSO role check failed: missing {$role}");
            return false;
        }
        return true;
    }

    private function checkIssuer(?string $decoded, ?string $required = null): bool
    {
        if (!$decoded) {
            return false;
        }
        if ($required && $decoded !== $required) {
            logger()->warning("SSO issuer check failed. Expected {$required}, got {$decoded}");
            return false;
        }
        return true;
    }
}
