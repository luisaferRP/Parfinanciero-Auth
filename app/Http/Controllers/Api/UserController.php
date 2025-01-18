<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(): JsonResponse
    {
        $users = User::all();
        return response()->json(UserResource::collection($users));
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findUserById($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(new UserResource($user));
    }

    public function destroy(int $id): JsonResponse
    {
        $user = $this->userService->findUserById($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->userService->deleteUser($user);
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $this->userService->findUserById($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validatedData = $this->userService->validateUserData($request->all(), $id);
        $updatedUser = $this->userService->updateUser($user, $validatedData);

        return response()->json(new UserResource($updatedUser));
    }
}