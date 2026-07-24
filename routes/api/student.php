<?php

use App\Http\Controllers\Api\Course\Student\CheckoutController;
use App\Http\Controllers\Api\Course\Student\CourseController;
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
            });
    });
