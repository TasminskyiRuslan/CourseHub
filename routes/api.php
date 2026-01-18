<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LessonController; // <--- 1. Не забудьте імпортувати контролер
use App\Models\Course;
use App\Models\Lesson;
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
            Route::post('/', [CourseController::class, 'store'])
                ->middleware('can:create,' . Course::class);

            Route::put('/{course}', [CourseController::class, 'update'])
                ->middleware('can:update,course');

            Route::delete('/{course}', [CourseController::class, 'destroy'])
                ->middleware('can:delete,course');

            Route::post('/{course}/lessons', [LessonController::class, 'store'])
                ->middleware('can:create,' . Lesson::class . ',course');
        });

    });
});
