<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="Mi API Documentada con Swagger",
 *     version="1.0.0",
 *     description="Esta es la documentación de la API generada por Swagger.",
 *     @OA\Contact(
 *         email="soporte@miapi.com"
 *     )
 * )
 */

 class UserController extends Controller
 {
     /**
      * List all users.
      *
      * @OA\Get(
      *     path="/users",
      *     summary="Get list of users",
      *     tags={"Users"},
      *     @OA\Response(
      *         response=200,
      *         description="List of users",
      *         @OA\JsonContent(
      *             type="array",
      *             @OA\Items(ref="#/components/schemas/User")
      *         )
      *     ),
      *     @OA\Response(
      *         response=500,
      *         description="Internal server error"
      *     )
      * )
      */
     public function index(): JsonResponse
     {
         $users = User::all();
         return response()->json($users);
     }

     /**
      * Get user by ID.
      *
      * @OA\Get(
      *     path="/users/{id}",
      *     summary="Get user by ID",
      *     tags={"Users"},
      *     @OA\Parameter(
      *         name="id",
      *         in="path",
      *         required=true,
      *         description="ID of the user to retrieve",
      *         @OA\Schema(type="integer")
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="User data",
      *         @OA\JsonContent(ref="#/components/schemas/User")
      *     ),
      *     @OA\Response(
      *         response=404,
      *         description="User not found"
      *     )
      * )
      */
     public function show(int $id): JsonResponse
     {
         $user = User::find($id);

         if (!$user) {
             return response()->json(['message' => 'User not found'], 404);
         }

         return response()->json($user);
     }

     /**
      * Delete user by ID.
      *
      * @OA\Delete(
      *     path="/users/{id}",
      *     summary="Delete user by ID",
      *     tags={"Users"},
      *     @OA\Parameter(
      *         name="id",
      *         in="path",
      *         required=true,
      *         description="ID of the user to delete",
      *         @OA\Schema(type="integer")
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="User deleted successfully"
      *     ),
      *     @OA\Response(
      *         response=404,
      *         description="User not found"
      *     )
      * )
      */
     public function destroy(int $id): JsonResponse
     {
         $user = User::find($id);

         if (!$user) {
             return response()->json(['message' => 'User not found'], 404);
         }

         $user->delete();
         return response()->json(['message' => 'User deleted successfully']);
     }

     /**
      * Edit user by ID.
      *
      * @OA\Put(
      *     path="/users/{id}",
      *     summary="Edit user by ID",
      *     tags={"Users"},
      *     @OA\Parameter(
      *         name="id",
      *         in="path",
      *         required=true,
      *         description="ID of the user to edit",
      *         @OA\Schema(type="integer")
      *     ),
      *     @OA\RequestBody(
      *         required=true,
      *         @OA\JsonContent(
      *             type="object",
      *             @OA\Property(property="name", type="string"),
      *             @OA\Property(property="email", type="string", format="email")
      *         )
      *     ),
      *     @OA\Response(
      *         response=200,
      *         description="User updated successfully",
      *         @OA\JsonContent(ref="#/components/schemas/User")
      *     ),
      *     @OA\Response(
      *         response=404,
      *         description="User not found"
      *     )
      * )
      */
     public function update(Request $request, int $id): JsonResponse
     {
         $user = User::find($id);

         if (!$user) {
             return response()->json(['message' => 'User not found'], 404);
         }

         $validatedData = $request->validate([
             'name' => 'string|max:255',
             'email' => 'email|unique:users,email,' . $id,
         ]);

         $user->update($validatedData);
         return response()->json($user);
     }
 }
