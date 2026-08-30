<?php

namespace App\Mail;

use App\Models\AttendanceEmailDelivery;
use App\Services\Attendance\AttendanceEmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class AttendanceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AttendanceEmailDelivery $delivery) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('attendance_mail.reminder.subject', ['team' => $this->teamNames()]));
    }

    public function content(): Content
    {
        $service = app(AttendanceEmailService::class);
        $event = $this->delivery->attendable;
        $params = ['user' => $this->delivery->user_id, 'type' => $service->typeFor($event), 'event' => $event->id];

        return new Content(view: 'emails.attendance-reminder', with: [
            'user' => $this->delivery->user,
            'event' => $event,
            'startsAt' => $service->startsAt($event),
            'teamNames' => $this->teamNames(),
            'eventName' => $this->eventName(),
            'yesUrl' => URL::temporarySignedRoute('attendance.email.respond', now()->addDays(10), $params + ['status' => 'confirmed']),
            'noUrl' => URL::temporarySignedRoute('attendance.email.respond', now()->addDays(10), $params + ['status' => 'declined']),
            'detailUrl' => route('member.attendance.show', ['type' => $params['type'], 'id' => $event->id]),
            'unsubscribeUrl' => URL::temporarySignedRoute('attendance.email.unsubscribe', now()->addDays(30), ['user' => $this->delivery->user_id, 'preference' => 'attendance_reminders']),
        ]);
    }

    private function teamNames(): string
    {
        return app(AttendanceEmailService::class)->teams($this->delivery->attendable)->pluck('name')->join(', ');
    }

    private function eventName(): string
    {
        $event = $this->delivery->attendable;

        return $event->title ?? $event->display_name ?? ($event->opponent ? __('attendance_mail.reminder.match_against', ['opponent' => $event->opponent->name]) : __('attendance_mail.reminder.event_fallback'));
    }
}
