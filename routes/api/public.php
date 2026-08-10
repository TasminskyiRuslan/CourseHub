<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Course\Public\CourseController;
use App\Http\Controllers\Api\User\Public\TeacherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Courses
Route::prefix('courses')->group(function (): void {
    Route::get('/', [CourseController::class, 'index'])
        ->name('courses.index');

    Route::get('/{publicCourse}', [CourseController::class, 'show'])
        ->name('courses.show');
});

// Teachers
Route::prefix('teachers')->group(function () {
    Route::get('/', [TeacherController::class, 'index'])
        ->name('teachers.index');

    Route::get('/{publicTeacher}', [TeacherController::class, 'show'])
        ->name('teachers.show');
});
