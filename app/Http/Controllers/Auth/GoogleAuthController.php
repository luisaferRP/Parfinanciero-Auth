<?php

namespace App\Http\Controllers\Auth;

use Kreait\Firebase\Auth as FirebaseAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth as LaravelAuth;

class GoogleAuthController extends Controller
{
    /**
     * @var FirebaseAuth
     */
    protected $auth;

    /**
     * GoogleAuthController constructor.
     *
     * @param FirebaseAuth $auth
     */
    public function __construct(FirebaseAuth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Login with Google ID Token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginWithGoogle(Request $request)
    {
        // Recibimos el ID Token desde el cliente (Google)
        $idTokenString = $request->get('idToken');

        if (empty($idTokenString)) {
            return response()->json(['error' => 'ID Token is required'], 400);
        }

        try {
            // Verificamos el ID Token recibido
            $verifiedIdToken = $this->auth->verifyIdToken($idTokenString);

            // Extraemos el UID del usuario desde el token
            $uid = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');
            $name = $verifiedIdToken->claims()->get('name');
            $picture = $verifiedIdToken->claims()->get('picture');

            // Buscamos si el usuario ya existe en la base de datos
            $user = User::firstOrCreate(
                ['firebase_uid' => $uid], // Buscamos por UID de Firebase
                [
                    'email' => $email,  // Guardamos el email
                    'name' => $name,    // Guardamos el nombre
                    'profile_picture' => $picture,  // Guardamos la imagen de perfil
                ]
            );

            // Autenticamos al usuario en Laravel
            LaravelAuth::login($user);

            // Devolvemos la respuesta con el usuario y su sesión activa
            return response()->json([
                'success' => true,
                'user' => $user,
            ]);

        } catch (FailedToVerifyToken $e) {
            // En caso de que el token no se pueda verificar
            return response()->json(['error' => 'Invalid ID token'], 400);
        } catch (\Exception $e) {
            // Manejo de otros errores generales
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
}
