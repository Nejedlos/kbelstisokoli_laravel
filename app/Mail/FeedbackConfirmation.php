<?php

namespace App\Mail;

use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FeedbackConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $type; // coach|admin
    public User $user;
    public ?Team $team;
    public string $bodyMessage;

    public function __construct(
        string $type,
        User $user,
        string $subject,
        string $message,
        ?Team $team = null,
        ?string $locale = null,
    ) {
        $this->type = $type;
        $this->user = $user;
        $this->subject($subject);
        $this->bodyMessage = $message;
        $this->team = $team;

        if ($locale) {
            $this->locale($locale);
        }
    }

    public function build(): self
    {
        return $this->view('emails.feedback.confirmation')
            ->with([
                'type' => $this->type,
                'user' => $this->user,
                'team' => $this->team,
                'bodyMessage' => $this->bodyMessage,
            ]);
    }
}
