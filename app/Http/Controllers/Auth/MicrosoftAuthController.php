<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\CreateNewUser;
use Kreait\Firebase\Auth as FirebaseAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth as LaravelAuth;

// class MicrosoftAuthController extends Controller
// {
//     /**
//      * @var FirebaseAuth
//      */
//     protected $auth;

//     /**
//      * GoogleAuthController constructor.
//      *
//      * @param FirebaseAuth $auth
//      */
//     public function __construct()
//     {
//         // $projectId = env('FIREBASE_PROJECT_ID');

//         // if (!$projectId) {
//         //     throw new \Exception('FIREBASE_PROJECT_ID is not set in the .env file');
//         // }

//         // $this->auth = (new Factory)
//         //     ->withProjectId($projectId) // Pasamos el projectId aquí
//         //     ->createAuth();
        
//     }

//     // public function authenticateWithMicrosoft(Request $request)
//     // {
//     //         // Registrar los datos para verificar la entrada
//     // \Log::info('Datos recibidos:', $request->all());
//     //     // Obtener el token de ID y otros datos enviados desde el cliente
//     //     $idToken = $request->input('idToken'); // Token de Microsoft
//     //     $name = $request->input('name'); 
//     //     $last_name = $request->input('last_name');
//     //     $email = $request->input('email'); 
//     //     $password = $request->input('password');
//     //     $current_team_id = $request->input('current_team_id');
//     //     $photoURL = $request->input('profile_photo_path');


//     //     try {
//     //         //Comprobar si el usuario ya existe
//     //          $user = User::where('email', $email)->first();

             
//     //         // if (!$user) {
//     //         // //Si el usuario no existe, lo creamos
//     //         //     $user = $this->$createNewUser->create([
//     //         //         'name' => $name,
//     //         //         'email' => $email,
//     //         //         'password' => $password,
//     //         //         'profile_photo_path' => $photoURL,
//     //         //         'current_team_id' => $current_team_id,
//     //         //     ])
//     //         // }

//     //         // Devolvemos la respuesta con el usuario y su sesión activa
//     //         return response()->json([
//     //             'success' => true,
//     //             'user' => $user,
//     //         ]);
            

//     //     } catch (FailedToVerifyToken $e) {
//     //         // Si el token no es válido
//     //         return response()->json(['error' => 'Token no válido'], 401);
//     //     } catch (\Exception $e) {
//     //         \Log::error('Microsoft Auth Error: '.$e->getMessage());
//     //         return response()->json(['error' => 'An error occurred'], 500);
//     //     }
//     // }
// }


// namespace App\Http\Controllers\Auth;

// use Kreait\Firebase\Auth as FirebaseAuth;
// use Illuminate\Http\Request;
// use App\Http\Controllers\Controller;
// use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
// use App\Models\User;
// use App\Actions\Fortify\CreateNewUser;
// use Illuminate\Support\Facades\Auth as LaravelAuth;

// class MicrosoftAuthController extends Controller
// {
//     /**
//      * @var FirebaseAuth
//      */
//     protected $auth;

//     /**
//      * GoogleAuthController constructor.
//      *
//      * @param FirebaseAuth $auth
//      */
//     public function __construct(FirebaseAuth $auth)
//     {
//         $this->auth = $auth;
//     }

//     public function authenticateWithMicrosoft(Request $request)
//     {
//         // Obtener el token de Microsoft
//         $idToken = $request->get('idToken'); // El token de Microsoft enviado desde el cliente

//         try {
//             // Verificar el token usando Firebase
//             $verifiedIdToken = $this->auth->verifyIdToken($idToken);
//             return response()->json($verifiedIdToken->claims()->all());
            
//             // $uid = $verifiedIdToken->claims()->get('sub');
//             // $email = $verifiedIdToken->claims()->get('email');
//             // $name = $verifiedIdToken->claims()->get('name');
//             // $picture = $verifiedIdToken->claims()->get('picture');

//             // // Comprobar si el usuario ya existe
//             // $user = User::where('email', $email)->first();

//             // if (!$user) {
//             //     // Si el usuario no existe, lo creamos
//             //     $user = $this->$createNewUser->create([
//             //         'name' => $name,
//             //         'email' => $email,
//             //         'password' => $uid,
//             //         'profile_photo_path' => $picture,
//             //     ])

//              // Devolvemos la respuesta con el usuario y su sesión activa
//             //  return response()->json([
//             //     'success' => true,
//             //     'user' => $user,
//             // ]);

//         } catch (FailedToVerifyToken $e) {
//             // Si el token no es válido
//             return response()->json(['error' => 'Token no válido'], 401);
//         }catch (\Exception $e) {
//             \Log::error('Microsoft Auth Error: '.$e->getMessage());
//             return response()->json(['error' => 'An error occurred'], 500);
//         }
//     }
// }


class MicrosoftAuthController extends Controller
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
    public function authenticateWithMicrosoft(Request $request)
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




