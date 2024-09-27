<?php

namespace App\Providers;

use App\Models\Stepper\Order;
use App\Models\Stepper\StockOrder;
use App\Observers\OrderObserver;
use App\Observers\StockOrderObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        StockOrder::observe(StockOrderObserver::class);
        Order::observe(OrderObserver::class);

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
