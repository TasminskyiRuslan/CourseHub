<?php

declare(strict_types=1);

namespace App\Queries;

use Illuminate\Http\Request;

abstract readonly class BaseQuery
{
    /**
     * Check if the request qualifies for caching.
     */
    protected function shouldUseCache(Request $request): bool
    {
        return ! $request->hasAny(['filter', 'sort', 'include', 'page']);
    }
}
