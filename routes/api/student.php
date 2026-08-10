<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Course\Student\CheckoutController;
use App\Http\Controllers\Api\Course\Student\CourseController;
use App\Http\Controllers\Api\Lesson\Student\LessonController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/
Route::prefix('student')
    ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
    ->group(function (): void {
        Route::prefix('courses')
            ->scopeBindings()
            ->group(function (): void {
                Route::post('/{publicCourse}/checkout', CheckoutController::class)
                    ->name('student.courses.checkout');

                Route::get('/', [CourseController::class, 'index'])
                    ->name('student.courses.index');

                Route::get('/{studentCourse}', [CourseController::class, 'show'])
                    ->name('student.courses.show');

                Route::prefix('/{studentCourse}/lessons')
                    ->group(function (): void {
                        Route::get('/', [LessonController::class, 'index'])
                            ->name('student.courses.lessons.index');

                        Route::get('/{studentLesson}', [LessonController::class, 'show'])
                            ->name('student.courses.lessons.show');
                    });
            });
    });
