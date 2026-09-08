<?php

namespace App\Services;

use App\Mail\LeadNotificationMail;
use App\Models\Lead;
use App\Models\LeadRoutingSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LeadWorkflowService
{
    public function create(array $attributes): Lead
    {
        $responsible = $this->defaultResponsible();
        $lead = Lead::create(array_merge($attributes, [
            'status' => $attributes['status'] ?? 'new',
            'responsible_user_id' => $responsible?->id,
            'assigned_at' => $responsible ? now() : null,
            'status_changed_at' => now(),
        ]));

        $this->notify($lead);

        return $lead;
    }

    public function assign(Lead $lead, ?int $userId): void
    {
        $responsible = $userId ? $this->eligibleUsers()->find($userId) : null;

        $lead->update([
            'responsible_user_id' => $responsible?->id,
            'assigned_at' => $responsible ? now() : null,
        ]);

        if ($responsible) {
            $this->notify($lead->fresh(), true);
        }
    }

    public function eligibleUsers(): Builder
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', ['admin', 'super_admin']))
            ->orderBy('name');
    }

    private function defaultResponsible(): ?User
    {
        $responsible = LeadRoutingSetting::query()->with('defaultResponsible')->first()?->defaultResponsible;

        return $responsible && $this->eligibleUsers()->whereKey($responsible->id)->exists() ? $responsible : null;
    }

    private function notify(Lead $lead, bool $assignmentNotice = false): void
    {
        $lead->loadMissing('responsible');
        $recipient = $lead->responsible?->email ?: config('leads.technical_contact_email');

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            Log::critical('Lead notification has no valid recipient.', ['lead_id' => $lead->id]);

            return;
        }

        try {
            Mail::to($recipient)->send(new LeadNotificationMail($lead, $assignmentNotice));
        } catch (\Throwable $exception) {
            Log::error('Lead notification could not be delivered.', [
                'lead_id' => $lead->id,
                'error' => $exception::class,
            ]);
        }
    }
}
