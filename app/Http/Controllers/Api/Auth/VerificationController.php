<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\Auth\EmailVerificationFailedException;
use App\Http\Controllers\Controller;
use App\Services\Auth\VerificationService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class VerificationController extends Controller
{
    public function __construct(
        protected VerificationService $verificationService,
    )
    {
    }

    /**
     * @throws EmailVerificationFailedException
     */
    public function verify(Request $request, $id, $hash)
    {
        $this->verificationService->verify($id, $hash);
        return response()->success('Email verified.', [
            'verified' => true,
        ]);
    }

    public function resendVerificationEmail(Request $request)
    {
        $resent = $this->verificationService->resendVerificationEmail($request->user());
        return response()->success($resent ? 'Verification email resent.' : 'Email already verified.', [
            'resent' => $resent,
        ], $resent ? HttpResponse::HTTP_ACCEPTED : HttpResponse::HTTP_CONFLICT);
    }
}
