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
Route::prefix('auth')->name('auth.')->group(function () {

    // Register action
    Route::post('/register', RegisterController::class)->name('register');

    // Login action
    Route::post('/login', LoginController::class)->name('login');

    // Me action
    Route::get('/me', MeController::class)
        ->middleware('auth:sanctum')
        ->name('me');

    // Logout actions
    Route::post('/logout', LogoutController::class)
        ->middleware('auth:sanctum')
        ->name('logout');
    Route::delete('/tokens', LogoutAllController::class)
        ->middleware('auth:sanctum')
        ->name('tokens.destroy');

    /*
    |--------------------------------------------------------------------------
    | Password actions
    |--------------------------------------------------------------------------
    */
    Route::prefix('password')->name('password.')->group(function () {
        Route::post('/forgot', ForgotPasswordController::class)
            ->middleware('throttle:5,1')
            ->name('forgot');
        Route::post('/reset', ResetPasswordController::class)
            ->name('reset');
    });

    /*
    |--------------------------------------------------------------------------
    | Email verification actions
    |--------------------------------------------------------------------------
    */
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

    // Course actions
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

    // Grouped routes under a specific course
    Route::prefix('{course}')->scopeBindings()->group(function () {

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

        // Course image actions
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
    });
});
