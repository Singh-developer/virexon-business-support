<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AgentRegisteredNotification extends Notification
{
    use Queueable;

    protected $agent;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $agent)
    {
        $this->agent = $agent;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'agent_id' => $this->agent->id,
            'name' => $this->agent->name,
            'message' => 'New agent ' . $this->agent->name . ' has completed their registration application.'
        ];
    }
}
