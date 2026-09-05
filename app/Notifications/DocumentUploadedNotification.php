<?php

namespace App\Notifications;

use App\Models\AgentDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentUploadedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }
    public function __construct(public AgentDocument $document, public string $agentName) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    public function via($notifiable): array
    {
        return ['mail'];
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    public function toArray($notifiable): array
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $typeLabel = AgentDocument::typeLabel($this->document->document_type);
        return [
            //
            'message'       => "{$this->agentName} uploaded: {$typeLabel}",
            'agent_id'      => $this->document->user_id,
            'document_id'   => $this->document->id,
            'document_type' => $this->document->document_type,
            'url'           => route('admin.documents.index', $this->document->user_id),
        ];
    }
}
