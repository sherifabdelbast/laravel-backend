<?php

namespace App\Notifications;

use App\Models\RequestHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    protected string $identifyNumber;

    /**
     * Create a new notification instance.
     */
    public function __construct(?string $identifyNumber)
    {
        $this->identifyNumber = $identifyNumber ?? Str::uuid()->toString();
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
        $token = bcrypt(Str::random(10));
        RequestHistory::query()
            ->create([
                'identify_number' => $this->identifyNumber,
                'token' => $token,
                'expired_at' => now()->addMinutes(15)
            ]);

        $url = 'https://taskat-app.netlify.app';
        $verificationUrl = "$url/complete-registration/?token={$token}";
        return (new MailMessage)
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If you did not create an account, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}