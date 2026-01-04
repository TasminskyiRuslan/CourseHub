<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\VerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Email verification (link from email)
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/
Route::prefix('auth')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::delete('/tokens', [AuthController::class, 'logoutAll']);


        Route::get('/me', function (Request $request) {
            return $request->user();
        });

        Route::post('/email/verification/resend', [VerificationController::class, 'resendVerificationEmail'])
            ->middleware('throttle:6,1')
            ->name('verification.resend');

        Route::post('/password/forgot', [ResetPasswordController::class, 'sendResetLink']);
        Route::post('/password/reset', [ResetPasswordController::class, 'reset']);
    });

/*
|--------------------------------------------------------------------------
| Verified
|--------------------------------------------------------------------------
*/
Route::prefix('auth')
    ->middleware(['auth:sanctum', 'verified'])
    ->group(function () {

    });
