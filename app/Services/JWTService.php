<?php

namespace App\Services;

use Firebase\JWT\JWT;


class JWTService
{
    /**
     * Generate and store JWT for a user session.
     *
     * @param $user
     * @param string $providerId
     * @return string
     */
    public static function generateToken($user): string
    {
        $payload = [
            'iss' => config('app.url'), // Emisor
            'sub' => $user->id,         // ID del usuario
            'iat' => time(),            // Emitido en
            'exp' => time() + (config('jwt.ttl') * 60), // Expiración
        ];

        return JWT::encode($payload, config('jwt.secret'), 'HS256');
    }
}

