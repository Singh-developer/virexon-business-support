<?php

namespace App\Notifications;

use App\Models\AgentDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentUploadedNotification extends Notification
{
    use Queueable;

    public function __construct(public AgentDocument $document, public string $agentName) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'message'       => "{$this->agentName} uploaded a new document: " . AgentDocument::typeLabel($this->document->document_type),
            'agent_id'      => $this->document->user_id,
            'document_id'   => $this->document->id,
            'document_type' => $this->document->document_type,
            'url'           => route('admin.documents.index', $this->document->user_id),
        ];
    }
}