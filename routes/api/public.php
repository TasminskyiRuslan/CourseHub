<?php

use App\Http\Controllers\Api\Course\Public\CourseController;
use App\Http\Controllers\Api\User\Public\TeacherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public actions
|--------------------------------------------------------------------------
*/

// Course actions
Route::prefix('courses')->group(function () {

    // Get courses list action
    Route::get('/', [CourseController::class, 'index'])
        ->name('courses.index');

    // Show course action
    Route::get('/{course}', [CourseController::class, 'show'])
        ->name('courses.show');
});

// Teacher actions
Route::prefix('teachers')->group(function () {

    // Get teachers list action
    Route::get('/', [TeacherController::class, 'index'])
        ->name('teachers.index');

    // Show teacher action
    Route::get('/{teacher}', [TeacherController::class, 'show'])
        ->name('teachers.show');
});
