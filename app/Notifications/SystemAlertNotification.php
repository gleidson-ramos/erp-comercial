<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        private array $alert
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'alert_key' => $this->alert['alert_key'],
            'type' => $this->alert['type'],
            'title' => $this->alert['title'],
            'message' => $this->alert['message'],
            'record_id' => $this->alert['record_id'] ?? null,
            'url' => $this->alert['url'] ?? null,
            'occurred_at' => now()->toDateTimeString(),
        ];
    }
}