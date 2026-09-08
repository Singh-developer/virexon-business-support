<?php

namespace App\Mail;

use App\Models\AssertionLetter;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class AssertionLetterMail extends Mailable
{
    use Queueable, SerializesModels;

    public AssertionLetter $letter;
    public User $agent;
    public string $pdfBinary;
    public string $pdfFilename;

    public function __construct(
        AssertionLetter $letter,
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
            view: 'emails.assertion-letter',
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
