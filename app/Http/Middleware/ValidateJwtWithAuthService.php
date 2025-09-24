<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValidateJwtWithAuthService
{
    public function handle(Request $request, Closure $next)
    {
        // Essayer de récupérer le token depuis l'en-tête Authorization ou directement depuis le cookie
        $token = $request->bearerToken();

        if (!$token && $request->hasCookie('jwt')) {
            $token = $request->cookie('jwt');
        }

        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        try {
            // Appeler le service d'authentification pour valider le token
            $authServiceUrl = env('AUTH_SERVICE_URL', 'http://localhost:8001');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get($authServiceUrl . '/api/validate-token');

            if ($response->successful()) {
                $userData = $response->json();

                // Stocker les informations utilisateur dans la requête
                $request->attributes->set('auth_user', $userData['user']);

                Log::info('JWT validation successful', [
                    'user_id' => $userData['user']['id'],
                    'user_email' => $userData['user']['email']
                ]);

                return $next($request);
            } else {
                Log::warning('JWT validation failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return response()->json(['message' => 'Invalid or expired token'], 401);
            }
        } catch (\Exception $e) {
            Log::error('Error validating JWT with auth service', [
                'error' => $e->getMessage(),
                'token_exists' => !empty($token)
            ]);

            return response()->json(['message' => 'Authentication service unavailable'], 503);
        }
    }
}
