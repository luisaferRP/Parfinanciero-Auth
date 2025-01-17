<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use App\Services\JWTService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handle user login and create a session.
     *
     * @param  Request  $request
     * @return JsonResponse
     */

    /**
     * @OA\Post(
     *     path="/api/v1/login",
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

        // Check for existing session and invalidate it
        $existingSession = UserSession::where('user_id', $user->id)->first();
        if ($existingSession) {
            $existingSession->update(['session_token' => null]);
        }

        // Generate a new JWT
        $token = JWTService::generateToken($user);

        // Save new session
        UserSession::updateOrCreate(
            ['user_id' => $user->id],
            [
                'provider_id'   => 'local',
                'session_token' => $token,
                'expires_at'    => Carbon::now()->addMinutes(config('jwt.ttl')),
            ]
        );

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user,
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
