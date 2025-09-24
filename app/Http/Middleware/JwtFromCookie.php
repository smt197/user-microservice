<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JwtFromCookie
{
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si le token JWT est présent dans le cookie
        if (!$request->bearerToken() && $request->hasCookie('jwt')) {
            $token = $request->cookie('jwt');

            // Ajouter le token dans l'en-tête Authorization
            $request->headers->set('Authorization', 'Bearer ' . $token);

            \Log::info('JWT token extracted from cookie', [
                'has_token' => !empty($token),
                'token_length' => strlen($token ?? '')
            ]);
        }

        return $next($request);
    }
}