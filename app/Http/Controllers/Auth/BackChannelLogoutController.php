<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;


class BackChannelLogoutController
{
    public function handle(Request $request)
    {
        $logoutToken = $request->input('logout_token');

        if (!$logoutToken) {
            return response()->json(['error' => 'No logout_token'], 400);
        }

        try {
            $publicKey = config('services.sso.public_key');
            $decoded = (array) JWT::decode($logoutToken, new Key($publicKey, 'RS256'));

            // The 'sub' claim usually identifies the user
            $userIdentifier = isset($decoded['sub']) ? $decoded['sub'] : null;

            if ($userIdentifier) {
                // Remove session(s) for this user
                $user = User::where('nik', $userIdentifier)->first();
                if ($user) {
                    $sid = isset($decoded['sid']) ? $decoded['sid'] : $decoded['session_state'];
                    $laravelSessionId = DB::table('sso_session_map')
                        ->where('sso_sid', $sid)
                        ->value('laravel_session_id');
                
                    if ($laravelSessionId) {
                        $driver = Config::get('session.driver');
                
                        switch ($driver) {
                            case 'file':
                                $sessionFile = storage_path('framework/sessions/'.$laravelSessionId);
                                if (File::exists($sessionFile)) {
                                    File::delete($sessionFile);
                                }
                                break;
                
                            case 'database':
                                DB::table(Config::get('session.table', 'sessions'))
                                    ->where('id', $laravelSessionId)
                                    ->delete();
                                break;
                
                            case 'redis':
                            case 'memcached':
                            case 'dynamodb':
                                // Use session handler
                                $handler = Session::getHandler();
                                if (method_exists($handler, 'destroy')) {
                                    $handler->destroy($laravelSessionId);
                                }
                                break;
                
                            default:
                                // fallback
                                DB::table(Config::get('session.table', 'sessions'))
                                    ->where('id', $laravelSessionId)
                                    ->delete();
                                break;
                        }
                    }
                }
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid logout_token'], 401);
        }
    }
}
