<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecruitmentFormMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $teamName,
        public string $messageBody,
        public string $subjectText,
        public array $extraData = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            replyTo: [$this->senderEmail => $this->senderName],
            subject: $this->subjectText,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recruitment-form',
            with: [
                'senderName' => $this->senderName,
                'senderEmail' => $this->senderEmail,
                'teamName' => $this->teamName,
                'messageBody' => $this->messageBody,
                'extraData' => $this->extraData,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
