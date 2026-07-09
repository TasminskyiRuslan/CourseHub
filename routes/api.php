<?php

use App\Enums\UserPermission;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth actions
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    // Register action
    Route::post('/register', \App\Http\Controllers\Api\Auth\RegisterController::class)
        ->name('auth.register');

    // Login action
    Route::post('/login', \App\Http\Controllers\Api\Auth\LoginController::class)
        ->name('auth.login');

    // Logout action
    Route::delete('/token', \App\Http\Controllers\Api\Auth\LogoutController::class)
        ->middleware('auth:sanctum')
        ->name('auth.token.destroy');

    // Logout all action
    Route::delete('/tokens', \App\Http\Controllers\Api\Auth\LogoutAllController::class)
        ->middleware('auth:sanctum')
        ->name('auth.tokens.destroy');

    // Send password reset email action
    Route::post('/password/forgot', \App\Http\Controllers\Api\Auth\SendPasswordResetEmailController::class)
        ->middleware('throttle:5,1')
        ->name('auth.password.forgot');

    // Reset password action
    Route::post('/password/reset', \App\Http\Controllers\Api\Auth\ResetPasswordController::class)
        ->name('auth.password.reset');

    // Verify email actions
    Route::get('/email/verify/{id}/{hash}', \App\Http\Controllers\Api\Auth\VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('auth.verification.verify');

    // Resend verification email action
    Route::post('/email/verification-notification', \App\Http\Controllers\Api\Auth\SendVerificationEmailController::class)
        ->middleware(['auth:sanctum', 'throttle:6,1'])
        ->name('auth.verification.send');
});

Route::prefix('account')
    ->middleware(['auth:sanctum'])
    ->group(function () {
    // Show auth action
    Route::get('/', [\App\Http\Controllers\Api\User\Account\AccountController::class, 'show'])
        ->name('account.show');

    // Update auth action
    Route::patch('/', [\App\Http\Controllers\Api\User\Account\AccountController::class, 'update'])
        ->middleware(['restrict.banned.user'])
        ->name('account.update');

    // Update auth avatar action
    Route::put('/avatar', [\App\Http\Controllers\Api\User\Account\AccountAvatarController::class, 'update'])
        ->middleware(['restrict.banned.user'])
        ->name('account.avatar.update');

    // Delete auth avatar action
    Route::delete('/avatar', [\App\Http\Controllers\Api\User\Account\AccountAvatarController::class, 'destroy'])
        ->middleware(['restrict.banned.user'])
        ->name('account.avatar.destroy');
});

/*
|--------------------------------------------------------------------------
| Course & Lessons actions
|--------------------------------------------------------------------------
*/
Route::prefix('courses')->group(function () {
    // Get courses list action
    Route::get('/', [\App\Http\Controllers\Api\Course\Public\CourseController::class, 'index'])
        ->name('course.index');

    // Show course action
    Route::get('/{course}', [\App\Http\Controllers\Api\Course\Public\CourseController::class, 'show'])
        ->name('course.show');
});

Route::prefix('teachers')->group(function () {
        // Get teachers list action
    Route::get('/', [\App\Http\Controllers\Api\User\Public\TeacherController::class, 'index'])
        ->name('teacher.index');

        // Show teacher action
    Route::get('/{teacher}', [\App\Http\Controllers\Api\User\Public\TeacherController::class, 'show'])
        ->name('teacher.show');
});

