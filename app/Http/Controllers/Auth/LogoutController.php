<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSession;

class LogoutController
{
    public function destroy(Request $request)
    {
        // Borra la sesión del usuario de la tabla 'user_sessions'
        UserSession::where('user_id', Auth::id())->delete();

        // Logout del usuario actual
        Auth::guard('web')->logout();

        // Invalida y regenera el token de sesión
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirige al usuario a la página principal (o cualquier otra)
        return redirect('/');
    }
}
