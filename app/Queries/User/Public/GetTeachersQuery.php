<?php

namespace App\Queries\User\Public;

use App\Enums\UserRole;
use App\Models\User;
use App\Queries\CachedListQuery;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GetTeachersQuery extends CachedListQuery
{
    /**
     * Retrieve paginated list of active teachers with conditional caching.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function handle(Request $request): LengthAwarePaginator
    {
        if (!$this->shouldUseCache($request)) {
            return $this->query($request)
                ->paginate(config('pagination.users_per_page'))
                ->withQueryString();
        }

        $page = (int) $request->query('page', 1);
        $cacheKey = "teachers:page:{$page}";
        $tags = [
            config('cache.tags.teacher_list')
        ];

        return Cache::tags($tags)
            ->remember(
                $cacheKey,
                config('cache.ttl.teacher'),
                fn () => $this->query($request)
                    ->paginate(config('pagination.users_per_page'))
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
        return QueryBuilder::for(User::class, $request)
            ->active()
            ->whereHas('roles', function ($query) {
                $query->where('name', UserRole::TEACHER->value);
            })
            ->with(['roles'])
            ->withCount(['courses'])
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('name', 'like', "%$value%");
                }),
            ])
            ->allowedSorts([
                'courses_count',
                'created_at',
                'name',
            ])
            ->defaultSort('-created_at');
    }
}
