<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LessonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/password/forgot', [ResetPasswordController::class, 'sendResetLink']);
    Route::post('/password/reset', [ResetPasswordController::class, 'reset']);
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{course}', [CourseController::class, 'show']);
Route::get('/courses/{course}/lessons', [LessonController::class, 'index']);
Route::get('/courses/{course}/lessons/{lesson}', [LessonController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::get('/me', fn (Request $request) => $request->user());
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::delete('/tokens', [AuthController::class, 'logoutAll']);
        Route::post('/email/verification/resend', [VerificationController::class, 'resendVerificationEmail'])
            ->middleware('throttle:6,1')
            ->name('verification.resend');
    });

    /*
    |--------------------------------------------------------------------------
    | Verified & Authorized Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('verified')->group(function () {
        Route::prefix('courses')->group(function () {
            Route::post('/', [CourseController::class, 'store']);
            Route::patch('/{course}', [CourseController::class, 'update']);
            Route::put('/{course}', [CourseController::class, 'update']);
            Route::delete('/{course}', [CourseController::class, 'destroy']);
            Route::post('/{course}/lessons', [LessonController::class, 'store']);
        });
    });
});
