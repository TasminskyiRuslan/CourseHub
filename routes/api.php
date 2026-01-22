<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CourseImageController;
use App\Http\Controllers\Api\CoursePublishController;
use App\Http\Controllers\Api\LessonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::prefix('/auth')->group(function () {
    Route::middleware('guest:sanctum')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/password/forgot', [ResetPasswordController::class, 'sendResetLink']);
        Route::post('/password/reset', [ResetPasswordController::class, 'reset']);
    });

    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

Route::prefix('/courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
    Route::get('/{course}', [CourseController::class, 'show']);

    Route::prefix('/{course}/lessons')->group(function () {
        Route::get('/', [LessonController::class, 'index']);
        Route::get('/{lesson}', [LessonController::class, 'show'])->scopeBindings();
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::get('/me', fn(Request $request) => $request->user());
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::delete('/tokens', [AuthController::class, 'logoutAll']);

        Route::post('/email/verification/resend', [VerificationController::class, 'resendVerificationEmail'])
            ->middleware('throttle:6,1')
            ->name('verification.resend');
    });

    Route::middleware('verified')->prefix('courses')->group(function () {
        Route::post('/', [CourseController::class, 'store']);
        Route::prefix('/{course}')->group(function () {
            Route::put('/', [CourseController::class, 'update']);
            Route::delete('/', [CourseController::class, 'destroy']);

            Route::post('/image', [CourseImageController::class, 'store']);
            Route::delete('/image', [CourseImageController::class, 'destroy']);

            Route::patch('/publish', [CoursePublishController::class, 'publish']);
            Route::patch('/unpublish', [CoursePublishController::class, 'unpublish']);

            Route::prefix('/lessons')->group(function () {
                Route::post('/', [LessonController::class, 'store']);
                Route::put('/{lesson}', [LessonController::class, 'update'])->scopeBindings();
                Route::delete('/{lesson}', [LessonController::class, 'destroy'])->scopeBindings();
            });
        });

    });
});
