<?php

namespace App\Notifications;

use App\Models\Entities\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class DeviceResumedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var Device
     */
    protected $device;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($device)
    {
        $this->device = $device;
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
            ->subject(__('email.subject.device_resumed'))
            ->action(
                __('email.action.show_device'),
                route('device.edit', ['id' => $this->device->id])
            )
            ->markdown('emails.device.resumed', ['device' => $this->device]);
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
            //
        ];
    }
}
