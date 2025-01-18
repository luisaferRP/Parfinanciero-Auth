<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class UserService
{
    public function findUserById(int $id): ?User
    {
        return User::find($id);
    }

    public function validateUserData(array $data, int $id = null): array
    {
        $validator = Validator::make($data, [
            'name'      => 'string|max:255',
            'last_name' => 'string|max:255',
            'email'     => 'email|unique:users,email,' . $id,
            'id_rol'    => 'integer|exists:roles,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    public function updateUser(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }
}
