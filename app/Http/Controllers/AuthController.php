<?php

namespace App\Http\Controllers;

use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Http\Resources\AuthResource;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, AuthService $authService)
    {
        $result = $authService->registerUser(RegisterDTO::fromRequest($request));
        return (new AuthResource($result))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request, AuthService $authService)
    {
        $result = $authService->loginUser(LoginDTO::fromRequest($request));
        return (new AuthResource($result))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function logout(Request $request, AuthService $authService)
    {
        $authService->logoutUser($request->user());
        return response()->noContent();
    }

    public function logoutAll(Request $request, AuthService $authService)
    {
        $authService->logoutUser($request->user(), true);
        return response()->noContent();
    }
}
