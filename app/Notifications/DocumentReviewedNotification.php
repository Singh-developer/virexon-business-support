<?php

namespace App\Notifications;

use App\Models\AgentDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(public AgentDocument $document) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $typeLabel = AgentDocument::typeLabel($this->document->document_type);
        $status    = ucfirst($this->document->status);

        $msg = match($this->document->status) {
            'approved'      => "Your {$typeLabel} has been approved!",
            'rejected'      => "Your {$typeLabel} was rejected. Please re-upload.",
            're_upload'     => "Please re-upload your {$typeLabel}.",
            'form_received' => $this->document->admin_note ?? 'Your form has been received! Please upload your documents.',
            default         => "Your {$typeLabel} status was updated to {$status}.",
        };

        return [
            'message'       => $msg,
            'document_id'   => $this->document->id,
            'document_type' => $this->document->document_type,
            'status'        => $this->document->status,
            'admin_note'    => $this->document->admin_note,
            'url'           => route('documents.index'),
        ];
    }
}
