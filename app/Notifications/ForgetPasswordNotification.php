<?php

namespace App\Notifications;

use App\Models\RequestForgetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForgetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected  $identifyNumber;
    public function __construct($identifyNumber)
    {
        $this->identifyNumber = $identifyNumber;
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
        $identifyNumber = $this->identifyNumber;

        $randomCode = rand(10000,99999);
        RequestForgetPassword::query()
        ->create([
            'identify_number'=>$identifyNumber,
            'code' => $randomCode,
            'expired_at' => now()->addMinutes(15)
        ]);

            return (new MailMessage)
                ->line('You have requested to reset your password. To complete the password reset process, please use the following verification code')
                ->line('Your code is:'. $randomCode)
                ->line('If you did not request a password reset, please disregard this email. Your account security is important to us .');
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
