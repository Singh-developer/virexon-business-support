<?php

namespace App\Notifications;

use App\Models\SanctionLetter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SanctionLetterReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(public SanctionLetter $letter) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $approved = $this->letter->review_status === 'approved';

        $message = $approved
            ? "Your Sanction Letter {$this->letter->sanction_letter_no} has been Approved. Workflow complete."
            : "Your signed Sanction Letter {$this->letter->sanction_letter_no} was not accepted. Please upload a new signed PDF.";

        if (!empty($this->letter->review_comment)) {
            $message .= ' Note: ' . $this->letter->review_comment;
        }

        return [
            'message'     => $message,
            'sanction_id' => $this->letter->id,
            'status'      => $this->letter->review_status,
            'needs_action'=> $approved ? false : true,
            'url'         => route('agent.sanctions.show', $this->letter),
        ];
    }
}