<?php

namespace App\Notifications;

class RsvpChangedNotification extends BaseNotification
{
    protected string $notificationType = 'attendance';

    public function __construct(
        protected string $eventTitle,
        protected string $status,
        protected ?\App\Models\User $user = null,
        protected ?string $actionUrl = null,
        protected string $eventLabelKey = 'event',
        protected ?\Carbon\Carbon $eventDate = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Pro změny docházky používáme pouze databázový kanál (in-app dropdown).
        // E-maily pro každou změnu docházky jsou příliš frekventované (spamující)
        // a způsobují technické problémy s limity SMTP serveru při odesílání více notifikací najednou.
        return ['database'];
    }

    protected function getNotificationData(): array
    {
        $statusKey = match ($this->status) {
            'confirmed' => 'confirmed',
            'declined' => 'declined',
            'maybe' => 'maybe',
            default => 'changed',
        };

        $type = match ($this->status) {
            'confirmed' => 'success',
            'declined' => 'danger',
            'maybe' => 'warning',
            default => 'info',
        };

        $icon = match ($this->eventLabelKey) {
            'training' => 'calendar-star',
            'match' => 'basketball',
            'club_event' => 'star',
            default => 'calendar',
        };

        $label = __("member.notifications.event_labels.{$this->eventLabelKey}");
        $datetime = $this->eventDate ? " (" . $this->eventDate->translatedFormat('j. n. H:i') . ")" : '';

        // Akce (sloveso)
        $actionKey = $this->user ? "member.notifications.rsvp_actions_user.{$statusKey}" : "member.notifications.rsvp_actions_self.{$statusKey}";
        $action = __($actionKey, [
            'name' => $this->user?->name,
        ]);

        // Celá zpráva
        $message = __('member.notifications.rsvp_message', [
            'action' => $action,
            'label' => mb_strtolower($label),
            'title' => $this->eventTitle,
            'datetime' => $datetime,
        ]);

        return [
            'type' => $type,
            'icon' => $icon,
            'category' => 'attendance',
            'title' => 'member.notifications.rsvp_changed_title',
            'message' => $message,
            'user_id' => $this->user?->id,
            'user_name' => $this->user?->name,
            'user_avatar' => $this->user?->getAvatarUrl('thumb'),
            'action_label' => 'member.notifications.view_program',
            'action_url' => $this->actionUrl ?? route('member.attendance.index'),
        ];
    }
}
