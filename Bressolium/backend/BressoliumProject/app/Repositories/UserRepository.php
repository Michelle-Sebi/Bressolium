<?php

/**
 * @module UserRepository
 *
 * @description Repositorio para la gestión de datos del modelo User.
 */

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    /**
     * Crea un nuevo usuario.
     */
    public function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Busca un usuario por su email.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
