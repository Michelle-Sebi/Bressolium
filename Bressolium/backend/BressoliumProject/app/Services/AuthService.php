<?php

/**
 * @module AuthService
 *
 * @description Servicio para gestionar la lógica de negocio de autenticación.
 */

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Registra un nuevo usuario y devuelve el token.
     */
    public function register(array $data): array
    {
        $user = $this->userRepository->create($data);
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Valida credenciales y devuelve datos de usuario y token de sesión.
     *
     * @throws \Exception
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new \Exception('Invalid credentials');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