Route::prefix('teacher')
    ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user', 'can:' . UserPermission::TEACHER_PANEL_ACCESS->value])
    ->group(function () {
    Route::prefix('courses')
        ->scopeBindings()
        ->group(function () {
            // Get teacher courses list action
            Route::get('/', [\App\Http\Controllers\Api\Course\Teacher\CourseController::class, 'index'])
                ->name('teacher.course.index');

            // Create course action
            Route::post('/', [\App\Http\Controllers\Api\Course\Teacher\CourseController::class, 'store'])
                ->name('teacher.course.store');

            // Show course action
            Route::get('/{course}', [\App\Http\Controllers\Api\Course\Teacher\CourseController::class, 'show'])
                ->name('teacher.course.show');

            // Update course action
            Route::patch('/{course}', [\App\Http\Controllers\Api\Course\Teacher\CourseController::class, 'update'])
                ->name('teacher.course.update');

            // Delete course action
            Route::delete('/{course}', [\App\Http\Controllers\Api\Course\Teacher\CourseController::class, 'destroy'])
                ->name('teacher.course.destroy');

            // Update course image action
            Route::put('/{course}/image', [\App\Http\Controllers\Api\Course\Teacher\CourseImageController::class, 'update'])
                ->name('teacher.course.image.update');

            // Delete course image action
            Route::delete('/{course}/image', [\App\Http\Controllers\Api\Course\Teacher\CourseImageController::class, 'destroy'])
                ->name('teacher.course.image.destroy');

            // Publish course actions
            Route::patch('/{course}/publish', \App\Http\Controllers\Api\Course\Teacher\PublishCourseController::class)
                ->name('teacher.course.publish');

            // Unpublish course action
            Route::patch('/{course}/unpublish', \App\Http\Controllers\Api\Course\Teacher\UnpublishCourseController::class)
                ->name('teacher.course.unpublish');

            Route::prefix('/{course}/lessons')->group(function () {
                // Get course lessons list action
                Route::get('/', [\App\Http\Controllers\Api\Lesson\Teacher\LessonController::class, 'index'])
                    ->name('teacher.course.lesson.index');

                // Create lesson action
                Route::post('/', [\App\Http\Controllers\Api\Lesson\Teacher\LessonController::class, 'store'])
                    ->name('teacher.course.lesson.store');

                // Show lesson action
                Route::get('/{lesson}', [\App\Http\Controllers\Api\Lesson\Teacher\LessonController::class, 'show'])
                    ->name('teacher.course.lesson.show');

                // Update lesson action
                Route::patch('/{lesson}', [\App\Http\Controllers\Api\Lesson\Teacher\LessonController::class, 'update'])
                    ->name('teacher.course.lesson.update');

                // Delete lesson action
                Route::delete('/{lesson}', [\App\Http\Controllers\Api\Lesson\Teacher\LessonController::class, 'destroy'])
                    ->name('teacher.course.lesson.destroy');
            });
        });
});

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user', 'can:' . UserPermission::ADMIN_PANEL_ACCESS->value])
    ->group(function () {
    Route::prefix('courses')
        ->scopeBindings()
        ->group(function () {
            // Get all courses list action
            Route::get('/', [\App\Http\Controllers\Api\Course\Admin\CourseController::class, 'index'])
                ->name('admin.course.index');

            // Show course action
            Route::get('/{course}', [\App\Http\Controllers\Api\Course\Admin\CourseController::class, 'show'])
                ->name('admin.course.show');

            // Delete course action
            Route::delete('/{course}', [\App\Http\Controllers\Api\Course\Admin\CourseController::class, 'destroy'])
                ->name('admin.course.destroy');

            // Ban course action
            Route::patch('/{course}/ban', \App\Http\Controllers\Api\Course\Admin\BanCourseController::class)
                ->name('admin.course.ban');

            // Unban course action
            Route::patch('/{course}/unban', \App\Http\Controllers\Api\Course\Admin\UnbanCourseController::class)
                ->name('admin.course.unban');

            Route::prefix('/{course}/lessons')->group(function () {
                // Get course lessons list action
                Route::get('/', [\App\Http\Controllers\Api\Lesson\Admin\LessonController::class, 'index'])
                    ->name('admin.course.lesson.index');

                // Show lesson action
                Route::get('/{lesson}', [\App\Http\Controllers\Api\Lesson\Admin\LessonController::class, 'show'])
                    ->name('admin.course.lesson.show');

                // Delete lesson action
                Route::delete('/{lesson}', [\App\Http\Controllers\Api\Lesson\Admin\LessonController::class, 'destroy'])
                    ->name('admin.course.lesson.destroy');
            });
        });

    Route::prefix('users')
        ->group(function () {
            // Get users list action
            Route::get('/', [\App\Http\Controllers\Api\User\Admin\UserController::class, 'index'])
                ->name('admin.user.index');

            // Show user action
            Route::get('/{user}', [\App\Http\Controllers\Api\User\Admin\UserController::class, 'show'])
                ->name('admin.user.show');

            // Delete user action
            Route::delete('/{user}', [\App\Http\Controllers\Api\User\Admin\UserController::class, 'destroy'])
                ->name('admin.user.destroy');

            // Update user role action
            Route::put('/{user}/role', [\App\Http\Controllers\Api\User\Admin\UserRoleController::class, 'update'])
                ->name('admin.user.role.update');

            // Ban user action
            Route::patch('/{user}/ban', \App\Http\Controllers\Api\User\Admin\BanUserController::class)
                ->name('admin.user.ban');

            // Unban user action
            Route::patch('/{user}/unban', \App\Http\Controllers\Api\User\Admin\UnbanUserController::class)
                ->name('admin.user.unban');
        });
});
