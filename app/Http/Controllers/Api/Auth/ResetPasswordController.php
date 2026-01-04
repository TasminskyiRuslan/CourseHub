<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\DTO\Auth\ResetPasswordDTO;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Services\Auth\ResetPasswordService;

class ResetPasswordController extends Controller
{
    public function sendResetLink(ForgotPasswordRequest $request, ResetPasswordService $service)
    {
        $service->sendResetLink($request->input('email'));

        return response()->success('Password reset link sent', [
            'email_sent' => true,
        ]);
    }

    public function reset(ResetPasswordRequest $request, ResetPasswordService $service)
    {
        $service->reset(ResetPasswordDTO::fromRequest($request));

        return response()->success('Password has been reset', [
            'password_reset' => true
        ]);
    }
}
