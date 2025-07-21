<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiration
{
    public function handle($request, Closure $next)
    {
        if ($request->bearerToken()) {
            $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());

            // Ganti $token->isValid() dengan:
            if ($token && $token->expired_at && now()->gt($token->expired_at)) {
                return response()->json(['message' => 'Token expired'], 401);
            }
        }

        return $next($request);
    }
}
