<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AppDatabaseNotification extends Notification
{
    /**
     * @param  array{title: string, body: string, href: string, kind: string, meta?: array<string, mixed>}  $payload
     */
    public function __construct(public array $payload) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->payload['title'],
            'body' => $this->payload['body'],
            'href' => $this->payload['href'],
            'kind' => $this->payload['kind'],
            'meta' => $this->payload['meta'] ?? [],
        ];
    }
}
