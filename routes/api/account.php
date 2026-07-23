<?php

use App\Http\Controllers\Api\User\Account\AccountAvatarController;
use App\Http\Controllers\Api\User\Account\AccountController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Account actions
|--------------------------------------------------------------------------
*/
Route::prefix('account')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        // Show auth action
        Route::get('/', [AccountController::class, 'show'])
            ->name('account.show');

        // Update auth action
        Route::patch('/', [AccountController::class, 'update'])
            ->middleware(['restrict.banned.user'])
            ->name('account.update');

        // Update auth avatar action
        Route::put('/avatar', [AccountAvatarController::class, 'update'])
            ->middleware(['restrict.banned.user'])
            ->name('account.avatar.update');

        // Delete auth avatar action
        Route::delete('/avatar', [AccountAvatarController::class, 'destroy'])
            ->middleware(['restrict.banned.user'])
            ->name('account.avatar.destroy');
    });
