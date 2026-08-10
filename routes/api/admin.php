<?php

declare(strict_types=1);

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
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user', 'can:'.UserPermission::ADMIN_PANEL_ACCESS->value])
    ->group(function (): void {
        Route::prefix('courses')
            ->scopeBindings()
            ->group(function (): void {
                Route::get('/', [CourseController::class, 'index'])
                    ->name('admin.courses.index');

                Route::get('/{adminCourse}', [CourseController::class, 'show'])
                    ->name('admin.courses.show');

                Route::delete('/{adminCourse}', [CourseController::class, 'destroy'])
                    ->name('admin.courses.destroy');

                Route::patch('/{adminCourse}/ban', BanCourseController::class)
                    ->name('admin.courses.ban');

                Route::patch('/{adminCourse}/unban', UnbanCourseController::class)
                    ->name('admin.courses.unban');

                Route::prefix('/{adminCourse}/lessons')
                    ->group(function (): void {
                        Route::get('/', [LessonController::class, 'index'])
                            ->name('admin.courses.lessons.index');

                        Route::get('/{adminLesson}', [LessonController::class, 'show'])
                            ->name('admin.courses.lessons.show');

                        Route::delete('/{adminLesson}', [LessonController::class, 'destroy'])
                            ->name('admin.courses.lessons.destroy');
                    });
            });

        Route::prefix('users')
            ->group(function (): void {
                Route::get('/', [UserController::class, 'index'])
                    ->name('admin.users.index');

                Route::get('/{adminUser}', [UserController::class, 'show'])
                    ->name('admin.users.show');

                Route::delete('/{adminUser}', [UserController::class, 'destroy'])
                    ->name('admin.users.destroy');

                Route::put('/{adminUser}/role', [UserRoleController::class, 'update'])
                    ->name('admin.users.role.update');

                Route::patch('/{adminUser}/ban', BanUserController::class)
                    ->name('admin.users.ban');

                Route::patch('/{adminUser}/unban', UnbanUserController::class)
                    ->name('admin.users.unban');
            });
    });
