<?php

namespace App\Observers\User;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    /**
     * Flush the cache when a new user is created.
     *
     * @param User $user
     * @return void
     */
    public function created(User $user): void
    {
        $this->flushUserCache();
    }

    /**
     * Flush the cache when a user is updated.
     *
     * @param User $user
     * @return void
     */
    public function updated(User $user): void
    {
        $this->flushUserCache();
    }

    /**
     * Remove associated courses before the user is removed.
     *
     * @param User $user
     * @return void
     */
    public function deleting(User $user): void
    {
        $user->courses()->get()->each->delete();
    }

    /**
     * Flush the cache when a user is deleted.
     *
     * @param User $user
     * @return void
     */
    public function deleted(User $user): void
    {
        $this->flushUserCache();
    }

    /**
     * Handle the pivot sync event (specifically for Spatie roles).
     *
     * @param User $user
     * @param string $relation
     * @param array $pivotIds
     * @return void
     */
    public function pivotSynced(User $user, string $relation, array $pivotIds): void
    {
        if ($relation === 'roles') {
            $this->flushUserCache();
        }
    }

    /**
     * Handle the pivot attach event.
     *
     * @param User $user
     * @param string $relation
     * @param array $pivotIds
     * @return void
     */
    public function pivotAttached(User $user, string $relation, array $pivotIds): void
    {
        if ($relation === 'roles') {
            $this->flushUserCache();
        }
    }

    /**
     * Handle the pivot detach event.
     *
     * @param User $user
     * @param string $relation
     * @param array $pivotIds
     * @return void
     */
    public function pivotDetached(User $user, string $relation, array $pivotIds): void
    {
        if ($relation === 'roles') {
            $this->flushUserCache();
        }
    }

    /**
     * Flush the user list cache.
     *
     * @return void
     */
    protected function flushUserCache(): void
    {
        Cache::tags([config('cache.tags.teacher_list')])->flush();
    }
}
