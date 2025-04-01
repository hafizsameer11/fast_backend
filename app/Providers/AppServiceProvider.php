<?php

namespace App\Providers;
use App\Repositories\WithdrawalRepository;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(WithdrawalRepository::class, function ($app) {
            return new WithdrawalRepository(new \App\Models\Withdrawal());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
