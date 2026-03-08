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
        protected string $eventLabelKey = 'event'
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
        $statusLabel = match ($this->status) {
            'confirmed' => __('member.notifications.rsvp_statuses.confirmed'),
            'declined' => __('member.notifications.rsvp_statuses.declined'),
            'maybe' => __('member.notifications.rsvp_statuses.maybe'),
            default => __('member.notifications.rsvp_statuses.changed'),
        };

        $label = __("member.notifications.event_labels.{$this->eventLabelKey}");

        if ($this->user) {
            $message = __('member.notifications.rsvp_message_user', [
                'label' => $label,
                'title' => $this->eventTitle,
                'status' => $statusLabel,
            ]);
        } else {
            $message = __('member.notifications.rsvp_message_self', [
                'label' => $label,
                'title' => $this->eventTitle,
                'status' => $statusLabel,
            ]);
        }

        return [
            'type' => 'info',
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
