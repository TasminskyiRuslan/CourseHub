<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Listeners\StripeCheckoutCompletedListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Events\WebhookReceived;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StripeClient::class, function () {
            return new StripeClient(config('cashier.secret'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole(UserRole::SUPER_ADMIN->value) ? true : null;
        });

        Event::listen(
            WebhookReceived::class,
            StripeCheckoutCompletedListener::class
        );
    }
}
