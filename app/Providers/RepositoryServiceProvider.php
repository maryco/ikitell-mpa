<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Models\Repositories\UserRepositoryInterface::class,
            \App\Models\Repositories\UserRepository::class
        );
        $this->app->bind(
            \App\Models\Repositories\DeviceRepositoryInterface::class,
            \App\Models\Repositories\DeviceRepository::class
        );
        $this->app->bind(
            \App\Models\Repositories\RuleRepositoryInterface::class,
            \App\Models\Repositories\RuleRepository::class
        );
        $this->app->bind(
            \App\Models\Repositories\ContactRepositoryInterface::class,
            \App\Models\Repositories\ContactRepository::class
        );
        $this->app->bind(
            \App\Models\Repositories\AlertRepositoryInterface::class,
            \App\Models\Repositories\AlertRepository::class
        );
        $this->app->bind(
            \App\Models\Repositories\MessageRepositoryInterface::class,
            \App\Models\Repositories\MessageRepository::class
        );
    }
}
