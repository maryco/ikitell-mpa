<?php
namespace App\Notifications;


use App\Models\Entities\NotificationLog;
use Throwable;

interface ShouldLogging
{
    /**
     * Logging the notification detail.
     *
     * @param mixed $notifiable
     * @param int $jobStatus (See available values at 'NotificationLog')
     *
     * @throws Throwable
     * @see NotificationLog
     */
    public function putLog(mixed $notifiable, int $jobStatus);
}
