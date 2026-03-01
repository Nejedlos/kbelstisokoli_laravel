<?php

namespace App\Notifications;

class RsvpChangedNotification extends BaseNotification
{
    public function __construct(
        protected string $eventTitle,
        protected string $status,
        protected ?\App\Models\User $user = null,
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

        if ($this->user) {
            $message = "Změna účasti na akci \"{$this->eventTitle}\" na: {$statusLabel}.";
        } else {
            $message = "Tvoje účast na akci \"{$this->eventTitle}\" byla {$statusLabel}.";
        }

        return [
            'type' => 'info',
            'title' => 'Změna účasti na akci',
            'message' => $message,
            'user_id' => $this->user?->id,
            'user_name' => $this->user?->name,
            'user_avatar' => $this->user?->getAvatarUrl('thumb'),
            'action_label' => 'Zobrazit program',
            'action_url' => $this->actionUrl ?? route('member.attendance.index'),
        ];
    }
}
