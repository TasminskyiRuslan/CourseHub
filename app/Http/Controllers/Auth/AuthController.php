<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Http\Controllers\ApiController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends ApiController
{
    public function register(RegisterRequest $request, AuthService $service)
    {
        $result = $service->register(RegisterDTO::fromRequest($request));
        return $this->successResponse([
            'user' => new UserResource($result['user']),
            'auth_token' => $result['auth_token']
        ],
            'User registered successfully.', Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request, AuthService $service)
    {
        $result = $service->login(LoginDTO::fromRequest($request));
        return $this->successResponse([
            'user' => new UserResource($result['user']),
            'auth_token' => $result['auth_token']
        ],
            'User logged in.');
    }

    public function logout(Request $request, AuthService $service)
    {
        $service->logout($request->user());
        return response()->noContent();
    }

    public function logoutAll(Request $request, AuthService $service)
    {
        $service->logout($request->user(), true);
        return response()->noContent();
    }
}
