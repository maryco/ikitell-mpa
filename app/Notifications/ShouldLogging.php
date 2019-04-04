<?php
namespace App\Notifications;


use App\Models\Entities\NotificationLog;

interface ShouldLogging
{
    /**
     * Logging the notification detail.
     *
     * @param $notifiable
     * @param int $jobStatus (See available values at 'NotificationLog')
     *
     * @throws \Throwable
     * @see NotificationLog
     */
    public function putLog($notifiable, $jobStatus);
}
