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
            subject: "Zpětná vazba č. {$this->report->id} | {$this->report->title}",
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

        if ($this->report->screenshot_path && Storage::disk('local')->exists($this->report->screenshot_path)) {
            $attachments[] = Attachment::fromStorageDisk('local', $this->report->screenshot_path)
                ->as('screenshot.jpg')
                ->withMime('image/jpeg');
        }

        if ($this->report->logs_path && Storage::disk('local')->exists($this->report->logs_path)) {
            $attachments[] = Attachment::fromStorageDisk('local', $this->report->logs_path)
                ->as('console-logs.json')
                ->withMime('application/json');
        }

        if ($this->report->network_path && Storage::disk('local')->exists($this->report->network_path)) {
            $attachments[] = Attachment::fromStorageDisk('local', $this->report->network_path)
                ->as('network-logs.json')
                ->withMime('application/json');
        }

        if ($this->report->dom_path && Storage::disk('local')->exists($this->report->dom_path)) {
            $attachments[] = Attachment::fromStorageDisk('local', $this->report->dom_path)
                ->as('dom-snapshot.html')
                ->withMime('text/html');
        }

        if ($this->report->breadcrumbs_path && Storage::disk('local')->exists($this->report->breadcrumbs_path)) {
            $attachments[] = Attachment::fromStorageDisk('local', $this->report->breadcrumbs_path)
                ->as('breadcrumbs.json')
                ->withMime('application/json');
        }

        if ($this->report->clicks_path && Storage::disk('local')->exists($this->report->clicks_path)) {
            $attachments[] = Attachment::fromStorageDisk('local', $this->report->clicks_path)
                ->as('clicks.json')
                ->withMime('application/json');
        }

        return $attachments;
    }
}
