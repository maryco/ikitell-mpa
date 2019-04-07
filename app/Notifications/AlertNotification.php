<?php

namespace App\Notifications;

use App\Models\Entities\ConcernMessage;
use App\Models\Entities\NotificationLog;
use App\Models\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class AlertNotification extends Notification implements ShouldQueue, ShouldLogging
{
    use Queueable;

    /**
     * The queue name for a job.queue
     */
    const QUEUE_NAME = 'default';//'alert';

    /**
     * The key of the view.
     *
     * @var string
     */
    private $view;

    /**
     * The subject text.
     *
     * @var string
     */
    private $subject;

    /**
     * The parameters for the mail body.
     * @see ConcernMessage
     *
     * @var
     */
    private $content;

    /**
     * The Alert of cause of this notification.
     *
     * @var
     */
    private $alert;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($view, $subject, $content)
    {
        $this->view = $view;
        $this->subject = $subject;
        $this->content = $content;

        $this->queue = self::QUEUE_NAME;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject($this->subject)
            ->markdown($this->view, $this->toArray($notifiable));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'content' => $this->content,
            'to' => [
                'name' => $notifiable->name ?: __('email.unknown_name'),
                'email' => $notifiable->email,
            ],
        ];
    }

    /**
     * Set the alert identifier.
     *
     * @param $id
     */
    public function setAlert($alert)
    {
        $this->alert = $alert;
    }

    /**
     * Get the alert identifier.
     *
     * @return int|null
     */
    public function getAlert()
    {
        return $this->alert;
    }

    /**
     * Render the mail as HTML.
     *
     * @param $notifiable
     * @param $disableLink (Replace '#' all included href attribute.)
     * @return mixed
     */
    public function renderAsMarkdown($notifiable, $disableLink = true)
    {
        $view = app(Markdown::class)->render(
            $this->toMail($notifiable)->markdown,
            $this->toArray($notifiable)
        );

        return ($disableLink) ? preg_replace('/href=\".+\"/', 'href="#"', $view) : $view;
    }

    /**
     * Render the mail view as Text
     *
     * @param $notifiable
     * @return string
     *
     * @throws \Throwable
     */
    public function renderAsText($notifiable)
    {
        return view(
            $this->view,
            $this->toArray($notifiable)
        )->render();
    }

    /**
     * Logging the alert detail and notification detail.
     * NOTE: Logging only for the notice for the Contacts.
     *
     * @param $notifiable (User|Contact)
     * @param int $jobStatus
     *
     * @see \App\Notifications\ShouldLogging::putLog
     * @throws \Throwable
     */
    public function putLog($notifiable, $jobStatus)
    {
        if ($notifiable instanceof User) {
            return;
        }

        if (!$this->alert) {
            return;
        }

        NotificationLog::create([
            'alert_id' => $this->alert->id,
            'device_id' => $this->alert->device_id,
            'job_status' => $jobStatus,
            'notify_count' => $this->alert->notify_count,
            'email' => $notifiable->email,
            'name' => $notifiable->name ?: __('email.unknown_name'),
            'content' => $this->renderAsText($notifiable),
        ])->save();
    }
}
