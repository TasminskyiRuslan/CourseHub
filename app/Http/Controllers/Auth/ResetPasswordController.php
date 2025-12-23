<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\ResetPasswordDTO;
use App\Http\Controllers\ApiController;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\Auth\ResetPasswordService;

class ResetPasswordController extends ApiController
{
    public function sendResetLink(ForgotPasswordRequest $request, ResetPasswordService $service)
    {
        $service->sendResetLink($request->input('email'));

        return $this->successResponse([
            'email_sent' => true,
        ], 'Password reset link sent');
    }

    public function reset(ResetPasswordRequest $request, ResetPasswordService $service)
    {
        $service->reset(ResetPasswordDTO::fromRequest($request));

        return $this->successResponse([
            'password_reset' => true
        ], 'Password has been reset');
    }
}
