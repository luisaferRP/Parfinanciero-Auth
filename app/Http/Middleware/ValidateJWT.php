<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSession;
use App\Services\JWTService;

class ValidateJWT
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token || !JWTService::isValid($token)) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        $payload = JWTService::decode($token);
        $userId = $payload['sub'] ?? null;

        $session = UserSession::where('user_id', $userId)
            ->where('session_token', $token)
            ->first();

        if (!$session) {
            return response()->json(['message' => 'Invalid session'], 401);
        }

        // Check token expiry and renew if necessary
        if (Carbon::parse($session->expires_at)->isPast()) {
            return response()->json(['message' => 'Token expired'], 401);
        }

        if (Carbon::parse($session->expires_at)->diffInMinutes(now()) <= 10) {
            $newToken = JWTService::generateToken(Auth::user());
            $session->update(['session_token' => $newToken, 'expires_at' => Carbon::now()->addMinutes(config('jwt.ttl'))]);
            $request->headers->set('Authorization', 'Bearer ' . $newToken);
        }

        return $next($request);
    }
}

