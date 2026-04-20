<?php

use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutAllController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResendVerificationEmailController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\Course\CourseController;
use App\Http\Controllers\Api\Course\CourseImageController;
use App\Http\Controllers\Api\Course\PublishCourseController;
use App\Http\Controllers\Api\Course\UnpublishCourseController;
use App\Http\Controllers\Api\Lesson\LessonController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication actions
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    // Register action
    Route::post('/register', RegisterController::class)
        ->name('auth.register');

    // Login action
    Route::post('/login', LoginController::class)
        ->name('auth.login');

    // Me action
    Route::get('/me', MeController::class)
        ->middleware('auth:sanctum')
        ->name('auth.me');

    // Logout actions
    Route::delete('/logout', LogoutController::class)
        ->middleware('auth:sanctum')
        ->name('auth.logout');
    Route::delete('/logout/all', LogoutAllController::class)
        ->middleware('auth:sanctum')
        ->name('auth.logout.all');

    // Password actions
    Route::post('/password/forgot', ForgotPasswordController::class)
        ->middleware('throttle:5,1')
        ->name('auth.password.forgot');
    Route::post('/password/reset', ResetPasswordController::class)
        ->name('auth.password.reset');

    // Email verification actions
    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('auth.verification.verify');
    Route::post('/email/verification-notification', ResendVerificationEmailController::class)
        ->middleware(['auth:sanctum', 'throttle:6,1'])
        ->name('auth.verification.resend');
});

/*
|--------------------------------------------------------------------------
| Course & Lessons actions
|--------------------------------------------------------------------------
*/
Route::prefix('courses')->group(function () {
    // Get courses list action
    Route::get('/', [CourseController::class, 'index'])
        ->name('course.index');

    // Create course action
    Route::post('/', [CourseController::class, 'store'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('course.store');

    // Show course action
    Route::get('/{course}', [CourseController::class, 'show'])
        ->name('course.show');

    // Update course action
    Route::patch('/{course}', [CourseController::class, 'update'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('course.update');

    // Delete course action
    Route::delete('/{course}', [CourseController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('course.destroy');

    // Update course image action
    Route::put('/{course}/image', [CourseImageController::class, 'update'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('course.image.update');

    // Delete course image action
    Route::delete('{course}/image', [CourseImageController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('course.image.destroy');

    // Publish course actions
    Route::patch('{course}/publish', PublishCourseController::class)
        ->middleware(['auth:sanctum', 'verified'])
        ->name('course.publish');
    // Unpublish course action
    Route::patch('{course}/unpublish', UnpublishCourseController::class)
        ->middleware(['auth:sanctum', 'verified'])
        ->name('course.unpublish');

    // Lesson actions
    Route::prefix('lessons')->group(function () {
        Route::get('/', [LessonController::class, 'index'])
            ->name('lesson.index');
        Route::post('/', [LessonController::class, 'store'])
            ->middleware(['auth:sanctum', 'verified'])
            ->name('lesson.store');
        Route::get('/{lesson}', [LessonController::class, 'show'])
            ->name('lesson.show');
        Route::put('/{lesson}', [LessonController::class, 'update'])
            ->middleware(['auth:sanctum', 'verified'])
            ->name('lesson.update');
        Route::delete('/{lesson}', [LessonController::class, 'destroy'])
            ->middleware(['auth:sanctum', 'verified'])
            ->name('lesson.destroy');
    });
});
