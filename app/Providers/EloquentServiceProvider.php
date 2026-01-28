<?php

namespace App\Providers;

use App\Enums\CourseType;
use App\Models\OfflineLesson;
use App\Models\OnlineLesson;
use App\Models\VideoLesson;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class EloquentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            CourseType::VIDEO->value   => VideoLesson::class,
            CourseType::ONLINE->value  => OnlineLesson::class,
            CourseType::OFFLINE->value => OfflineLesson::class,
        ]);
    }
}
