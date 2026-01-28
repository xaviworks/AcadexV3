<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetSuccess extends Notification
{
    use Queueable;

    protected $resetAt;
    protected $ipAddress;
    protected $userAgent;

    /**
     * Create a new notification instance.
     */
    public function __construct($resetAt, $ipAddress, $userAgent)
    {
        $this->resetAt = $resetAt;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
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
        return (new MailMessage)
            ->subject('Password Reset Successful - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->full_name . ',')
            ->line('Your password was successfully reset.')
            ->line('**Reset Details:**')
            ->line('• **Time:** ' . $this->resetAt->format('F d, Y h:i A'))
            ->line('• **IP Address:** ' . $this->ipAddress)
            ->line('• **Device:** ' . $this->userAgent)
            ->line('If you did not make this change, please contact your administrator immediately and secure your account.')
            ->action('Login to Your Account', route('login'))
            ->line('Thank you for using ' . config('app.name') . '!');
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
