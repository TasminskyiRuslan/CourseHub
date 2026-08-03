<?php

use App\Http\Controllers\Api\Course\Student\CheckoutController;
use App\Http\Controllers\Api\Course\Student\CourseController;
use App\Http\Controllers\Api\Lesson\Student\LessonController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student actions
|--------------------------------------------------------------------------
*/
Route::prefix('student')
    ->middleware(['auth:sanctum', 'verified', 'restrict.banned.user'])
    ->group(function () {

        // Course actions
        Route::prefix('courses')
            ->scopeBindings()
            ->group(function () {

                // Checkout course action
                Route::post('/{course}/checkout', CheckoutController::class)
                    ->name('student.courses.checkout');

                // Get student courses list action
                Route::get('/', [CourseController::class, 'index'])
                    ->name('student.courses.index');

                // Show student course action
                Route::get('/{course}', [CourseController::class, 'show'])
                    ->name('student.courses.show');

                // Lesson actions
                Route::prefix('/{course}/lessons')->group(function () {

                    // Get course lessons list action
                    Route::get('/', [LessonController::class, 'index'])
                        ->name('student.courses.lessons.index');

                    // Show course lesson action
                    Route::get('/{lesson}', [LessonController::class, 'show'])
                        ->name('student.courses.lessons.show');
                });
            });
    });
