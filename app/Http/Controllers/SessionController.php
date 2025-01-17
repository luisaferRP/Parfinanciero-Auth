<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SessionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/validate-session",
     *     summary="Validar la sesión de un usuario",
     *     description="Este endpoint valida que el token de sesión del usuario sea válido y no haya expirado.",
     *     operationId="validateSession",
     *     tags={"Validator"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sesión válida",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sesión válida."),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Juan Pérez")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Sesión inválida o expirada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sesión inválida o expirada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token inválido o corrupto",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El token es inválido o está corrupto.")
     *         )
     *     )
     * )
     */
    public function validateSession(Request $request)
    {
        // Validate the received data
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'token' => 'required|string',
        ]);

        $userId = $validated['user_id'];
        $token = $validated['token'];

        // Get the active session of the user
        $session = DB::table('user_sessions')
            ->where('user_id', $userId)
            ->where('session_token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$session) {
            return response()->json([
                'message' => 'Invalid or expired session.',
            ], 401);
        }

        // Validate the JWT
        try {
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'The token is invalid or corrupted.',
            ], 401);
        }

        return response()->json([
            'message' => 'Valid Session',
            'user' => [
                'id' => $userId,
            ],
        ]);
    }
}
