<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SendOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $code,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['log'];
    }

    /**
     * Deliver the OTP over the log channel.
     *
     * In production this should be replaced by an SMS or WhatsApp gateway.
     *
     * @return array<string, string>
     */
    public function toLog(AnonymousNotifiable $notifiable): array
    {
        Log::info("OTP for {$notifiable->routeNotificationFor('log')}", ['code' => $this->code]);

        return ['code' => $this->code];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'code' => $this->code,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your '.config('app.name').' OTP')
            ->line("Your one-time password is {$this->code}. It expires in 5 minutes.");
    }
}
