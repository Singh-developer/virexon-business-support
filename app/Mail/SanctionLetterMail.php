<?php

namespace App\Mail;

use App\Models\SanctionLetter;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class SanctionLetterMail extends Mailable
{
    use Queueable, SerializesModels;

    public SanctionLetter $letter;
    public User $agent;
    public string $pdfBinary;
    public string $pdfFilename;

    public function __construct(
        SanctionLetter $letter,
        User $agent,
        string $pdfBinary,
        string $pdfFilename
    ) {
        $this->letter = $letter;
        $this->agent = $agent;
        $this->pdfBinary = $pdfBinary;
        $this->pdfFilename = $pdfFilename;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->letter->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sanction-letter',
            with: [
                'letter' => $this->letter,
                'agent' => $this->agent,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => $this->pdfBinary,
                $this->pdfFilename
            )->withMime('application/pdf'),
        ];
    }
}
