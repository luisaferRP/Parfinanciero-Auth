<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\CreateNewUser;
use Kreait\Firebase\Auth as FirebaseAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth as LaravelAuth;


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
        // We receive the ID Token from the customer 
        $idTokenString = $request->get('idToken');

        if (empty($idTokenString)) {
            return response()->json(['error' => 'ID Token is required'], 400);
        }

        try {
            //We verify the ID Token received
            $verifiedIdToken = $this->auth->verifyIdToken($idTokenString);

            // Extract the user's UID from the token
            $uid = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');
            $name = $verifiedIdToken->claims()->get('name');
            $picture = $verifiedIdToken->claims()->get('picture');

            //We search if the user already exists in the database.
            $user = User::firstOrCreate(
                ['firebase_uid' => $uid], //search for the Firebase UID
                [
                    'email' => $email, 
                    'name' => $name,   
                    'profile_picture' => $picture,  
                ]
            );

            // Authenticate the user in Laravel
            LaravelAuth::login($user);

            // We return the response with the user and his active session.
            return response()->json([
                'success' => true,
                'user' => $user,
            ]);

        } catch (FailedToVerifyToken $e) {
            // In case the token cannot be verified
            return response()->json(['error' => 'Invalid ID token'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
}




