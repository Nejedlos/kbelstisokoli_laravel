<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ErrorMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Kompletní data pro report chyby.
     */
    public array $report;

    /**
     * Vytvoření nové instance s reportem.
     */
    public function __construct(array $report)
    {
        $this->report = $report;
    }

    /**
     * Předmět e-mailu dynamicky z dat.
     */
    public function envelope(): Envelope
    {
        $app = $this->report['app'] ?? [];
        $exception = $this->report['exception'] ?? [];
        $subject = sprintf(
            '[%s][%s] %s (%s:%s)',
            $app['name'] ?? 'App',
            $app['env'] ?? 'env',
            $exception['class'] ?? 'Exception',
            $exception['file'] ?? 'file',
            $exception['line'] ?? 'line'
        );

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Obsah e-mailu (Markdown šablona).
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.error-report',
            with: [
                'report' => $this->report,
            ],
        );
    }

    /**
     * Přílohy (nepoužito).
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
