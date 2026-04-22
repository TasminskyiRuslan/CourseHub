<?php

namespace App\Queries\Course;

use App\Models\Course;
use App\Models\User;
use App\Queries\Concerns\HasCacheBypass;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class GetCourseListQuery
{
    use HasCacheBypass;

    /**
     * Get paginated courses with conditional caching.
     *
     * @param User|null $author
     * @return LengthAwarePaginator
     */
    public function get(?User $author): LengthAwarePaginator
    {
        if ($this->shouldBypassCache($author) || request()->hasAny(['filter', 'sort', 'include'])) {
            return $this->fetchFromDatabase($author);
        }

        return $this->fetchFromCache();
    }

    /**
     * Fetch filtered and sorted courses directly from the database.
     *
     * @param User|null $author
     * @return LengthAwarePaginator
     */
    protected function fetchFromDatabase(?User $author): LengthAwarePaginator
    {
        return QueryBuilder::for(Course::class)
            ->visibleFor($author)
            ->allowedIncludes([
                AllowedInclude::count('lessons_count', 'lessons'),
                'author',
            ])
            ->allowedFilters([
                'type',
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('title', 'like', "%$value%")
                            ->orWhere('description', 'like', "%$value%");
                    });
                }),
                AllowedFilter::callback('author', function ($query, $value) {
                    $query->whereHas('author', function ($q) use ($value) {
                        $q->where('slug', $value);
                    });
                }),
            ])
            ->allowedSorts(['title', 'price', 'created_at'])
            ->defaultSort('-created_at')
            ->paginate(config('pagination.courses_per_page'))
            ->withQueryString();
    }

    /**
     * Fetch courses from the cache.
     *
     * @return LengthAwarePaginator
     */
    protected function fetchFromCache(): LengthAwarePaginator
    {
        $page = request()->query('page', 1);
        $cacheKey = "courses:page:{$page}";
        $tags = [
            config('cache.tags.course_list'),
        ];
        return Cache::tags($tags)->remember(
            $cacheKey,
            config('cache.ttl.lesson'),
            function () {
                return Course::query()
                    ->where('is_published', true)
                    ->orderByDesc('created_at')
                    ->paginate(config('pagination.courses_per_page'))
                    ->withQueryString();
            }
        );
    }
}
