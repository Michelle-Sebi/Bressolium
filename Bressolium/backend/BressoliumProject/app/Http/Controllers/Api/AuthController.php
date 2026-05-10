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
