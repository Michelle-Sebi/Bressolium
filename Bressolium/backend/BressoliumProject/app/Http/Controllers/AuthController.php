<?php
/**
 * @module AuthController
 * @description Controlador para gestionar el acceso y registro de usuarios.
 * Delega la lógica de negocio en AuthService.
 */

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class AuthController extends Controller
{
    protected $authService;

    /**
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Procesa la petición de registro.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => $validator->errors()
            ], 422);
        }

        try {
            $data = $this->authService->register($request->all());
            return response()->json([
                'success' => true,
                'data' => [
                    'user'  => (new UserResource($data['user']))->toArray($request),
                    'token' => $data['token'],
                ],
                'error' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesa el inicio de sesión.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => $validator->errors()
            ], 422);
        }

        try {
            $data = $this->authService->login($request->email, $request->password);
            return response()->json([
                'success' => true,
                'data' => [
                    'user'  => (new UserResource($data['user']))->toArray($request),
                    'token' => $data['token'],
                ],
                'error' => null
            ], 200);
        } catch (Exception $e) {
            $status = ($e->getMessage() === 'Invalid credentials') ? 401 : 500;
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => $e->getMessage()
            ], $status);
        }
    }
}
