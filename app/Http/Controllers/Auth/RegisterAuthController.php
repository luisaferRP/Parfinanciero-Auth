<?php

namespace  App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class RegisterAuthController extends Controller
{

    public function create(Request $request)
    {
            // Log para ver los datos recibidos
        Log::info('Datos de entrada al registro:', $request->all());

        // Valida los datos del request
        Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'profile_photo_path' => ['nullable', 'string'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],

        ])->validate();
    
        $user = User::create([
            'name' => $request->input('name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'current_team_id' => $request->input['current_team_id'] ?? null,
            'profile_photo_path' => $request->input['profile_photo_path'] ?? '',
            'role_id' => 1,
        ]);
    
        return response()->json(['message' => 'Usuario registrado con éxito!', 'user' => $user]);
    }
    
}
