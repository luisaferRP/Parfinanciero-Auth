<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input)
    {
        Log::info('Datos de entrada: ', $input);
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'profile_photo_path' => ['nullable', 'string'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'current_team_id' => $input['current_team_id'] ?? null,
            'profile_photo_path' => $input['profile_photo_path'] ?? ''
            
        ]);

    }
}
