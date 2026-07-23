<?php

use App\Http\Controllers\Api\Course\Student\CheckoutController;
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

                // Checkout course
                Route::post('/{course}/checkout', CheckoutController::class)
                    ->name('student.courses.checkout');
            });
    });
