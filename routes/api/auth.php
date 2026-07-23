<?php

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
| Auth actions
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {

    // Register action
    Route::post('/register', RegisterController::class)
        ->name('auth.register');

    // Login action
    Route::post('/login', LoginController::class)
        ->name('auth.login');

    // Logout action
    Route::delete('/token', LogoutController::class)
        ->middleware('auth:sanctum')
        ->name('auth.token.destroy');

    // Logout all action
    Route::delete('/tokens', LogoutAllController::class)
        ->middleware('auth:sanctum')
        ->name('auth.tokens.destroy');

    // Send password reset email action
    Route::post('/password/forgot', SendPasswordResetLinkController::class)
        ->middleware('throttle:5,1')
        ->name('auth.password.forgot');

    // Reset password action
    Route::post('/password/reset', ResetPasswordController::class)
        ->name('auth.password.reset');

    // Verify email actions
    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('auth.verification.verify');

    // Resend verification email action
    Route::post('/email/verification-notification', SendEmailVerificationNotificationController::class)
        ->middleware(['auth:sanctum', 'throttle:6,1'])
        ->name('auth.verification.send');
});
