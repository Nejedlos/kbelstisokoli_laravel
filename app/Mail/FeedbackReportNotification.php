<?php

namespace App\Mail;

use App\Models\FeedbackReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class FeedbackReportNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public FeedbackReport $report
    ) {
        $this->onQueue(config('feedback.notifications.queue', 'default'));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[KS FEEDBACK #{$this->report->id}] {$this->report->title}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.feedback-report',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->report->screenshot_path && Storage::exists($this->report->screenshot_path)) {
            $attachments[] = Attachment::fromStorage($this->report->screenshot_path)
                ->as('screenshot.jpg')
                ->withMime('image/jpeg');
        }

        if ($this->report->logs_path && Storage::exists($this->report->logs_path)) {
            $attachments[] = Attachment::fromStorage($this->report->logs_path)
                ->as('console-logs.json')
                ->withMime('application/json');
        }

        return $attachments;
    }
}
