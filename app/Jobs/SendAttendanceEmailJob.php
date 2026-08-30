<?php

namespace App\Jobs;

use App\Mail\AttendanceReminderMail;
use App\Mail\AttendanceSummaryMail;
use App\Models\AttendanceEmailDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendAttendanceEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 60;

    public array $backoff = [30, 120, 300, 900];

    public function __construct(public int $deliveryId) {}

    public function handle(): void
    {
        $delivery = AttendanceEmailDelivery::with(['user', 'attendable'])->findOrFail($this->deliveryId);
        $preference = $delivery->kind === 'summary' ? 'attendance_summaries' : 'attendance_reminders';

        if ($delivery->sent_at || $delivery->status !== 'pending') {
            return;
        }

        if (! $delivery->user || ! $delivery->attendable || ! $delivery->user->prefersNotification($preference)) {
            $delivery->update(['status' => 'skipped']);

            return;
        }

        $locale = in_array($delivery->user->preferred_locale, ['cs', 'en'], true)
            ? $delivery->user->preferred_locale
            : config('app.fallback_locale', 'cs');

        $previousLocale = App::currentLocale();
        App::setLocale($locale);

        try {
            $mail = $delivery->kind === 'summary'
                ? new AttendanceSummaryMail($delivery)
                : new AttendanceReminderMail($delivery);

            Mail::to($delivery->user)->send($mail);
        } finally {
            App::setLocale($previousLocale);
        }

        $delivery->update([
            'status' => 'sent',
            'sent_at' => now(),
            'attempts' => $this->attempts(),
            'last_error' => null,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        AttendanceEmailDelivery::whereKey($this->deliveryId)->update([
            'status' => 'failed',
            'attempts' => $this->attempts(),
            'last_error' => mb_substr($exception->getMessage(), 0, 2000),
        ]);
    }
}
