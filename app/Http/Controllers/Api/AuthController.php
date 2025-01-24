<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserSession;
use App\Services\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    /**
     * Retrieve a JWT for a user by their email.
     *
     * @OA\Post(
     *     path="/get-bearer-token",
     *     summary="Obtiene el JWT para un usuario registrado",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="JWT obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="jwt", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario o sesión no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El usuario no existe o no tiene una sesión activa.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error interno del servidor.")
     *         )
     *     )
     * )
     */
    public function getBearerToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $email = $request->input('email');

            // Verificar si el usuario existe
            $user = User::where('email', $email)->first();

            if (! $user) {
                return response()->json([
                    'message' => 'El usuario no existe o no tiene una sesión activa.',
                ], 404);
            }

            // Verificar si el usuario tiene una sesión activa en la tabla user_sessions
            $session = DB::table('user_sessions')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc') // Si hay múltiples sesiones, obtener la más reciente
                ->first();

            if (! $session || ! $session->session_token) {
                return response()->json([
                    'message' => 'El usuario no tiene una sesión activa o no se encontró un JWT.',
                ], 404);
            }

            return response()->json([
                'jwt' => $session->session_token,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en getBearerToken:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error interno del servidor.',
            ], 500);
        }
    }

    /**
     * Check if a user is already registered in the database.
     *
     * @OA\Post(
     *     path="/check-user",
     *     summary="Verifica si un email ya está registrado",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Respuesta exitosa",
     *         @OA\JsonContent(
     *             @OA\Property(property="exists", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solicitud inválida",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El campo email es requerido.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error interno del servidor.")
     *         )
     *     )
     * )
     */
    public function checkUser(Request $request)
    {
        $email = $request->input('email');

        \Log::info('Email recibido:', ['email' => $email]);

        // Check if the user exists
        $exists = User::where('email', $email)->exists();

        \Log::info('Usuario existe:', ['exists' => $exists]);

        return response()->json(['exists' => $exists]);
    }

    /**
     * Handle user login and create a session.
     *
     * @param  Request  $request
     * @return JsonResponse
     */

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="User login and generates a JWT token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="user", type="object", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        // // Remove any previous session
        DB::table('user_sessions')->where('user_id', $user->id)->delete();

        // Generate a new JWT token
        $providerId = $request->input('providerId') ! null ? $request->input('providerId') : 'local';
        $token      = JWTService::generateToken($user);

        // Insert the session into the database
        DB::table('user_sessions')->insert([
            'user_id'       => $user->id,
            'provider_id'   => $providerId,
            'session_token' => $token,
            'expires_at'    => now()->addMinutes(config('jwt.ttl')),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => new UserResource($user),
        ]);
    }

    /**
     * Handle user logout and invalidate session.
     *
     * @param  Request  $request
     * @return JsonResponse
     */

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();

        $session = UserSession::where('user_id', $user->id)->first();
        if ($session) {
            $session->update(['session_token' => null]);
        }

        return response()->json(['message' => 'Logout successful']);
    }
}
