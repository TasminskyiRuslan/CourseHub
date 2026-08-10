<?php

declare(strict_types=1);

use App\Enums\UserPermission;
use App\Http\Controllers\Api\Course\Teacher\CourseController;
use App\Http\Controllers\Api\Course\Teacher\CourseImageController;
use App\Http\Controllers\Api\Course\Teacher\PublishCourseController;
use App\Http\Controllers\Api\Course\Teacher\UnpublishCourseController;
use App\Http\Controllers\Api\Lesson\Teacher\LessonController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
*/
Route::prefix('teacher')
    ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user', 'can:'.UserPermission::TEACHER_PANEL_ACCESS->value])
    ->group(function () {
        Route::prefix('courses')
            ->scopeBindings()
            ->group(function (): void {
                Route::get('/', [CourseController::class, 'index'])
                    ->name('teacher.courses.index');

                Route::post('/', [CourseController::class, 'store'])
                    ->name('teacher.courses.store');

                Route::get('/{teacherCourse}', [CourseController::class, 'show'])
                    ->name('teacher.courses.show');

                Route::patch('/{teacherCourse}', [CourseController::class, 'update'])
                    ->name('teacher.courses.update');

                Route::delete('/{teacherCourse}', [CourseController::class, 'destroy'])
                    ->name('teacher.courses.destroy');

                Route::put('/{teacherCourse}/image', [CourseImageController::class, 'update'])
                    ->name('teacher.courses.image.update');

                Route::delete('/{teacherCourse}/image', [CourseImageController::class, 'destroy'])
                    ->name('teacher.courses.image.destroy');

                Route::patch('/{teacherCourse}/publish', PublishCourseController::class)
                    ->name('teacher.courses.publish');

                Route::patch('/{teacherCourse}/unpublish', UnpublishCourseController::class)
                    ->name('teacher.courses.unpublish');

                Route::prefix('/{teacherCourse}/lessons')
                    ->group(function (): void {

                        Route::get('/', [LessonController::class, 'index'])
                            ->name('teacher.courses.lessons.index');

                        Route::post('/', [LessonController::class, 'store'])
                            ->name('teacher.courses.lessons.store');

                        Route::get('/{teacherLesson}', [LessonController::class, 'show'])
                            ->name('teacher.courses.lessons.show');

                        Route::patch('/{teacherLesson}', [LessonController::class, 'update'])
                            ->name('teacher.courses.lessons.update');

                        Route::delete('/{teacherLesson}', [LessonController::class, 'destroy'])
                            ->name('teacher.courses.lessons.destroy');
                    });
            });
    });
