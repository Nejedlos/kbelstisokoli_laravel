<?php

namespace App\Notifications;

class RsvpChangedNotification extends BaseNotification
{
    public function __construct(
        protected string $eventTitle,
        protected string $status,
        protected ?string $actionUrl = null
    ) {}

    protected function getNotificationData(): array
    {
        $statusLabel = match ($this->status) {
            'confirmed' => 'potvrzena',
            'declined' => 'zrušena (omluveno)',
            'maybe' => 'změněna na Možná',
            default => 'změněna',
        };

        return [
            'type' => 'info',
            'title' => 'Změna účasti na akci',
            'message' => "Tvoje účast na akci \"{$this->eventTitle}\" byla {$statusLabel}.",
            'action_label' => 'Zobrazit program',
            'action_url' => $this->actionUrl ?? route('member.attendance.index'),
        ];
    }
}
