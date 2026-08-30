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

class AttendanceSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AttendanceEmailDelivery $delivery, public ?array $previewGroups = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('attendance_mail.summary.subject', ['team' => $this->teamNames()]));
    }

    public function content(): Content
    {
        $service = app(AttendanceEmailService::class);
        $event = $this->delivery->attendable;
        $players = $service->summaryRoster($event);
        $responses = $event->attendances()->whereIn('user_id', $players->pluck('id'))->get()->keyBy('user_id');
        $groups = ['confirmed' => collect(), 'declined' => collect(), 'maybe' => collect(), 'pending' => collect()];

        foreach ($players as $player) {
            $status = $responses->get($player->id)?->planned_status;
            $status = is_string($status) && array_key_exists($status, $groups) ? $status : 'pending';
            $groups[$status]->push($player);
        }

        if ($this->previewGroups !== null) {
            $groups = $this->previewGroups;
        }

        return new Content(view: 'emails.attendance-summary', with: [
            'event' => $event,
            'startsAt' => $service->startsAt($event),
            'teamNames' => $this->teamNames(),
            'eventName' => $event->title ?? $event->display_name ?? ($event->opponent ? __('attendance_mail.reminder.match_against', ['opponent' => $event->opponent->name]) : __('attendance_mail.reminder.event_fallback')),
            'groups' => $groups,
            'detailUrl' => route('member.attendance.show', ['type' => $service->typeFor($event), 'id' => $event->id]),
            'unsubscribeUrl' => URL::temporarySignedRoute('attendance.email.unsubscribe', now()->addDays(30), ['user' => $this->delivery->user_id, 'preference' => 'attendance_summaries']),
        ]);
    }

    private function teamNames(): string
    {
        return app(AttendanceEmailService::class)->teams($this->delivery->attendable)->pluck('name')->join(', ');
    }
}
