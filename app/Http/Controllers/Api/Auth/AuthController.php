<?php

namespace App\Http\Controllers\Api\Auth;

use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Exceptions\Auth\EmailVerificationFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AuthController extends Controller
{

    public function __construct(
        protected AuthService $authService,
    )
    {
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register(RegisterDTO::fromRequest($request));
        return response()->success(
            'User registered successfully.',
            new AuthResource($result),
            HttpResponse::HTTP_CREATED
        );
    }

    /**
     * @throws EmailVerificationFailedException
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login(LoginDTO::fromRequest($request));
        return response()->success('User logged in.',
            new AuthResource($result)
        );
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return response()->noContent();
    }

    public function logoutAll(Request $request)
    {
        $this->authService->logout($request->user(), true);
        return response()->noContent();
    }
}
