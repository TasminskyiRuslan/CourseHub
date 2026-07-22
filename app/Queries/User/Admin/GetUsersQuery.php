<?php

namespace App\Queries\User\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

readonly class GetUsersQuery
{
    /**
     * Retrieve paginated list of users.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function handle(Request $request): LengthAwarePaginator
    {
        return $this->query($request)
            ->paginate(config('pagination.users_per_page'))
            ->withQueryString();
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
            ->with(['roles'])
            ->withCount(['courses' => fn($q) => $q->withTrashed()])
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('name', 'like', "%$value%")
                            ->orWhere('email', 'like', "%$value%");
                    });
                }),
                AllowedFilter::callback('role', function ($query, $value) {
                    $query->whereHas('roles', function ($q) use ($value) {
                        $q->where('name', $value);
                    });
                }),
                AllowedFilter::callback('verified', function ($query, $value) {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    $value ? $query->whereNotNull('email_verified_at') : $query->whereNull('email_verified_at');
                }),
                AllowedFilter::callback('banned', function ($query, $value) {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    $value ? $query->whereNotNull('banned_at') : $query->whereNull('banned_at');
                }),
                AllowedFilter::trashed(),
            ])
            ->allowedSorts([
                'created_at',
                'name',
                'email_verified_at',
                'courses_count',
                'banned_at',
                'deleted_at',
            ])
            ->defaultSort('-created_at');
    }
}
