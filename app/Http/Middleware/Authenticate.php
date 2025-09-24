<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        return null;
    }

    public function handle($request, Closure $next, ...$guards)
    {
        // 1. Essayer de récupérer le token depuis plusieurs sources
        $token = $this->extractToken($request);
        
        \Log::info('Token extraction', [
            'cookie_jwt' => $request->cookie('jwt') ? 'present' : 'absent',
            'header_auth' => $request->header('Authorization') ? 'present' : 'absent',
            'bearer_token' => $request->bearerToken() ? 'present' : 'absent',
            'all_cookies' => array_keys($request->cookies->all()),
            'token_found' => !empty($token)
        ]);
        
        if (!$token) {
            return response()->json([
                'message' => 'Token missing',
                'debug' => [
                    'cookies' => array_keys($request->cookies->all()),
                    'headers' => $request->headers->all()
                ]
            ], 401);
        }

        $authServiceUrl = env('AUTH_SERVICE_URL', 'http://authentificationservice.test:8080');

        try {
            // Valider le token auprès du service d'authentification
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(5)
                ->get($authServiceUrl . '/api/validate-token');

            \Log::info('Auth service response', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            if ($response->successful()) {
                $userData = $response->json('user');
                
                if (isset($userData['id'], $userData['name'], $userData['email'])) {
                    // Créer un objet User temporaire
                    $user = new \App\Models\User();
                    $user->forceFill($userData);
                    $user->id = $userData['id'];
                    
                    // Définir l'utilisateur authentifié
                    Auth::setUser($user);
                    
                    // Stocker le token pour une utilisation ultérieure
                    $request->attributes->set('jwt_token', $token);
                    $request->merge(['auth_user' => $user]);

                    return $next($request);
                }
            }
            
            \Log::error('Token validation failed', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Auth validation exception', [
                'message' => $e->getMessage(),
                'url' => $authServiceUrl . '/api/validate-token'
            ]);
        }
        
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    /**
     * Extraire le token depuis différentes sources
     */
    private function extractToken(Request $request): ?string
    {
        // 1. Essayer le cookie JWT
        if ($token = $request->cookie('jwt')) {
            \Log::info('Token found in cookie');
            return $token;
        }

        // 2. Essayer le header Authorization Bearer
        if ($token = $request->bearerToken()) {
            \Log::info('Token found in Authorization header');
            return $token;
        }

        // 3. Essayer le header Authorization simple
        if ($auth = $request->header('Authorization')) {
            if (strpos($auth, 'Bearer ') === 0) {
                return substr($auth, 7);
            }
            return $auth;
        }

        // 4. Essayer un paramètre de requête (pour debug uniquement)
        if ($token = $request->query('token')) {
            \Log::warning('Token found in query parameter - not recommended for production');
            return $token;
        }

        return null;
    }
}