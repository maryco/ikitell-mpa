<?php

namespace App\Notifications;

use App\Models\Entities\ConcernMessage;
use App\Models\Entities\NotificationLog;
use App\Models\Entities\User;
use ArrayObject;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Throwable;

class AlertNotification extends Notification implements ShouldQueue, ShouldLogging
{
    use Queueable;

    /**
     * The queue name for a 'job.queue'
     */
    public const QUEUE_NAME = 'default'; //'alert';

    /**
     * The key of the view.
     *
     * @var string
     */
    private string $view;

    /**
     * The subject text.
     *
     * @var string
     */
    private string $subject;

    /**
     * The parameters for the mail body.
     * @see ConcernMessage
     *
     * @var array<string, mixed>
     */
    private array $content;

    /**
     * The Alert of cause of this notification.
     *
     * @var ArrayObject<mixed>|null
     */
    private ?ArrayObject $alert;

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
     * @param $notifiable
     * @return array|string
     */
    public function via($notifiable): array|string
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->subject)
            ->markdown($this->view, $this->toArray($notifiable));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
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
     * @param ArrayObject $alert
     */
    public function setAlert(ArrayObject $alert): void
    {
        $this->alert = $alert;
    }

    /**
     * Get the alert identifier.
     *
     * @return ArrayObject|null
     */
    public function getAlert(): ?ArrayObject
    {
        return $this->alert;
    }

    /**
     * Render the mail as HTML.
     *
     * @param $notifiable
     * @param ?bool $disableLink (Replace '#' all included href attribute.)
     * @return mixed
     */
    public function renderAsMarkdown($notifiable, bool $disableLink = true): mixed
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
     * @throws Throwable
     */
    public function renderAsText($notifiable): string
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
     * @inheritDoc
     */
    public function putLog(mixed $notifiable, int $jobStatus): void
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
