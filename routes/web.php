<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LogoutController;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    return view('auth.login');
});

Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/firebase-test', function () {
    $firebase = app('firebase');
    return response()->json([
        'message' => 'Firebase conectado correctamente'
    ]);
});

Route::post('google-login', [GoogleAuthController::class, 'loginWithGoogle'])->name('google.login');

