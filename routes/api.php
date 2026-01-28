<?php
//
//use App\Http\Controllers\Api\Auth\AuthController;
//use App\Http\Controllers\Api\Auth\ResetPasswordController1;
//use App\Http\Controllers\Api\Auth\VerificationController;
//use App\Http\Controllers\Api\Course\CourseController;
//use App\Http\Controllers\Api\Course\CourseImageController;
//use App\Http\Controllers\Api\Course\CoursePublishController;
//use App\Http\Controllers\Api\Course\LessonController;
//use Illuminate\Support\Facades\Route;
//
///*
//|--------------------------------------------------------------------------
//| Authentication & User
//|--------------------------------------------------------------------------
//*/
//
//Route::prefix('auth')->group(function () {
//
//    Route::middleware('guest:sanctum')->group(function () {
//        Route::controller(AuthController::class)->group(function () {
//            Route::post('/register', 'register');
//            Route::post('/login', 'login');
//        });
//
//        Route::controller(ResetPasswordController1::class)->group(function () {
//            Route::post('/password/forgot', 'sendResetLink');
//            Route::post('/password/reset', 'reset');
//        });
//    });
//
//    Route::middleware('auth:sanctum')->controller(AuthController::class)->group(function () {
//        Route::get('/me', 'me');
//        Route::post('/logout', 'logout');
//        Route::delete('/tokens', 'logoutAll');
//    });
//});
//
///*
//|--------------------------------------------------------------------------
//| Email Verification
//|--------------------------------------------------------------------------
//*/
//
//Route::prefix('auth/email')->group(function () {
//
//    Route::get('/verify/{id}/{hash}', [VerificationController::class, 'verify'])
//        ->middleware(['signed', 'throttle:6,1'])
//        ->name('verification.verify');
//
//    Route::post('/verification-notification', [VerificationController::class, 'resend'])
//        ->middleware(['auth:sanctum', 'throttle:6,1'])
//        ->name('verification.resend');
//});
//
///*
//|--------------------------------------------------------------------------
//| Courses & Lessons
//|--------------------------------------------------------------------------
//*/
//
//Route::prefix('courses')->group(function () {
//
//    // Public Read Access
//    Route::controller(CourseController::class)->group(function () {
//        Route::get('/', 'index');
//        Route::get('/{course}', 'show');
//    });
//
//    Route::prefix('/{course}/lessons')->controller(LessonController::class)->group(function () {
//        Route::get('/', 'index');
//        Route::get('/{lesson}', 'show')->scopeBindings();
//    });
//
//    // Protected Write Access
//    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
//
//        Route::controller(CourseController::class)->group(function () {
//            Route::post('/', 'store');
//            Route::put('/{course}', 'update');
//            Route::delete('/{course}', 'destroy');
//        });
//
//        Route::prefix('/{course}')->group(function () {
//
//            Route::controller(CourseImageController::class)->group(function () {
//                Route::post('/image', 'store');
//                Route::delete('/image', 'destroy');
//            });
//
//            Route::controller(CoursePublishController::class)->group(function () {
//                Route::patch('/publish', 'publish');
//                Route::patch('/unpublish', 'unpublish');
//            });
//
//            Route::prefix('/lessons')->controller(LessonController::class)->group(function () {
//                Route::post('/', 'store');
//                Route::put('/{lesson}', 'update')->scopeBindings();
//                Route::delete('/{lesson}', 'destroy')->scopeBindings();
//            });
//        });
//    });
//});


use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutAllController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResendVerificationController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\Courses\CourseController;
use App\Http\Controllers\Api\Courses\CourseImageController;
use App\Http\Controllers\Api\Courses\PublishCourseController;
use App\Http\Controllers\Api\Courses\UnpublishCourseController;
use App\Http\Controllers\Api\Lessons\LessonController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication actions
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    // Register action
    Route::post('/register', RegisterController::class);

    // Login action
    Route::post('/login', LoginController::class);

    // Me action
    Route::get('/me', MeController::class)->middleware('auth:sanctum');

    // Logout actions
    Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');
    Route::delete('/tokens', LogoutAllController::class)->middleware('auth:sanctum');

    // Password actions
    Route::post('/password/forgot', ForgotPasswordController::class)->middleware('throttle:5,1');
    Route::post('/password/reset', ResetPasswordController::class);

    // Email verification actions
    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', ResendVerificationController::class)
        ->middleware(['auth:sanctum', 'throttle:6,1'])
        ->name('verification.resend');
});

/*
|--------------------------------------------------------------------------
| Courses & Lessons actions
|--------------------------------------------------------------------------
*/
Route::prefix('courses')->group(function () {
    // Course actions
    Route::get('/', [CourseController::class, 'index']);
    Route::post('/', [CourseController::class, 'store'])->middleware(['auth:sanctum', 'verified']);
    Route::get('/{course}', [CourseController::class, 'show']);
    Route::put('/{course}', [CourseController::class, 'update'])->middleware(['auth:sanctum', 'verified']);
    Route::delete('/{course}', [CourseController::class, 'destroy'])->middleware(['auth:sanctum', 'verified']);

    // Lesson actions
    Route::prefix('{course}/lessons')->group(function () {
        Route::get('/', [LessonController::class, 'index'])->scopeBindings();
        Route::post('/', [LessonController::class, 'store'])->middleware(['auth:sanctum', 'verified']);
        Route::get('/{lesson}', [LessonController::class, 'show'])->scopeBindings();
        Route::put('/{lesson}', [LessonController::class, 'update'])->middleware(['auth:sanctum', 'verified']);
        Route::delete('/{lesson}', [LessonController::class, 'destroy'])->middleware(['auth:sanctum', 'verified']);
    })->scopeBindings();

    // Course image actions
    Route::put('/{course}/image', [CourseImageController::class, 'update'])->middleware(['auth:sanctum', 'verified']);
    Route::delete('/{course}/image', [CourseImageController::class, 'destroy'])->middleware(['auth:sanctum', 'verified']);

    // Publish actions
    Route::patch('/{course}/publish', PublishCourseController::class)->middleware(['auth:sanctum', 'verified']);
    Route::patch('/{course}/unpublish', UnpublishCourseController::class)->middleware(['auth:sanctum', 'verified']);
});
