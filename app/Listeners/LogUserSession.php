<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\JWTService;

class LogUserSession
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Generate a unique token for the session
        // $sessionToken = Str::uuid();

        // Generate a new JWT token
        $token = JWTService::generateToken($user);

        // Save the session to the user_sessions table
        DB::table('user_sessions')->insert([
            'user_id' => $user->id,
            'provider_id' => $user->auth_provider ?? 'local',
            'session_token' => $token,
            'expires_at' => now()->addHours(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
