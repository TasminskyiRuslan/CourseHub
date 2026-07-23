<?php

use App\Enums\UserPermission;
use App\Http\Controllers\Api\Course\Admin\BanCourseController;
use App\Http\Controllers\Api\Course\Admin\CourseController;
use App\Http\Controllers\Api\Course\Admin\UnbanCourseController;
use App\Http\Controllers\Api\Lesson\Admin\LessonController;
use App\Http\Controllers\Api\User\Admin\BanUserController;
use App\Http\Controllers\Api\User\Admin\UnbanUserController;
use App\Http\Controllers\Api\User\Admin\UserController;
use App\Http\Controllers\Api\User\Admin\UserRoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin actions
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user', 'can:' . UserPermission::ADMIN_PANEL_ACCESS->value])
    ->group(function () {

        // Course actions
        Route::prefix('courses')
            ->scopeBindings()
            ->group(function () {

                // Get all courses list action
                Route::get('/', [CourseController::class, 'index'])
                    ->name('admin.courses.index');

                // Show course action
                Route::get('/{course}', [CourseController::class, 'show'])
                    ->name('admin.courses.show');

                // Delete course action
                Route::delete('/{course}', [CourseController::class, 'destroy'])
                    ->name('admin.courses.destroy');

                // Ban course action
                Route::patch('/{course}/ban', BanCourseController::class)
                    ->name('admin.courses.ban');

                // Unban course action
                Route::patch('/{course}/unban', UnbanCourseController::class)
                    ->name('admin.courses.unban');

                // Lesson actions
                Route::prefix('/{course}/lessons')->group(function () {

                    // Get course lessons list action
                    Route::get('/', [LessonController::class, 'index'])
                        ->name('admin.courses.lessons.index');

                    // Show lesson action
                    Route::get('/{lesson}', [LessonController::class, 'show'])
                        ->name('admin.courses.lessons.show');

                    // Delete lesson action
                    Route::delete('/{lesson}', [LessonController::class, 'destroy'])
                        ->name('admin.courses.lessons.destroy');
                });
            });

        // Users actions
        Route::prefix('users')
            ->group(function () {

                // Get users list action
                Route::get('/', [UserController::class, 'index'])
                    ->name('admin.users.index');

                // Show user action
                Route::get('/{user}', [UserController::class, 'show'])
                    ->name('admin.users.show');

                // Delete user action
                Route::delete('/{user}', [UserController::class, 'destroy'])
                    ->name('admin.users.destroy');

                // Update user role action
                Route::put('/{user}/role', [UserRoleController::class, 'update'])
                    ->name('admin.users.role.update');

                // Ban user action
                Route::patch('/{user}/ban', BanUserController::class)
                    ->name('admin.users.ban');

                // Unban user action
                Route::patch('/{user}/unban', UnbanUserController::class)
                    ->name('admin.users.unban');
            });
    });
