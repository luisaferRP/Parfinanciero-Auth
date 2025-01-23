<?php

namespace App\Actions;

use Illuminate\Http\Request;

class RedirectIfAuthenticated
{
    public function __invoke(Request $request)
    {
        // Generates the JWT of the authenticated user
        $jwt = auth()->user()->createToken('auth_token')->plainTextToken;

        // Redirects to the dashboard with the JWT as a query parameter
        return redirect()->route('dashboard', ['jwt' => $jwt]);
    }
}
