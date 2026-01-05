<?php

namespace App\Http\Controllers\Api\Auth;

use App\DTO\Auth\ResetPasswordDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Services\Auth\ResetPasswordService;

class ResetPasswordController extends Controller
{
    public function __construct(
        protected ResetPasswordService $resetPasswordService,
    )
    {
    }

    public function sendResetLink(ForgotPasswordRequest $request)
    {
        $this->resetPasswordService->sendResetLink($request->input('email'));
        return response()->success('Password reset link sent', [
            'email_sent' => true,
        ]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $this->resetPasswordService->reset(ResetPasswordDTO::fromRequest($request));
        return response()->success('Password has been reset', [
            'password_reset' => true
        ]);
    }
}
