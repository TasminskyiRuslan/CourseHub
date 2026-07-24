<?php

use App\Enums\UserPermission;
use App\Http\Controllers\Api\Course\Teacher\CourseController;
use App\Http\Controllers\Api\Course\Teacher\CourseImageController;
use App\Http\Controllers\Api\Course\Teacher\PublishCourseController;
use App\Http\Controllers\Api\Course\Teacher\UnpublishCourseController;
use App\Http\Controllers\Api\Lesson\Teacher\LessonController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Teacher actions
|--------------------------------------------------------------------------
*/
Route::prefix('teacher')
    ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user', 'can:' . UserPermission::TEACHER_PANEL_ACCESS->value])
    ->group(function () {

        // Course actions
        Route::prefix('courses')
            ->scopeBindings()
            ->group(function () {

                // Get teacher courses list action
                Route::get('/', [CourseController::class, 'index'])
                    ->name('teacher.courses.index');

                // Create course action
                Route::post('/', [CourseController::class, 'store'])
                    ->name('teacher.courses.store');

                // Show teacher course action
                Route::get('/{course}', [CourseController::class, 'show'])
                    ->name('teacher.courses.show');

                // Update teacher course action
                Route::patch('/{course}', [CourseController::class, 'update'])
                    ->name('teacher.courses.update');

                // Delete teacher course action
                Route::delete('/{course}', [CourseController::class, 'destroy'])
                    ->name('teacher.courses.destroy');

                // Update teacher course image action
                Route::put('/{course}/image', [CourseImageController::class, 'update'])
                    ->name('teacher.courses.image.update');

                // Delete teacher course image action
                Route::delete('/{course}/image', [CourseImageController::class, 'destroy'])
                    ->name('teacher.courses.image.destroy');

                // Publish teacher course actions
                Route::patch('/{course}/publish', PublishCourseController::class)
                    ->name('teacher.courses.publish');

                // Unpublish teacher course action
                Route::patch('/{course}/unpublish', UnpublishCourseController::class)
                    ->name('teacher.courses.unpublish');

                // Lesson actions
                Route::prefix('/{course}/lessons')->group(function () {

                    // Get course lessons list action
                    Route::get('/', [LessonController::class, 'index'])
                        ->name('teacher.courses.lessons.index');

                    // Create lesson action
                    Route::post('/', [LessonController::class, 'store'])
                        ->name('teacher.courses.lessons.store');

                    // Show course lesson action
                    Route::get('/{lesson}', [LessonController::class, 'show'])
                        ->name('teacher.courses.lessons.show');

                    // Update course lesson action
                    Route::patch('/{lesson}', [LessonController::class, 'update'])
                        ->name('teacher.courses.lessons.update');

                    // Delete course lesson action
                    Route::delete('/{lesson}', [LessonController::class, 'destroy'])
                        ->name('teacher.courses.lessons.destroy');
                });
            });
    });
