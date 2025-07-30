<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationNotification extends Notification
{
    use Queueable;

    protected string $inviteIdentify;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $inviteIdentify)
    {
        $this->inviteIdentify = $inviteIdentify;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = 'https://www.taskat.approved.tech';
        $invitationUrl = "$url/invitation/?invite_identify={$this->inviteIdentify}";
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', $invitationUrl)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
