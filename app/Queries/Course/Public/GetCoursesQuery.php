<?php

declare(strict_types=1);

namespace App\Queries\Course\Public;

use App\Models\Course;
use App\Queries\BaseQuery;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

readonly class GetCoursesQuery extends BaseQuery
{
    /**
     * Retrieve a paginated list of active courses with conditional caching.
     */
    public function handle(Request $request): LengthAwarePaginator
    {
        if (! $this->shouldUseCache($request)) {
            return $this->query($request)
                ->paginate(config('pagination.courses_per_page'))
                ->withQueryString();
        }

        $page = (int) $request->query('page', 1);
        $cacheKey = "courses:page:{$page}";
        $tags = [
            config('cache.tags.course_list'),
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
     */
    protected function query(Request $request): QueryBuilder
    {
        return QueryBuilder::for(Course::active(), $request)
            ->with(['author' => function (Builder $query): void {
                $query->with(['roles'])
                    ->withCount(['courses' => fn (Builder $q): Builder => $q->active()]);
            }])
            ->withCount(['lessons'])
            ->allowedFilters([
                'type',
                AllowedFilter::callback('search', function (Builder $query, mixed $value): void {
                    $query->where(function (Builder $q) use ($value): void {
                        $q->where('title', 'like', "%{$value}%")
                            ->orWhere('description', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('author', function (Builder $query, mixed $value): void {
                    $query->whereRelation('author', 'slug', $value);
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
