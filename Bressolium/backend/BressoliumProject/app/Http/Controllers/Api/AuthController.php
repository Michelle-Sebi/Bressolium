<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Support\ResponseBuilder;
use Exception;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected ResponseBuilder $rb,
    ) {}

    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Registra un nuevo usuario",
     *     description="Crea un usuario y devuelve un token Sanctum para empezar sesión inmediatamente.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="Bárbara"),
     *             @OA\Property(property="email", type="string", format="email", example="barbara@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario registrado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="string", example="01HFG..."),
     *                     @OA\Property(property="name", type="string", example="Bárbara"),
     *                     @OA\Property(property="email", type="string", example="barbara@example.com")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|abc123...")
     *             ),
     *             @OA\Property(property="error", type="string", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(response=422, description="Datos inválidos", @OA\JsonContent(ref="#/components/schemas/ApiError")),
     *     @OA\Response(response=500, description="Error del servidor", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function register(RegisterRequest $request)
    {
        try {
            $data = $this->authService->register($request->validated());

            return $this->rb->success([
                'user' => (new UserResource($data['user']))->toArray($request),
                'token' => $data['token'],
            ]);
        } catch (Exception $e) {
            return $this->rb->error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Inicia sesión y devuelve un token Sanctum",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="barbara@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login correcto",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="token", type="string")
     *             ),
     *             @OA\Property(property="error", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Credenciales inválidas", @OA\JsonContent(ref="#/components/schemas/ApiError")),
     *     @OA\Response(response=422, description="Datos inválidos", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function login(LoginRequest $request)
    {
        try {
            $data = $this->authService->login($request->email, $request->password);

            return $this->rb->success([
                'user' => (new UserResource($data['user']))->toArray($request),
                'token' => $data['token'],
            ]);
        } catch (Exception $e) {
            $status = ($e->getMessage() === 'Invalid credentials') ? 401 : 500;

            return $this->rb->error($e->getMessage(), $status);
        }
    }
}
