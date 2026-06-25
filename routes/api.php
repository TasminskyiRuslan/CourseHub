<?php

use App\Http\Controllers\Api\Account\AccountController;
use App\Http\Controllers\Api\Account\AccountAvatarController;
use App\Http\Controllers\Api\Account\SendPasswordResetEmailController;
use App\Http\Controllers\Api\Account\LoginController;
use App\Http\Controllers\Api\Account\LogoutAllController;
use App\Http\Controllers\Api\Account\LogoutController;
use App\Http\Controllers\Api\Account\RegisterController;
use App\Http\Controllers\Api\Account\ResendVerificationEmailController;
use App\Http\Controllers\Api\Account\ResetPasswordController;
use App\Http\Controllers\Api\Account\VerifyEmailController;
use App\Http\Controllers\Api\Course\BanCourseController;
use App\Http\Controllers\Api\Course\CourseController;
use App\Http\Controllers\Api\Course\CourseImageController;
use App\Http\Controllers\Api\Course\PublishCourseController;
use App\Http\Controllers\Api\Course\UnpublishCourseController;
use App\Http\Controllers\Api\Lesson\LessonController;
use App\Http\Controllers\Api\User\BanUserController;
use App\Http\Controllers\Api\User\UnbanUserController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\User\UserRoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Account actions
|--------------------------------------------------------------------------
*/
Route::prefix('account')->group(function () {
    // Register action
    Route::post('/register', RegisterController::class)
        ->name('account.register');

    // Login action
    Route::post('/login', LoginController::class)
        ->name('account.login');

    // Show account action
    Route::get('/', [AccountController::class, 'show'])
        ->middleware('auth:sanctum')
        ->name('account.show');

    // Update account action
    Route::patch('/', [AccountController::class, 'update'])
        ->middleware(['auth:sanctum', 'restrict.banned.user'])
        ->name('account.update');

    // Update account avatar action
    Route::put('/avatar', [AccountAvatarController::class, 'update'])
        ->middleware(['auth:sanctum', 'restrict.banned.user'])
        ->name('account.avatar.update');

    // Delete account avatar action
    Route::delete('/avatar', [AccountAvatarController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'restrict.banned.user'])
        ->name('account.avatar.destroy');

    // Logout action
    Route::delete('/token', LogoutController::class)
        ->middleware('auth:sanctum')
        ->name('account.token.destroy');

    // Logout all action
    Route::delete('/tokens', LogoutAllController::class)
        ->middleware('auth:sanctum')
        ->name('account.tokens.destroy');

    // Send password reset email action
    Route::post('/password/forgot', SendPasswordResetEmailController::class)
        ->middleware('throttle:5,1')
        ->name('account.password.forgot');

    // Reset password action
    Route::post('/password/reset', ResetPasswordController::class)
        ->name('account.password.reset');

    // Verify email actions
    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('account.verification.verify');

    // Resend verification email action
    Route::post('/email/verification-notification', ResendVerificationEmailController::class)
        ->middleware(['auth:sanctum', 'throttle:6,1'])
        ->name('account.verification.resend');
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
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('course.store');

    // Show course action
    Route::get('/{course}', [CourseController::class, 'show'])
        ->name('course.show');

    // Update course action
    Route::patch('/{course}', [CourseController::class, 'update'])
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('course.update');

    // Delete course action
    Route::delete('/{course}', [CourseController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('course.destroy');

    // Update course image action
    Route::put('/{course}/image', [CourseImageController::class, 'update'])
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('course.image.update');

    // Delete course image action
    Route::delete('/{course}/image', [CourseImageController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('course.image.destroy');

    // Publish course actions
    Route::patch('/{course}/publish', PublishCourseController::class)
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('course.publish');

    // Unpublish course action
    Route::patch('/{course}/unpublish', UnpublishCourseController::class)
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('course.unpublish');

    // Ban user action
    Route::patch('/{course}/ban', BanCourseController::class)
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('course.ban');

    Route::prefix('/{course}/lessons')->group(function () {
        // Get course lessons list action
        Route::get('/', [LessonController::class, 'index'])
            ->name('course.lesson.index');

        // Create lesson action
        Route::post('/', [LessonController::class, 'store'])
            ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
            ->name('course.lesson.store');

        // Show lesson action
        Route::get('/{lesson}', [LessonController::class, 'show'])
            ->name('course.lesson.show');

        // Update lesson action
        Route::patch('/{lesson}', [LessonController::class, 'update'])
            ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
            ->name('course.lesson.update');

        // Delete lesson action
        Route::delete('/{lesson}', [LessonController::class, 'destroy'])
            ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
            ->name('course.lesson.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| User actions
|--------------------------------------------------------------------------
*/
Route::prefix('users')->group(function () {
    // Get users list action
    Route::get('/', [UserController::class, 'index'])
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('user.index');

    // Show user action
    Route::get('/{user}', [UserController::class, 'show'])
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('user.show');

    // Delete user action
    Route::delete('/{user}', [UserController::class, 'destroy'])
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('user.destroy');

    // Update user role action
    Route::put('/{user}/role', [UserRoleController::class, 'update'])
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('user.role.update');

    // Ban user action
    Route::patch('/{user}/ban', BanUserController::class)
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('user.ban');

    // Unban user action
    Route::patch('/{user}/unban', UnbanUserController::class)
        ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
        ->name('user.unban');
});
