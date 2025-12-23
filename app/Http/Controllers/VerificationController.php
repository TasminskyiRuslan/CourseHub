<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Services\VerificationService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificationController extends Controller
{
    public function verify(Request $request, VerificationService $verificationService, $id, $hash)
    {
        $user = $verificationService->verify($id, $hash);

        return (new UserResource($user))
        ->additional(['verified' => true])
        ->response()
        ->setStatusCode(Response::HTTP_OK);

    }

    public function resendVerificationEmail(Request $request, VerificationService $verificationService)
    {
        $resend = $verificationService->resendVerificationEmail($request->user());
            return response()->json([
                'resent' => $resend,
            ], $resend ? Response::HTTP_ACCEPTED : Response::HTTP_CONFLICT);
    }
}
