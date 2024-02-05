<?php

namespace App\Providers;

use App\Listeners\MarkDeviceReported;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Verified::class => [
            MarkDeviceReported::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        if (Config::get('app.db_debug_query')) {
            DB::listen(
                static function ($query) {
                    /**
                     * NOTE: Prevent too many logging for a jobs table.
                     * Append also table 'failed_jobs' if you need.
                     */
                    if (
                        app()->environment('production')
                        && str_contains($query->sql, 'jobs')
                    ) {
                        return;
                    }

                    Log::channel('sql')->debug(
                        $query->sql,
                        [
                            'bindings' => $query->bindings,
                            'time' => $query->time
                        ]
                    );
                }
            );
        }
    }
}
