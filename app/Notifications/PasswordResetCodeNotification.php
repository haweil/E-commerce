<?php

namespace App\Notifications;

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetCodeNotification extends Notification
{
    private $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Password Reset Code')
            ->line('Your password reset code is: ' . $this->code)
            ->line('This code will expire in 30 minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
