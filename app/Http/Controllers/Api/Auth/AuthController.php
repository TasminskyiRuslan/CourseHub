<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, AuthService $service)
    {
        $result = $service->register(RegisterDTO::fromRequest($request));
        return response()->success('User registered successfully.', [
            'user' => new UserResource($result['user']),
            'auth_token' => $result['auth_token']
        ], Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request, AuthService $service)
    {
        $result = $service->login(LoginDTO::fromRequest($request));
        return response()->success('User logged in.',
            [
                'user' => new UserResource($result['user']),
                'auth_token' => $result['auth_token']
            ]);
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
