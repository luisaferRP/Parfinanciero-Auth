<?php

namespace App\Services;

use App\Models\UserSession;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class JWTService
{
    /**
     * Generate and store JWT for a user session.
     *
     * @param $user
     * @param string $providerId
     * @return string
     */
    public static function generateAndStoreToken($user, string $providerId): string
    {
        // Generar el token JWT
        $token = JWTAuth::fromUser($user);

        // Calcular la fecha de expiración del token (opcional, ajusta según tu lógica)
        $expiresAt = Carbon::now()->addHours(2); // Ejemplo: 2 horas de validez

        // Guardar el token en la tabla `user_sessions`
        UserSession::create([
            'user_id' => $user->id,
            'provider_id' => $providerId,
            'session_token' => $token,
            'expires_at' => $expiresAt,
        ]);

        return $token;
    }
}

