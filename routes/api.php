<?php

use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutAllController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResendVerificationController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\Courses\CourseController;
use App\Http\Controllers\Api\Courses\CourseImageController;
use App\Http\Controllers\Api\Courses\PublishCourseController;
use App\Http\Controllers\Api\Courses\UnpublishCourseController;
use App\Http\Controllers\Api\Lessons\LessonController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication actions
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    // Register action
    Route::post('/register', RegisterController::class)->name('auth.register');

    // Login action
    Route::post('/login', LoginController::class)->name('auth.login');

    // Me action
    Route::get('/me', MeController::class)
        ->middleware('auth:sanctum')
        ->name('auth.me');

    // Logout actions
    Route::post('/logout', LogoutController::class)
        ->middleware('auth:sanctum')
        ->name('auth.logout');
    Route::delete('/tokens', LogoutAllController::class)
        ->middleware('auth:sanctum')
        ->name('auth.tokens.destroy');


    // Password actions
    Route::post('/password/forgot', ForgotPasswordController::class)
        ->middleware('throttle:5,1')
        ->name('password.forgot');
    Route::post('/password/reset', ResetPasswordController::class)
        ->name('password.reset');

    // Email verification actions
    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', ResendVerificationController::class)
        ->middleware(['auth:sanctum', 'throttle:6,1'])
        ->name('verification.resend');
});

/*
|--------------------------------------------------------------------------
| Courses & Lessons actions
|--------------------------------------------------------------------------
*/
Route::prefix('courses')->name('courses.')->group(function () {
    // CourseDataSchema actions
    Route::get('/', [CourseController::class, 'index'])->name('index');
    Route::post('/', [CourseController::class, 'store'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('store');
    Route::get('/{course}', [CourseController::class, 'show'])->name('show');
    Route::put('/{course}', [CourseController::class, 'update'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('update');
    Route::delete('/{course}', [CourseController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('destroy');

    Route::prefix('{course}')->scopeBindings()->group(function () {
        // CourseDataSchema image actions
        Route::prefix('image')->name('image.')->group(function () {
            Route::patch('/', [CourseImageController::class, 'update'])
                ->middleware(['auth:sanctum', 'verified'])
                ->name('update');
            Route::delete('/', [CourseImageController::class, 'destroy'])
                ->middleware(['auth:sanctum', 'verified'])
                ->name('destroy');
        });

        // Publish/Unpublish actions
        Route::patch('/publish', PublishCourseController::class)
            ->middleware(['auth:sanctum', 'verified'])
            ->name('publish');
        Route::patch('/unpublish', UnpublishCourseController::class)
            ->middleware(['auth:sanctum', 'verified'])
            ->name('unpublish');

        // Lesson actions
        Route::prefix('lessons')->name('lessons.')->group(function () {
            Route::get('/', [LessonController::class, 'index'])->name('index');
            Route::post('/', [LessonController::class, 'store'])
                ->middleware(['auth:sanctum', 'verified'])
                ->name('store');
            Route::get('/{lesson}', [LessonController::class, 'show'])->name('show');
            Route::put('/{lesson}', [LessonController::class, 'update'])
                ->middleware(['auth:sanctum', 'verified'])
                ->name('update');
            Route::delete('/{lesson}', [LessonController::class, 'destroy'])
                ->middleware(['auth:sanctum', 'verified'])
                ->name('destroy');
        });
    });
});
