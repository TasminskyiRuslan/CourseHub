<?php

namespace App\Queries\Course\Public;

use App\Models\Course;
use App\Queries\CachedListQuery;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GetCoursesQuery extends CachedListQuery
{
    /**
     * Retrieve paginated list of courses for a teacher with conditional caching.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function handle(Request $request): LengthAwarePaginator
    {
        if (!$this->shouldUseCache($request)) {
            return $this->query($request)
                ->paginate(config('pagination.courses_per_page'))
                ->withQueryString();
        }

        $page = (int) $request->query('page', 1);
        $cacheKey = "courses:page:{$page}";
        $tags = [
            config('cache.tags.course_list')
        ];

        return Cache::tags($tags)
            ->remember(
                $cacheKey,
                config('cache.ttl.course'),
                fn () => $this->query($request)
                    ->paginate(config('pagination.courses_per_page'))
                    ->withQueryString()
            );
    }

    /**
     * Build query builder with allowed filters and sorting.
     *
     * @param Request $request
     * @return QueryBuilder
     */
    protected function query(Request $request): QueryBuilder
    {
        return QueryBuilder::for(Course::class, $request)
            ->active()
            ->with(['author' => function ($query) {
                $query->with('roles')->withCount('courses');
            }])
            ->withCount(['lessons'])
            ->allowedFilters([
                'type',
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('title', 'like', "%{$value}%")
                            ->orWhere('description', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('author', function ($query, $value) {
                    $query->whereHas('author', fn ($q) => $q->where('slug', $value));
                }),
            ])
            ->allowedSorts([
                'title',
                'price',
                'published_at',
                'lessons_count',
            ])
            ->defaultSort('-published_at');
    }
}
