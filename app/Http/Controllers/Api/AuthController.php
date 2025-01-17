<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use App\Services\JWTService;
use Carbon\Carbon;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
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

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        // // Remove any previous session
        DB::table('user_sessions')->where('user_id', $user->id)->delete();

        // Generate a new JWT token
        $providerId = 'local';
        $token = JWTService::generateToken($user);

        // Insert the session into the database
        DB::table('user_sessions')->insert([
            'user_id' => $user->id,
            'provider_id' => $providerId,
            'session_token' => $token,
            'expires_at' => now()->addMinutes(config('jwt.ttl')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => new UserResource($user),
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
