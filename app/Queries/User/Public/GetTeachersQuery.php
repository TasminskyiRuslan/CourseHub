<?php

declare(strict_types=1);

namespace App\Queries\User\Public;

use App\Enums\UserRole;
use App\Models\User;
use App\Queries\BaseQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

readonly class GetTeachersQuery extends BaseQuery
{
    /**
     * Retrieve a paginated list of teachers with conditional caching.
     */
    public function handle(Request $request): LengthAwarePaginator
    {
        if (! $this->shouldUseCache($request)) {
            return $this->query($request)
                ->paginate(config('pagination.users_per_page'))
                ->withQueryString();
        }

        $page = (int) $request->query('page', 1);
        $cacheKey = "teachers:page:{$page}";
        $tags = [
            config('cache.tags.teacher_list'),
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
     */
    protected function query(Request $request): QueryBuilder
    {
        return QueryBuilder::for(User::active(), $request)
            ->whereHas('roles', function (Builder $query): void {
                $query->where('name', UserRole::TEACHER->value);
            })
            ->withCount(['courses' => fn (Builder $query): Builder => $query->active()])
            ->allowedFilters([
                AllowedFilter::callback('search', function (Builder $query, mixed $value): void {
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
