<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\Auth\MicrosoftAuthController;

// use  App\Actions\Fortify\CreateNewUser;
use  App\Http\Controllers\Auth\RegisterAuthController;


Route::prefix('v1')->group(function () {
    // Routes related to users
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::put('/users/{id}', [UserController::class, 'update']);

    // Authentication route
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/check-user', [AuthController::class, 'checkUser'])->middleware('throttle:10,1');
    Route::post('/get-bearer-token', [AuthController::class, 'getBearerToken'])->middleware('throttle:10,1');

    // Session route
    Route::post('/validate-session', [SessionController::class, 'validateSession']);
});

// Protected route for the authenticated user
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Generation of Swagger documentation
Route::get('/api-docs.json', function () {
    return response()->json(\L5Swagger\Generator::generateDocs());
});

Route::post('microsoft-login', [MicrosoftAuthController::class, 'authenticateWithMicrosoft']);

Route::post('/register', [RegisterAuthController::class, 'create']);