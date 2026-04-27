<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Exception;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function register(RegisterRequest $request)
    {
        try {
            $data = $this->authService->register($request->validated());
            return response()->json([
                'success' => true,
                'data'    => [
                    'user'  => (new UserResource($data['user']))->toArray($request),
                    'token' => $data['token'],
                ],
                'error'   => null,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'data' => null, 'error' => $e->getMessage()], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $data = $this->authService->login($request->email, $request->password);
            return response()->json([
                'success' => true,
                'data'    => [
                    'user'  => (new UserResource($data['user']))->toArray($request),
                    'token' => $data['token'],
                ],
                'error'   => null,
            ], 200);
        } catch (Exception $e) {
            $status = ($e->getMessage() === 'Invalid credentials') ? 401 : 500;
            return response()->json(['success' => false, 'data' => null, 'error' => $e->getMessage()], $status);
        }
    }
}
