<?php

namespace App\Notifications;

use App\Models\Entities\Contact;
use App\Models\Entities\NotificationLog;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Throwable;

class VerifyRequestContactsNotification extends VerifyEmail implements ShouldQueue, ShouldLogging
{
    use Queueable;

    /**
     * The queue name for 'jobs.queue'
     */
    public const QUEUE_NAME = 'default'; //'verify-request';

    /**
     * The view for mail
     */
    public const VIEW = 'emails.contact.verify';

    /**
     * @var Contact
     */
    private Contact $contact;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($contact)
    {
        $this->contact = $contact;
        $this->queue = self::QUEUE_NAME;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @inheritDoc
     */
    public function via($notifiable): array|string
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @inheritDoc
     */
    public function toMail($notifiable): MailMessage
    {
        if ($notifiable instanceof Contact) {
            $verifyUrl = $this->verificationUrl($notifiable);
        } else {
            $verifyUrl = url('/');
        }

        return (new MailMessage())
            ->subject(__('email.subject.verify_email_contacts'))
            ->action(__('email.action.do_verify_contacts'), $verifyUrl)
            ->markdown(
                self::VIEW,
                [
                    'contact' => $this->contact,
                    'user' => $this->contact->user,
                    'isCopy' => $notifiable->email === $this->contact->user?->email
                ]
            );
    }

    /**
     * @param mixed $notifiable
     * @return string
     * @see VerifyEmail::verificationUrl
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'notice.address.verify',
            Carbon::now()->addMinutes(config('specs.verify_limit.contacts')),
            ['id' => $notifiable->getKey()]
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'contact' => $this->contact,
            'user' => $this->contact->user,
            'isCopy' => $notifiable->email === $this->contact->user->email,
            'actionText' => __('email.action.do_verify_contacts'),
            'actionUrl' => '#'
        ];
    }

    /**
     * Render the mail as HTML.
     *
     * @param mixed $notifiable
     * @param ?bool $disableLink (Replace '#' all included href attribute.)
     * @return mixed
     */
    public function renderAsMarkdown(mixed $notifiable, bool $disableLink = true): mixed
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
            self::VIEW,
            $this->toArray($notifiable)
        )->render();
    }

    /**
     * Logging the notification detail.
     *
     * @inheritDoc
     */
    public function putLog(mixed $notifiable, int $jobStatus): void
    {
        if (!($notifiable instanceof Contact)) {
            return;
        }

        NotificationLog::create([
            'contact_id' => $this->contact->id,
            'job_status' => $jobStatus,
            'email' => $notifiable->email,
            'name' => $notifiable->name,
            'content' => $this->renderAsText($notifiable),
        ])->save();
    }
}
