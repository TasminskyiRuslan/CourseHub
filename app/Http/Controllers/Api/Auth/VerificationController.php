<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Exceptions\Auth\EmailVerificationFailedException;
use App\Services\Auth\VerificationService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificationController extends Controller
{
    /**
     * @throws EmailVerificationFailedException
     */
    public function verify(Request $request, VerificationService $service, $id, $hash)
    {
        $service->verify($id, $hash);

        return response()->success('Email verified.', [
            'verified' => true,
        ]);
    }

    public function resendVerificationEmail(Request $request, VerificationService $service)
    {
        $resent = $service->resendVerificationEmail($request->user());

        return response()->success($resent ? 'Verification email resent.' : 'Email already verified.', [
            'resent' => $resent,
        ], $resent ? Response::HTTP_ACCEPTED : Response::HTTP_CONFLICT);
    }
}
