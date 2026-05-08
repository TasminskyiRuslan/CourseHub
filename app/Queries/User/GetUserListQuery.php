<?php

namespace App\Queries\User;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GetUserListQuery
{
    /**
     * Retrieve paginated list of users.
     *
     * @return LengthAwarePaginator
     */
    public function handle(): LengthAwarePaginator
    {
        return QueryBuilder::for(User::class)
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
                    $value = boolval($value);
                    $value ? $query->whereNotNull('email_verified_at') : $query->whereNull('email_verified_at');
                }),
                AllowedFilter::callback('banned', function ($query, $value) {
                    $value = boolval($value);
                    $value ? $query->whereNotNull('banned_at') : $query->whereNull('banned_at');
                }),
                AllowedFilter::trashed(),
            ])
            ->allowedSorts([
                'created_at',
                'name',
                'email_verified_at',
                'banned_at',
            ])
            ->defaultSort('-created_at')
            ->paginate(config('pagination.users_per_page'))
            ->withQueryString();

    }
}
