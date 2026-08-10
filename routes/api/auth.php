<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutAllController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\SendEmailVerificationNotificationController;
use App\Http\Controllers\Api\Auth\SendPasswordResetLinkController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function (): void {
    Route::post('/register', RegisterController::class)
        ->name('auth.register');

    Route::post('/login', LoginController::class)
        ->name('auth.login');

    Route::delete('/token', LogoutController::class)
        ->middleware('auth:sanctum')
        ->name('auth.token.destroy');

    Route::delete('/tokens', LogoutAllController::class)
        ->middleware('auth:sanctum')
        ->name('auth.tokens.destroy');

    Route::post('/password/forgot', SendPasswordResetLinkController::class)
        ->middleware('throttle:5,1')
        ->name('auth.password.forgot');

    Route::post('/password/reset', ResetPasswordController::class)
        ->name('auth.password.reset');

    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('auth.verification.verify');

    Route::post('/email/verification-notification', SendEmailVerificationNotificationController::class)
        ->middleware(['auth:sanctum', 'throttle:6,1'])
        ->name('auth.verification.send');
});
