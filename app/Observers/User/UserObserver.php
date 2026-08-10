<?php

declare(strict_types=1);

namespace App\Observers\User;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    /**
     * Flush the cache when a new user is created.
     */
    public function created(User $user): void
    {
        $this->flushCache();
    }

    /**
     * Flush the cache when a user is updated.
     */
    public function updated(User $user): void
    {
        $this->flushCache();
    }

    /**
     * Remove associated courses before the user is removed.
     */
    public function deleting(User $user): void
    {
        $user->courses()->get()->each->delete();
    }

    /**
     * Flush the cache when a user is deleted.
     */
    public function deleted(User $user): void
    {
        $this->flushCache();
    }

    /**
     * Handle the pivot sync event (specifically for Spatie roles).
     */
    public function pivotSynced(User $user, string $relation, array $pivotIds): void
    {
        if ($relation === 'roles') {
            $this->flushCache();
        }
    }

    /**
     * Handle the pivot attach event.
     */
    public function pivotAttached(User $user, string $relation, array $pivotIds): void
    {
        if ($relation === 'roles') {
            $this->flushCache();
        }
    }

    /**
     * Handle the pivot detach event.
     */
    public function pivotDetached(User $user, string $relation, array $pivotIds): void
    {
        if ($relation === 'roles') {
            $this->flushCache();
        }
    }

    /**
     * Flush the user list cache.
     */
    protected function flushCache(): void
    {
        Cache::tags([
            config('cache.tags.teacher_list'),
            config('cache.tags.course_list'),
        ])->flush();
    }
}
