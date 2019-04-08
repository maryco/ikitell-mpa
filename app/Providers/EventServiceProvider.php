<?php

namespace App\Providers;

use App\Listeners\MarkDeviceReported;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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
                function ($query) {
                    if (preg_match('/`jobs`/', $query->sql)) {
                        /**
                         * NOTE: Prevent logging 'select ..jobs...' query endless.
                         * Append also table 'failed_jobs' if you need.
                         */
                        return;
                    }

                    Log::debug('SQL [%query] [%bindings] [%time]', [
                        '%query' => $query->sql,
                        '%bindings' => $query->bindings,
                        '%time' => $query->time
                    ]);
                }
            );
        }
    }
}
