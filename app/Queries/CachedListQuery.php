<?php

namespace App\Queries;

use Illuminate\Http\Request;

readonly abstract class CachedListQuery
{
    /**
     * Check if the request qualifies for caching.
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldUseCache(Request $request): bool
    {
        return !$request->hasAny(['filter', 'sort', 'include']);
    }
}
