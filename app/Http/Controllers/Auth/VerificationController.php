<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\ApiController;
use App\Http\Resources\UserResource;
use App\Services\Auth\VerificationService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificationController extends ApiController
{
    public function verify(Request $request, VerificationService $service, $id, $hash)
    {
        $service->verify($id, $hash);

        return $this->successResponse([
            'verified' => true,
        ], 'Email verified.');
    }

    public function resendVerificationEmail(Request $request, VerificationService $service)
    {
        $resent = $service->resendVerificationEmail($request->user());

        return $this->successResponse([
            'resent' => $resent,
        ], $resent ? 'Verification email resent.' : 'Email already verified.',
            $resent ? Response::HTTP_ACCEPTED : Response::HTTP_CONFLICT);
    }
}
