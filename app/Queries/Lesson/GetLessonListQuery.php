<?php

namespace App\Queries\Lesson;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Queries\Concerns\HasCacheBypass;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GetLessonListQuery
{
    use HasCacheBypass;

    /**
     * Get paginated lessons with conditional caching.
     *
     * @param Course $course
     * @param User|null $user
     * @return LengthAwarePaginator
     */
    public function get(Course $course, ?User $user): LengthAwarePaginator
    {
        if ($this->shouldBypassCache($user) || request()->hasAny(['filter', 'sort', 'include'])) {
            return $this->fetchFromDatabase($course, $user);
        }

        return $this->fetchFromCache($course);
    }

    /**
     * Fetch filtered and sorted lessons directly from the database.
     *
     * @param Course $course
     * @param User|null $user
     * @return LengthAwarePaginator
     */
    protected function fetchFromDatabase(Course $course, ?User $user): LengthAwarePaginator
    {
        return QueryBuilder::for(Lesson::class)
            ->where('course_id', $course->id)
            ->visibleFor($user)
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('title', 'like', "%$value%");
                }),
            ])
            ->allowedSorts(['title', 'position', 'created_at'])
            ->defaultSort('position')
            ->with('lessonable')
            ->paginate(config('pagination.lessons_per_page'))
            ->withQueryString();
    }

    /**
     * Fetch lessons from the cache.
     *
     * @param Course $course
     * @return LengthAwarePaginator
     */
    protected function fetchFromCache(Course $course): LengthAwarePaginator
    {
        $page = request()->query('page', 1);
        $cacheKey = "course:{$course->id}:lessons:page:{$page}";
        $tags = [
            config('cache.tags.lesson_list'),
            config('cache.tags.course') . ':' . $course->id
        ];
        return Cache::tags($tags)->remember(
            $cacheKey,
            config('cache.ttl.lesson'),
            function () use ($course) {
                return Lesson::query()
                    ->where('course_id', $course->id)
                    ->with('lessonable')
                    ->orderBy('position')
                    ->paginate(config('pagination.lessons_per_page'))
                    ->withQueryString();
            }
        );
    }
}
