<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;

class LogChannel
{
    /**
     * Send the given notification.
     *
     * @return array<string, mixed>
     */
    public function send(AnonymousNotifiable $notifiable, Notification $notification): array
    {
        return method_exists($notification, 'toLog')
            ? $notification->toLog($notifiable)
            : [];
    }
}
