<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteBindingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap contextual route model bindings.
     */
    public function boot(): void
    {
        Route::bind('publicCourse', function (string $value): Course {
            return Course::query()
                ->active()
                ->where('slug', $value)
                ->firstOrFail();
        });

        Route::bind('studentCourse', function (string $value): Course {
            return request()->user()
                ->enrolledCourses()
                ->active()
                ->where('slug', $value)
                ->firstOrFail();
        });

        Route::bind('teacherCourse', function (string $value): Course {
            return request()->user()
                ->courses()
                ->where('slug', $value)
                ->firstOrFail();
        });

        Route::bind('adminCourse', function (string $value): Course {
            return Course::query()
                ->withTrashed()
                ->where('slug', $value)
                ->firstOrFail();
        });

        Route::bind('studentLesson', function (string $value, RoutingRoute $route): Lesson {
            return $route->parameter('studentCourse')
                ->lessons()
                ->where('slug', $value)
                ->firstOrFail();
        });

        Route::bind('teacherLesson', function (string $value, RoutingRoute $route): Lesson {
            return $route->parameter('teacherCourse')
                ->lessons()
                ->where('slug', $value)
                ->firstOrFail();
        });

        Route::bind('adminLesson', function (string $value, RoutingRoute $route): Lesson {
            return $route->parameter('adminCourse')
                ->lessons()
                ->withTrashed()
                ->where('slug', $value)
                ->firstOrFail();
        });

        Route::bind('publicTeacher', function (string $value): User {
            return User::query()
                ->active()
                ->where('slug', $value)
                ->whereHas('roles', fn (Builder $q) => $q->where('name', UserRole::TEACHER->value))
                ->firstOrFail();
        });

        Route::bind('adminUser', function (string $value): User {
            return User::query()
                ->withTrashed()
                ->where('slug', $value)
                ->firstOrFail();
        });
    }
}
