<?php

namespace App\Providers;

use App\Models\Entities\NotificationLog;
use App\Notifications\AlertNotification;
use App\Notifications\ShouldLogging;
use App\Notifications\VerifyRequestContactsNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The queue names of the logging target notification.
     *
     * @var array
     */
    private $loggingQueue = [
        AlertNotification::QUEUE_NAME,
        VerifyRequestContactsNotification::QUEUE_NAME,
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Queue::after(function (JobProcessed $event) {

            list($loggingNotification, $notifiable) = $this->restoreShouldLoggingNotification($event);

            if ($loggingNotification) {
                $loggingNotification->putLog($notifiable, NotificationLog::JOB_STATUS_EXECUTED);
            }
        });

        // TODO: Test
        Queue::failing(function (JobFailed $event) {

            list($loggingNotification, $notifiable) = $this->restoreShouldLoggingNotification($event);

            if ($loggingNotification) {
                Log::debug(
                    'Failed queue job [%notifiable]',
                    ['%notifiable' => $notifiable]
                );
                $loggingNotification->putLog($notifiable, NotificationLog::JOB_STATUS_FAILED);
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Restore the logging notification instance from event
     * FIXME: It's seems 'UNKODE', need to refactor to more safe.
     * @see \Illuminate\Notifications\SendQueuedNotifications
     *
     * @param $event
     * @return array
     */
    private function restoreShouldLoggingNotification($event)
    {
        $empty = [null, null];

        if (!in_array($event->job->getQueue(), $this->loggingQueue)) {
            Log::debug('Not logging target Queue. []', ['' => $event->job->getQueue()]);
            return $empty;
        }

        $payload = $event->job->payload();
        $command = unserialize($payload['data']['command']);

        if (!$command || !($command instanceof SendQueuedNotifications)) {
            return $empty;
        }

        if (!($command->notification instanceof ShouldLogging)) {
            Log::debug(
                'Insetance is not implements of ShouldLogging [%job]',
                ['%job' => $event->job->getJobId()]
            );
            return $empty;
        }

        if (!$command->notifiables) {
            Log::warning(
                'Notifiable is not found [%job]',
                ['%job' => $event->job->getJobId()]
            );
            return $empty;
        }

        if (!($command->notifiables instanceof Model)) {
            Log::warning(
                'Notifiable is not a Model [%job]',
                ['%job' => $event->job->getJobId()]
            );
            return $empty;
        }

        return [
            $command->notification,
            $command->notifiables
        ];
    }
}
