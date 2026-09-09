<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LeadNotificationMail extends Mailable
{
    public function __construct(public Lead $lead, public bool $assignmentNotice = false) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->lead->email, $this->lead->name)],
            subject: $this->assignmentNotice
                ? __('leads.mail.assigned_subject', ['name' => $this->lead->name])
                : __('leads.mail.new_subject', ['name' => $this->lead->name]),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.lead-notification');
    }
}
