<?php

declare(strict_types=1);

namespace App\Queries\User\Admin;

use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

readonly class GetUsersQuery
{
    /**
     * Retrieve paginated list of users.
     */
    public function handle(Request $request): LengthAwarePaginator
    {
        return $this->query($request)
            ->paginate(config('pagination.users_per_page'))
            ->withQueryString();
    }

    /**
     * Build query builder with allowed filters and sorting.
     */
    protected function query(Request $request): QueryBuilder
    {
        return QueryBuilder::for(User::withTrashed(), $request)
            ->with(['roles'])
            ->withCount(['courses' => fn (Builder $q): Builder => $q->withTrashed()])
            ->allowedFilters([
                AllowedFilter::callback('search', function (Builder $query, mixed $value): void {
                    $query->where(function (Builder $q) use ($value): void {
                        $q->where('name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('role', function (Builder $query, mixed $value): void {
                    $query->when(
                        $value === 'without_role',
                        fn (Builder $q) => $q->doesntHave('roles'),
                        fn (Builder $q) => $q->whereRelation('roles', 'name', $value)
                    );
                }),
                AllowedFilter::callback('verified', function (Builder $query, mixed $value): void {
                    filter_var($value, FILTER_VALIDATE_BOOLEAN)
                        ? $query->whereNotNull('email_verified_at')
                        : $query->whereNull('email_verified_at');
                }),
                AllowedFilter::callback('banned', function (Builder $query, mixed $value): void {
                    filter_var($value, FILTER_VALIDATE_BOOLEAN)
                        ? $query->whereNotNull('banned_at')
                        : $query->whereNull('banned_at');
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
