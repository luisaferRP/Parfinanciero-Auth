<?php
namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
 *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Sesión válida",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Valid Session"),
 *             @OA\Property(property="user", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Juan"),
 *                 @OA\Property(property="last_name", type="string", example="Pérez"),
 *                 @OA\Property(property="email", type="string", example="juan@example.com"),
 *                 @OA\Property(property="id_rol", type="integer", example=1)
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
        // Validar el token recibido
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $token = $validated['token'];

        // Obtener la sesión activa del usuario
        $session = DB::table('user_sessions')
            ->join('users', 'users.id', '=', 'user_sessions.user_id')
            ->where('session_token', $token)
            ->where('expires_at', '>', now())
            ->select('users.*', 'user_sessions.*')
            ->first();

        if (! $session) {
            return response()->json([
                'message' => 'Invalid or expired session.',
            ], 401);
        }

        // Validar el JWT
        try {
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'The token is invalid or corrupted.',
            ], 400);
        }

        return response()->json([
            'message' => 'Valid Session',
            'user'    => [
                'id'        => $session->user_id,
                'name'      => $session->name,
                'last_name' => $session->last_name,
                'email'     => $session->email,
                'id_rol'    => $session->role_id,
            ],
        ], 200);
    }

}
