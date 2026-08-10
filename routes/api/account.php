<?php

declare(strict_types=1);

use App\Http\Controllers\Api\User\Account\AccountAvatarController;
use App\Http\Controllers\Api\User\Account\AccountController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Account Routes
|--------------------------------------------------------------------------
*/
Route::prefix('account')
    ->middleware(['auth:sanctum'])
    ->group(function (): void {
        Route::get('/', [AccountController::class, 'show'])
            ->name('account.show');

        Route::patch('/', [AccountController::class, 'update'])
            ->middleware(['restrict.banned.user'])
            ->name('account.update');

        Route::put('/avatar', [AccountAvatarController::class, 'update'])
            ->middleware(['restrict.banned.user'])
            ->name('account.avatar.update');

        Route::delete('/avatar', [AccountAvatarController::class, 'destroy'])
            ->middleware(['restrict.banned.user'])
            ->name('account.avatar.destroy');
    });
