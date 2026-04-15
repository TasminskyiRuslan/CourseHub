<?php

namespace App\Queries\Course;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class CourseListQuery
{
    /**
     * Handle the request and return results with or without caching based on user permissions.
     *
     * @return LengthAwarePaginator
     * @throws AuthenticationException
     */
    public function handle(): LengthAwarePaginator
    {
        $user = auth()->user();
        if ($user?->hasAnyPermission(UserPermission::COURSE_VIEW_UNPUBLISHED->value, UserPermission::COURSE_CREATE->value) || $user?->hasRole(UserRole::SUPER_ADMIN->value)) {
            return $this->get($user);
        }

        return Cache::tags(['course'])->remember(md5(http_build_query(request()->only('page', 'filter', 'sort', 'include'))), config('cache.ttl.course'), function () use ($user) {
            return $this->get($user);
        });
    }

    /**
     * Build the course list query with filters, sorting, and visibility scopes.
     *
     * @param User|null $user
     * @return LengthAwarePaginator
     */
    protected function get(?User $user): LengthAwarePaginator
    {
        return QueryBuilder::for(Course::class)
            ->visibleFor($user)
            ->allowedIncludes([
                AllowedInclude::count('lessons_count', 'lessons'),
                'lessons',
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
}
