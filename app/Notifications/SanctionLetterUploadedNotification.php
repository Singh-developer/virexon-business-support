<?php

namespace App\Notifications;

use App\Models\SanctionLetter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SanctionLetterUploadedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SanctionLetter $letter, public string $agentName) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message'     => "{$this->agentName} uploaded a signed Sanction Letter {$this->letter->sanction_letter_no}. Pending your review.",
            'sanction_id' => $this->letter->id,
            'status'      => 'under_review',
            'url'         => route('sanctions.review', $this->letter),
        ];
    }
}